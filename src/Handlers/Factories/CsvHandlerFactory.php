<?php

namespace DMT\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\HandlerInterface;
use InvalidArgumentException;

class CsvHandlerFactory implements HandlerFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createFromStream($stream, array $config, array $sanitizers): CsvReaderHandler
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('Illegal source');
        }

        return new CsvReaderHandler($stream, $config, ...$sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromString(string $source, array $config, array $sanitizers): CsvReaderHandler
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $source);
        rewind($stream);

        return $this->createFromStream($stream, $config, $sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromFile(string $fileOrUri, array $config, array $sanitizers): CsvReaderHandler
    {
        return $this->createFromStream(@fopen($fileOrUri, 'r'), $config, $sanitizers);
    }
}
