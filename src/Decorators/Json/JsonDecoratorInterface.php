<?php

namespace DMT\Import\Reader\Decorators\Json;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use stdClass;

interface JsonDecoratorInterface extends DecoratorInterface
{
    /**
     * Apply the decorator to the row.
     *
     * @param stdClass $currentRow The row received from an earlier applied decorator.
     * @return object The decorated row.
     * @throws DecoratorApplyException
     */
    public function apply(stdClass $currentRow): object;
}
