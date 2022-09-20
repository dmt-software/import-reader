<?php

namespace DMT\Import\Reader;

use ArrayObject;
use CallbackFilterIterator;
use Closure;
use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Decorators\Handler\ToSimpleXmlElementDecorator;
use DMT\Import\Reader\Decorators\ToArrayDecorator;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\HandlerInterface;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use Iterator;

/**
 * Array reader.
 *
 * Reads an import file into a list of arrays.
 */
final class ArrayReader implements ReaderInterface
{
    private ReaderInterface $reader;

    /**
     * Array reader.
     *
     * @param HandlerInterface $handler The handler that iterates through a file.
     * @param array $options The decorator options:
     *      mapping  : The csv column to array key mapping [optional]
     *      namespace: The namespace for the elements in xml to map [optional]
     */
    public function __construct(HandlerInterface $handler, array $options)
    {
        $namespace = $options['namespace'] ?? null;
        $mapping = $options['mapping'] ?? null;

        $handlerDecorator = new GenericHandlerDecorator();
        if ($namespace && $handler instanceof XmlReaderHandler) {
            $handlerDecorator = new ToSimpleXmlElementDecorator($namespace);
        }

        $decorators = [$handlerDecorator];
        if ($handler instanceof CsvReaderHandler && isset($mapping)) {
            $decorators[] = new ColumnMappingDecorator($mapping);
        }

        $this->reader = new Reader($handler, ...$decorators, new ToArrayDecorator());
    }

    /**
     * Read through a file.
     *
     * @param int $skip The number of lines or items to skip.
     * @param Closure|null $filter A callback filter to apply.
     * @return Iterator|array[] A list of arrays retrieved from a file.
     * @throws ReaderReadException When the reader can not continue to read from file.
     */
    public function read(int $skip = 0, Closure $filter = null): Iterator
    {
        $filter ??= fn($currentRow, $key) => true;
        $iterator = function (int $skip) {
            foreach ($this->reader->read($skip) as $key => $currentRow) {
                yield $key => $currentRow->getArrayCopy();
            }
        };

        return new CallbackFilterIterator($iterator($skip), $filter);
    }
}
