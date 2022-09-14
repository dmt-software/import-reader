<?php

namespace DMT\Import\Reader\Decorators;

use DMT\Import\Reader\Exceptions\DecoratorApplyException;

interface ToObjectDecoratorInterface extends DecoratorInterface
{
    /**
     * Apply the decorator to the row.
     *
     * @param string|array $currentRow The row received from the reader.
     * @return object The decorated row.
     * @throws DecoratorApplyException
     */
    public function apply($currentRow): object;
}
