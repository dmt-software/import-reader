<?php

namespace DMT\Import\Reader\Handlers\Factories;

use Closure;
use DMT\Import\Reader\Handlers\HandlerInterface;

class CallbackHandlerFactory implements HandlerFactoryInterface
{
    private Closure $callback;

    /**
     * @param \Closure $callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritDoc
     */
    public function createFromStream($stream, array $config, array $sanitizers): HandlerInterface
    {
        return call_user_func($this->callback, $stream, $config, $sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromString(string $source, array $config, array $sanitizers): HandlerInterface
    {
        return call_user_func($this->callback, $source, $config, $sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromFile(string $fileOrUri, array $config, array $sanitizers): HandlerInterface
    {
        return call_user_func($this->callback, $fileOrUri, $config, $sanitizers);
    }
}
