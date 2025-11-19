<?php

namespace DMT\Import\Reader\Decorators;

use Closure;

class CallbackDecorator implements DecoratorInterface
{

    public function __construct(private readonly Closure $callback)
    {
    }

    public function decorate(object $currentRow): object
    {
        return call_user_func($this->callback, $currentRow) ?? $currentRow;
    }
}
