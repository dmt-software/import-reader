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
use DMT\Import\Reader\Helpers\MimeTypeHelper;
use DMT\Import\Reader\Helpers\SourceHelper;
use JMS\Serializer\SerializerInterface;
use RuntimeException;

/**
 * Builder to help build a reader from configuration options.
 */
final class ReaderBuilder
{
    private HandlerFactory $handlerFactory;

    private array $sanitizers = [
        'trim' => TrimSanitizer::class,
        'encoding' => EncodingSanitizer::class,
    ];

    public function __construct(HandlerFactory $handlerFactory = null)
    {
        $this->handlerFactory = $handlerFactory ?? new HandlerFactory();
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

        $decorators = [];
        if ($namespace && $handler instanceof XmlReaderHandler) {
            $decorators[] = new ToSimpleXmlElementDecorator($namespace);
        }

        if ($handler instanceof CsvReaderHandler && isset($mapping)) {
            $decorators[] = new GenericHandlerDecorator();
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
     * @param string $source The file or protocol wrapper to read.
     * @param array $options The configuration options:
     *      handler   : the type of handler to use
     *      delimiter : the delimiter for csv files
     *      enclosure : the enclosure for csv files
     *      escape    : the escape character for csv
     *      path      : the path (from root) of the objects or elements to read
     *                   - xml  -> a xpath like structure of local element names (root/child/element)
     *                   - json -> a dotted path to the objects (root.elements)
     *      flags     : a bitmask of (xml or json) options, possible options:
     *                   - Reader::JSON_FLOATS_AS_STRINGS
     *                   - Reader::XML_DROP_NAMESPACES
     *                   - Reader::XML_USE_CDATA
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
    public function createHandler($source, array $options): HandlerInterface
    {
        try {
            $sourceType = SourceHelper::detect($source);
        } catch (RuntimeException $exception) {
            throw UnreadableException::unreadable('from ' . gettype($source), $exception);
        }
        $handler = $options['handler'] ?? $this->getHandlerType($source, $sourceType);

        switch ($handler) {
            case JsonReaderHandler::class:
                $sanitizers = $this->getSanitizersFromOptions($options, ['encoding']);
                break;
            case XmlReaderHandler::class:
                $sanitizers = $this->getSanitizersFromOptions($options, ['trim']);
                break;
            default:
                $sanitizers = $this->getSanitizersFromOptions($options);
                break;
        }

        return $this->handlerFactory->createReaderHandler($handler, $source, $sourceType, $options, $sanitizers);
    }

    /**
     * Get the sanitizers from options.
     *
     * @param array $options The configuration options.
     * @param array $exclude The sanitizers to ignore
     *
     * @return SanitizerInterface[]
     */
    private function getSanitizersFromOptions(array $options, array $exclude = []): array
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
            if (array_key_exists($option, $exclude)) {
                continue;
            }
            if (array_key_exists($option, $options)) {
                $sanitizers[] = new $sanitizer(...$options[$option]);
            }
        }

        return $sanitizers;
    }

    /**
     * Get the right handler for the source.
     *
     * @param resource|string $source
     * @param string $sourceType
     *
     * @return string
     * @throws UnreadableException
     */
    private function getHandlerType($source, string $sourceType): string
    {
        try {
            $mimeType = MimeTypeHelper::detect($source, $sourceType);
        } catch (UnreadableException $exception) {
            throw $exception;
        } catch (RuntimeException $exception) {
            throw UnreadableException::unreadable($source, $exception);
        }

        switch ($mimeType) {
            case MimeTypeHelper::MIME_TYPE_CSV:
                return CsvReaderHandler::class;
            case MimeTypeHelper::MIME_TYPE_JSON:
                return JsonReaderHandler::class;
            case MimeTypeHelper::MIME_TYPE_XML:
                return XmlReaderHandler::class;
        }

        if ($sourceType !== SourceHelper::SOURCE_TYPE_FILE) {
            $source = 'from ' . $sourceType;
        }

        throw UnreadableException::unreadable($source, new RuntimeException('no handler found'));
    }
}
