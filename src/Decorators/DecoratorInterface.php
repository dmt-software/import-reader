<?php

namespace DMT\Import\Reader\Decorators;

use DMT\Import\Reader\Exceptions\DecoratorApplyException;

interface DecoratorInterface
{
    /**
     * Apply the decorator to the row.
     *
     * @param string|array|object $currentRow The row received from the reader of an earlier applied decorator.
     * @return object The decorated row.
     * @throws DecoratorApplyException
     */
    public function apply($currentRow): object;
}
