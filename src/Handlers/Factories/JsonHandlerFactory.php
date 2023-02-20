<?php

namespace DMT\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\Pointers\JsonPathPointer;
use InvalidArgumentException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

class JsonHandlerFactory implements HandlerFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createFromStream($stream, array $config, array $sanitizers): JsonReaderHandler
    {
        try {
            $reader = new JsonReader($config['flags'] ?? 0);
            $reader->stream($stream);
        } catch (Exception $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }

        return $this->create($reader, $config, $sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromString(string $source, array $config, array $sanitizers): JsonReaderHandler
    {
        $reader = new JsonReader($config['flags'] ?? 0);
        $reader->json($source);

        return $this->create($reader, $config, $sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromFile(string $fileOrUri, array $config, array $sanitizers): JsonReaderHandler
    {
        try {
            $reader = new JsonReader($config['flags'] ?? 0);
            $reader->open($fileOrUri);
        } catch (Exception $exception) {
            throw new InvalidArgumentException($exception->getMessage());
        }

        return $this->create($reader, $config, $sanitizers);
    }

    private function create(JsonReader $reader, array $config, array $sanitizers): JsonReaderHandler
    {
        return new JsonReaderHandler($reader, new JsonPathPointer($config['path'] ?? ''), ...$sanitizers);
    }
}
