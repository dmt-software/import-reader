<?php

declare(strict_types=1);

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\HandlerInterface;

abstract class CustomReaderHandlerStub implements HandlerInterface
{
    public function __construct(public object $reader)
    {
    }
}
