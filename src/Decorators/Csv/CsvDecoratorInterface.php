<?php

namespace DMT\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;

interface CsvDecoratorInterface extends DecoratorInterface
{
    /**
     * Apply the decorator to the row.
     *
     * @param ArrayObject $currentRow The row received from an earlier applied decorator.
     * @return object|ArrayObject The decorated row.
     * @throws DecoratorApplyException
     */
    public function apply(ArrayObject $currentRow): object;
}
