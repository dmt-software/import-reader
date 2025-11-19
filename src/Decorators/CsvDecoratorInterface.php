<?php

namespace DMT\Import\Reader\Decorators;

use ArrayObject;

interface CsvDecoratorInterface
{
    /**
     * @param ArrayObject $currentRow The row received from an earlier applied decorator.
     * @return ArrayObject The decorated row.
     */
    public function decorate(ArrayObject $currentRow): ArrayObject;
}
