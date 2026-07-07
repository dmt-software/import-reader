<?php

declare(strict_types=1);

namespace DMT\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\HandlerInterface;
use InvalidArgumentException;

interface HandlerFactoryInterface
{
    /**
     * @param resource $stream
     * @throws InvalidArgumentException
     */
    public function createFromStream($stream, array $config, array $sanitizers): HandlerInterface;

    /**
     * @throws InvalidArgumentException
     */
    public function createFromString(string $source, array $config, array $sanitizers): HandlerInterface;

    /**
     * @throws InvalidArgumentException
     */
    public function createFromFile(string $fileOrUri, array $config, array $sanitizers): HandlerInterface;
}
