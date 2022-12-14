<?php

namespace DMT\Import\Reader\Handlers;

use Closure;
use DMT\Import\Reader\Handlers\FilePointers\JsonPathFilePointer;
use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use pcrov\JsonReader\JsonReader;
use SplFileObject;
use XMLReader;

final class HandlerFactory
{
    /**
     * @var array <string, Closure>
     */
    private array $handlerInstantiators = [];

    public function __construct()
    {
        foreach ($this->getDefaultInitializeHandlerCallbacks() as $handlerClassName => $instantiator) {
            $this->addInitializeHandlerCallback($handlerClassName, $instantiator);
        }
    }

    /**
     * Add handler instantiator callback.
     *
     * @param string $handlerClassName
     * @param Closure(string $fileOrUri, array $config, SanitizerInterface[] $sanitizers): HandlerInterface $instantiator
     *
     * @return void
     */
    public function addInitializeHandlerCallback(string $handlerClassName, Closure $instantiator): void
    {
        $this->handlerInstantiators[$handlerClassName] = $instantiator;
    }

    /**
     * Create reader handler.
     *
     * @param string $handlerClassName
     * @param string $fileOrUri
     * @param array $config
     * @param SanitizerInterface[] $sanitizers
     *
     * @return HandlerInterface
     */
    public function createReaderHandler(
        string $handlerClassName,
        string $fileOrUri,
        array $config = [],
        array $sanitizers = []
    ): HandlerInterface {
        $instantiator = $this->getInstantiatorForHandler($handlerClassName);

        return $instantiator($fileOrUri, $config, $sanitizers);
    }

    /**
     * Get instantiator for a handler.
     *
     * @param string $handlerClassName
     *
     * @return Closure
     */
    private function getInstantiatorForHandler(string $handlerClassName): Closure
    {
        if (!array_key_exists($handlerClassName, $this->handlerInstantiators)) {
            return function (string $fileOrUri, array $config, array $sanitizers) use ($handlerClassName) {
                return new $handlerClassName(new SplFileObject($fileOrUri), ...$sanitizers);
            };
        }

        return $this->handlerInstantiators[$handlerClassName];
    }

    /**
     * Get the default handler callbacks.
     *
     * @return array <string, Closure>
     */
    private function getDefaultInitializeHandlerCallbacks(): array
    {
        return [
            CsvReaderHandler::class => function (string $file, array $config, array $sanitizers): HandlerInterface {
                $fileHandler = new SplFileObject($file);
                $fileHandler->setCsvControl(
                    $config['delimiter'] ?? ',',
                    $config['enclosure'] ?? '"',
                    $config['escape'] ?? '\\'
                );

                return new CsvReaderHandler($fileHandler, ...$sanitizers);
            },
            XmlReaderHandler::class => function (string $file, array $config, array $sanitizers): HandlerInterface {
                $pointer = new XmlPathFilePointer($config['path'] ?? '');

                $fileHandler = new XMLReader();
                $fileHandler->open($file, $config['encoding'] ?? null, $config['flags'] ?? 0);

                return new XmlReaderHandler($fileHandler, $pointer, ...$sanitizers);
            },
            JsonReaderHandler::class => function (string $file, array $config, array $sanitizers): HandlerInterface {
                $pointer = new JsonPathFilePointer($config['path'] ?? '');

                $fileHandler = new JsonReader($config['flags'] ?? 0);
                $fileHandler->open($file);

                return new JsonReaderHandler($fileHandler, $pointer, ...$sanitizers);
            }
        ];
    }
}
