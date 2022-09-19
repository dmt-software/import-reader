<?php

namespace DMT\Import\Reader;

use ArrayObject;
use CallbackFilterIterator;
use Closure;
use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Decorators\Handler\DeserializeToObjectDecorator;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Decorators\Handler\ToSimpleXmlElementDecorator;
use DMT\Import\Reader\Decorators\ToArrayDecorator;
use DMT\Import\Reader\Decorators\ToObjectDecorator;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\Handlers\HandlerInterface;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\Sanitizers\EncodingSanitizer;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use InvalidArgumentException;
use JMS\Serializer\SerializerInterface;
use SimpleXMLElement;
use stdClass;

class ImportReaderManager
{
    private array $extensionToHandler = [
        'csv' => CsvReaderHandler::class,
        'json' => JsonReaderHandler::class,
        'xml' => XmlReaderHandler::class,
    ];

    private array $sanitizers = [
        'trim' => TrimSanitizer::class,
        'encoding' => EncodingSanitizer::class,
    ];

    /**
     * Default read from file.
     *
     * @param string $file         The file or protocol wrapper to read.
     * @param array $options       The configuration options (@see createHandler())
     * @param Closure|null $filter The filter to apply to the read lines.
     *
     * @return stdClass[]|ArrayObject[]|SimpleXMLElement[]
     */
    public function readFromFile(string $file, array $options, Closure $filter = null): iterable
    {
        $handler = $this->createHandler($file, $options);

        $decorators = [
            $handler instanceof XmlReaderHandler && isset($options['namespace'])
                ? new ToSimpleXmlElementDecorator($options['namespace'])
                : new GenericHandlerDecorator()
        ];

        if ($handler instanceof CsvReaderHandler && isset($options['mapping'])) {
            $decorators[] = new ColumnMappingDecorator($options['mapping']);
        }

        /** @var object[] $iterator */
        $iterator = new CallbackFilterIterator(
            (new Reader($handler, ...$decorators))->read(),
            $filter ?? fn($currentRow, $key) => true
        );

        yield from $iterator;
    }

    /**
     * Read as arrays.
     *
     * Iterate through a file and return an array for each line.
     *
     * @param string $file         The file or protocol wrapper to read.
     * @param array $options       The configuration options (@see createHandler())
     * @param Closure|null $filter The filter to apply to the read lines.
     *
     * @return array[]
     */
    public function readArraysFromFile(string $file, array $options, Closure $filter = null): iterable
    {
        $handler = $this->createHandler($file, $options);

        $decorators = [
            $handler instanceof XmlReaderHandler && isset($options['namespace'])
                ? new ToSimpleXmlElementDecorator($options['namespace'])
                : new GenericHandlerDecorator()
        ];

        if ($handler instanceof CsvReaderHandler && isset($options['mapping'])) {
            $decorators[] = new ColumnMappingDecorator($options['mapping']);
        }

        /** @var ArrayObject[] $iterator */
        $iterator = new CallbackFilterIterator(
            (new Reader($handler, ...$decorators, new ToArrayDecorator()))->read(),
            $filter ?? fn($currentRow, $key) => true
        );

        foreach ($iterator as $i => $objects) {
            yield $i => $objects->getArrayCopy();
        }
    }

    /**
     * Read as objects.
     *
     * Iterate through a file and return a user defined object for each line.
     *
     * @param string $file         The file or protocol wrapper to read.
     * @param array $options       The configuration options (@see createHandler())
     * @param string $class        The class to initiate for each of the lines.
     * @param Closure|null $filter The serializer to use for deserialization The filter to apply to the read lines.
     *
     * @return object[] A list of instances of <$class>
     */
    public function readObjectsFromFile(string $file, array $options, string $class, Closure $filter = null): iterable
    {
        $handler = $this->createHandler($file, $options);

        $mapping = $options['mapping'] ?? [];
        if (!$mapping) {
            throw new InvalidArgumentException('Mapping option is missing');
        }

        $defaultDecorator = new GenericHandlerDecorator();
        if ($handler instanceof XmlReaderHandler && isset($options['namespace'])) {
            $defaultDecorator = new ToSimpleXmlElementDecorator($options['namespace']);
        }

        /** @var object[] $iterator */
        $iterator = new CallbackFilterIterator(
            (new Reader($handler, $defaultDecorator, new ToObjectDecorator($class, $mapping)))->read(),
            $filter ?? fn($currentRow, $key) => true
        );

        yield from $iterator;
    }

    /**
     * Read as deserialized objects.
     *
     * Iterate through a file and return a deserialized object for each line.
     *
     * @param string $file                    The file or protocol wrapper to read.
     * @param array $options                  The configuration options (@see self::createHandler())
     * @param string $class                   The class to initiate for each of the lines.
     * @param SerializerInterface $serializer The serializer to use for deserialization
     * @param Closure|null $filter            The filter to apply to the read lines.
     *
     * @return object[] A list of instances of <$class>
     */
    public function readDeserializedObjectsFromFile(
        string $file,
        array $options,
        string $class,
        SerializerInterface $serializer,
        Closure $filter = null
    ): iterable {
        $handler = $this->createHandler($file, $options);

        /** @var object[] $iterator */
        $iterator = new CallbackFilterIterator(
            (new Reader($handler, new DeserializeToObjectDecorator($serializer, $class)))->read(),
            $filter ?? fn($currentRow, $key) => true
        );

        yield from $iterator;
    }

    /**
     * Get the reader handler.
     *
     * @param string $file   The file or protocol wrapper to read.
     * @param array $options The configuration options:
     *      trim      : array with chars and direction
     *      encoding  : the encoding of the file (when not utf-8)
     *      handler   : the type of handler to use
     *      delimiter : the delimiter for csv files
     *      enclosure : the enclosure for csv files
     *      escape    : the escape character for csv
     *      path      : the path (from root) of the objects or elements to read
     *                   - xml  -> a xpath like structure of local element names (root/child/element)
     *                   - json -> a dotted path to the objects (root.elements)
     *      mapping   : column, path or xpath to property mapping
     *      namespace : xml namespace of the xml elements to read
     *      flags     : a bitmask of (xml or json) options
     *
     * @return HandlerInterface
     */
    private function createHandler(string $file, array $options): HandlerInterface
    {
        $handlerFactory = new HandlerFactory();
        $handler = $options['handler'] ?? $this->getHandlerTypeForFile($file);

        if (!is_a($handler, HandlerInterface::class, true)) {
            throw new InvalidArgumentException('Unsupported handler');
        }

        switch ($handler) {
            case CsvReaderHandler::class:
                return $handlerFactory
                    ->createCsvReaderHandler($file, $options, ...$this->getSanitizersFromOptions($options));
            case JsonReaderHandler::class:
                return $handlerFactory->createCsvReaderHandler($file, $options);
            case XmlReaderHandler::class:
                return $handlerFactory->createXmlReaderHandler($file, $options);
            default:
                throw new InvalidArgumentException('No custom handlers supported yet');
        }
    }

    /**
     * Get the sanitizers from options.
     *
     * @param array $options The configuration options.
     *
     * @return SanitizerInterface[]
     */
    private function getSanitizersFromOptions(array $options): array
    {
        $encoding = $options['encoding'] ?? 'utf-8';
        if (strcasecmp($encoding, 'utf-8') === 0) {
            unset($options['encoding']);
        }

        foreach ($options as &$arguments) {
            $arguments = (array)$arguments;
        }

        $sanitizers = [];
        foreach ($this->sanitizers as $option => $sanitizer) {
            if (array_key_exists($option, $options)) {
                $sanitizers[] = new $sanitizer(...$options[$option]);
            }
        }

        return $sanitizers;
    }

    /**
     * Get handler for file.
     *
     * @param string $file The file or protocol wrapper to read
     * @return string
     */
    private function getHandlerTypeForFile(string $file): string
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (array_key_exists($extension, $this->extensionToHandler)) {
            throw new InvalidArgumentException('Can not determine a handler for the import file');
        }

        return $this->extensionToHandler[$extension];
    }
}
