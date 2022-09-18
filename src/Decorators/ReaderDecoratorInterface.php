<?php

namespace DMT\Import\Reader\Decorators;

use DMT\Import\Reader\Exceptions\DecoratorException;

interface ReaderDecoratorInterface extends DecoratorInterface
{
    /**
     * Apply the decorator to the raw value received from reader.
     *
     * @param string|array $currentRow The current row from reader.
     * @return object The decorated row.
     * @throws DecoratorException
     */
    public function decorate($currentRow): object;
}
