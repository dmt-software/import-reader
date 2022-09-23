<?php

namespace DMT\Import\Reader;

use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Decorators\Handler\ToSimpleXmlElementDecorator;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\Handlers\HandlerInterface;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\Sanitizers\EncodingSanitizer;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use JMS\Serializer\SerializerInterface;
use RuntimeException;

/**
 * Builder to help build a reader from configuration options.
 */
final class ReaderBuilder
{
    private HandlerFactory $handlerFactory;

    private array $extensionToHandler = [
        'csv' => CsvReaderHandler::class,
        'json' => JsonReaderHandler::class,
        'xml' => XmlReaderHandler::class,
    ];

    private array $sanitizers = [
        'trim' => TrimSanitizer::class,
        'encoding' => EncodingSanitizer::class,
    ];

    public function __construct(HandlerFactory $handlerFactory = null)
    {
        $this->handlerFactory = $handlerFactory ?? new HandlerFactory();
    }

    /**
     * Add a extension to handler mapping.
     *
     * @param string $extension The file extension for the handler.
     * @param string $handlerClassName The class name of the handler.
     */
    public function addExtensionToHandler(string $extension, string $handlerClassName): void
    {
        $this->extensionToHandler[$extension] = $handlerClassName;
    }

    /**
     * Add a sanitizer.
     *
     * The sanitizer will become available in the options to build a reader.
     * The value for the option should match the constructor arguments.
     *
     * @param string $configKey The key to reference the sanitizer in config options.
     * @param string $sanitizerClassName The sanitizer class.
     */
    public function addSanitizer(string $configKey, string $sanitizerClassName): void
    {
        $this->sanitizers[$configKey] = $sanitizerClassName;
    }

    /**
     * Build default reader from options.
     *
     * This reader returns a stdClass, ArrayObject or SimpleXMLElement on each iteration.
     *
     * @param string $file The file or protocol wrapper to read.
     * @param array $options The configuration options (@see createHandler())
     * @return ReaderInterface
     */
    public function build(string $file, array $options): ReaderInterface
    {
        $namespace = $options['namespace'] ?? null;
        $mapping = $options['mapping'] ?? null;
        $handler = $this->createHandler($file, $options);

        $handlerDecorator = new GenericHandlerDecorator();
        if ($namespace && $handler instanceof XmlReaderHandler) {
            $handlerDecorator = new ToSimpleXmlElementDecorator($namespace);
        }

        $decorators = [$handlerDecorator];
        if ($handler instanceof CsvReaderHandler && isset($mapping)) {
            $decorators[] = new ColumnMappingDecorator($mapping);
        }

        return new Reader($handler, ...$decorators);
    }

    /**
     * Build an array reader from options.
     *
     * @param string $file The file or protocol wrapper to read.
     * @param array $options The configuration options (@see createHandler())
     * @return ReaderInterface
     */
    public function buildToArrayReader(string $file, array $options): ReaderInterface
    {
        $readerOptions = [
            'mapping' => $options['mapping'] ?? null,
            'namespace' => $options['namespace'] ?? null,
        ];

        return new ToArrayReader($this->createHandler($file, $options), $readerOptions);
    }

    /**
     * Build an objects reader from options.
     *
     * @param string $file The file or protocol wrapper to read.
     * @param array $options The configuration options (@see createHandler())
     * @param SerializerInterface|null $serializer The deserialize handler.
     * @return ReaderInterface
     */
    public function buildToObjectReader(string $file, array $options, SerializerInterface $serializer = null): ReaderInterface
    {
        $readerOptions = [
            'class' => $options['class'] ?? null,
            'mapping' => $options['mapping'] ?? null,
            'namespace' => $options['namespace'] ?? null,
        ];

        return new ToObjectReader($this->createHandler($file, $options), $readerOptions, $serializer);
    }

    /**
     * Get the reader handler.
     *
     * @param string $file The file or protocol wrapper to read.
     * @param array $options The configuration options:
     *      handler   : the type of handler to use
     *      delimiter : the delimiter for csv files
     *      enclosure : the enclosure for csv files
     *      escape    : the escape character for csv
     *      path      : the path (from root) of the objects or elements to read
     *                   - xml  -> a xpath like structure of local element names (root/child/element)
     *                   - json -> a dotted path to the objects (root.elements)
     *      flags     : a bitmask of (xml or json) options
     *
     *      trim      : array with chars and direction
     *      encoding  : the encoding of the file (when not utf-8)
     *      <custom>  : <custom sanitizer options>
     *
     *      class     : the class to return for each read item.
     *      mapping   : csv column, xml xpath of json (dotted) path to property mapping
     *                  if no class is given (csv only) the keys are mapped to array keys.
     *
     * @return HandlerInterface
     */
    public function createHandler(string $file, array $options): HandlerInterface
    {
        $handler = $options['handler'] ?? $this->getHandlerTypeForFile($file);

        switch ($handler) {
            case CsvReaderHandler::class:
                return $this->handlerFactory
                    ->createCsvReaderHandler($file, $options, ...$this->getSanitizersFromOptions($options));
            case JsonReaderHandler::class:
                return $this->handlerFactory->createJsonReaderHandler($file, $options);
            case XmlReaderHandler::class:
                return $this->handlerFactory->createXmlReaderHandler($file, $options);
            default:
                return $this->handlerFactory
                    ->createCustomReaderHandler($file, $handler, ...$this->getSanitizersFromOptions($options));
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

        if (!array_key_exists($extension, $this->extensionToHandler)) {
            throw UnreadableException::unreadable(
                $file,
                new RuntimeException('no handler found for file type')
            );
        }

        return $this->extensionToHandler[$extension];
    }
}
