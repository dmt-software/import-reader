<?php

namespace DMT\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\HandlerInterface;

interface HandlerFactoryInterface
{
    /**
     * @param resource $stream
     * @param array $config
     * @param array $sanitizers
     * @return \DMT\Import\Reader\Handlers\HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function createFromStream($stream, array $config, array $sanitizers): HandlerInterface;

    /**
     * @param string $source
     * @param array $config
     * @param array $sanitizers
     * @return \DMT\Import\Reader\Handlers\HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function createFromString(string $source, array $config, array $sanitizers): HandlerInterface;

    /**
     * @param string $fileOrUri
     * @param array $config
     * @param array $sanitizers
     * @return \DMT\Import\Reader\Handlers\HandlerInterface
     * @throws \InvalidArgumentException
     */
    public function createFromFile(string $fileOrUri, array $config, array $sanitizers): HandlerInterface;
}
