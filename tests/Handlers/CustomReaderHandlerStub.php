<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\HandlerInterface;

abstract class CustomReaderHandlerStub implements HandlerInterface
{
    public object $reader;

    public function __construct(object $innerReader)
    {
        $this->reader = $innerReader;
    }
}
