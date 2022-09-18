<?php

namespace DMT\Import\Reader\Decorators;

use DMT\Import\Reader\Exceptions\DecoratorException;

interface DecoratorInterface
{
    /**
     * Apply the decorator to the row.
     *
     * @param object $currentRow The row received from an earlier applied decorator.
     * @return object The decorated row.
     * @throws DecoratorException
     */
    public function decorate(object $currentRow): object;
}
