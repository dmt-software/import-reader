<?php

namespace DMT\Import\Reader;

use CallbackFilterIterator;
use Closure;
use DMT\Import\Reader\Decorators\Handler\DeserializeToObjectDecorator;
use DMT\Import\Reader\Decorators\Handler\ToSimpleXmlElementDecorator;
use DMT\Import\Reader\Decorators\ToObjectDecorator;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Handlers\HandlerInterface;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use Iterator;
use JMS\Serializer\SerializerInterface;

/**
 * Objects reader.
 *
 * Reads an import file into a series of (user defined) objects.
 */
final class ToObjectReader implements ReaderInterface
{
    private ReaderInterface $reader;

    /**
     * Objects reader.
     *
     * @param HandlerInterface $handler The handler that iterates through a file.
     * @param array $options The decorator options:
     *      class    : The class name of the objects to return.
     *      mapping  : The column, xpath or dotted path from row to object property mapping [optional].
     *      namespace: The namespace for the elements in xml to map [optional].
     * @param SerializerInterface|null $serializer The deserializer (xml and json only).
     */
    public function __construct(HandlerInterface $handler, array $options, SerializerInterface $serializer = null)
    {
        $className = $options['class'] ?? null;
        $mapping = $options['mapping'] ?? null;
        $namespace = $options['namespace'] ?? null;

        if ($serializer && ($handler instanceof XmlReaderHandler || $handler instanceof JsonReaderHandler)) {
            $this->reader = new Reader($handler, new DeserializeToObjectDecorator($serializer, $className));
        } else {
            $decorators = [];
            if ($namespace && $handler instanceof XmlReaderHandler) {
                $decorators[] = new ToSimpleXmlElementDecorator($namespace);
            }

            $decorators[] = new ToObjectDecorator($className, $mapping);

            $this->reader = new Reader($handler, ...$decorators);
        }
    }

    /**
     * Read through a file.
     *
     * @param int $skip The number of lines or items to skip.
     * @param Closure|null $filter A callback filter to apply.
     * @return Iterator|object[] A list of objects retrieved from a file.
     * @throws ReaderReadException When the reader can not continue to read from file.
     */
    public function read(int $skip = 0, Closure $filter = null): Iterator
    {
        return new CallbackFilterIterator(
            $this->reader->read($skip),
            $filter ?? fn($currentRow, $key) => true
        );
    }
}
