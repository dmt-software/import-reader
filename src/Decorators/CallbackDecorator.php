<?php

namespace DMT\Import\Reader\Decorators;

use Closure;

class CallbackDecorator implements DecoratorInterface
{

    private Closure $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function decorate(object $currentRow): object
    {
        return call_user_func($this->callback, $currentRow) ?? $currentRow;
    }
}
