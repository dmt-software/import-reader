<?php

namespace DMT\Import\Reader\Decorators;

use stdClass;

interface JsonDecoratorInterface
{
    /**
     * @param stdClass $currentRow The row received from an earlier applied decorator.
     * @return stdClass The decorated row.
     */
    public function decorate(stdClass $currentRow): stdClass;
}
