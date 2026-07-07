<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\Factories\CsvHandlerFactory;
use DMT\Import\Reader\Handlers\Factories\HandlerFactoryInterface;
use DMT\Import\Reader\Handlers\Factories\JsonHandlerFactory;
use DMT\Import\Reader\Handlers\Factories\XmlHandlerFactory;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use DMT\Import\Reader\Helpers\SourceHelper;
use RuntimeException;
use TypeError;

final class HandlerFactory
{
    /**
     * @var array <string, HandlerFactoryInterface>
     */
    private array $handlerFactories = [];

    public function __construct()
    {
        $this->handlerFactories = [
            CsvReaderHandler::class => new CsvHandlerFactory(),
            XmlReaderHandler::class => new XmlHandlerFactory(),
            JsonReaderHandler::class => new JsonHandlerFactory(),
        ];
    }

    /**
     * Add handler instantiator callback.
     *
     *
     */
    public function addInitializeHandlerFactory(string $handlerClassName, HandlerFactoryInterface $factory): void
    {
        $this->handlerFactories[$handlerClassName] = $factory;
    }

    /**
     * Create reader handler.
     *
     * @param string|resource $source
     * @param array<SanitizerInterface> $sanitizers
     *
     */
    public function createReaderHandler(
        string $handlerClassName,
        mixed  $source,
        string $sourceType,
        array  $config = [],
        array  $sanitizers = []
    ): HandlerInterface {
        try {
            $instantiator = $this->getInstantiatorForHandler($handlerClassName, $sourceType);

            return call_user_func($instantiator, $source, $config, $sanitizers);
        } catch (TypeError) {
            throw new RuntimeException('Source type is not supported for instantiator');
        }
    }

    private function getInstantiatorForHandler(string $handlerClassName, ?string $sourceType = null): callable
    {
        if (!array_key_exists($handlerClassName, $this->handlerFactories)) {
            throw new RuntimeException('Can not initiate ' . $handlerClassName);
        }

        $methods = [
            'file' => 'createFromFile',
            'stream' => 'createFromStream',
            'contents' => 'createFromString',
        ];

        return [$this->handlerFactories[$handlerClassName], $methods[$sourceType ?? SourceHelper::SOURCE_TYPE_FILE]];
    }
}
