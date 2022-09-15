<?php

namespace DMT\Import\Reader\Decorators;

use DMT\Import\Reader\Exceptions\DecoratorApplyException;

interface DecoratorInterface
{
    /**
     * Apply the decorator to the row.
     *
     * @param object $currentRow The row received from an earlier applied decorator.
     * @return object The decorated row.
     * @throws DecoratorApplyException
     */
    public function apply(object $currentRow): object;
}
