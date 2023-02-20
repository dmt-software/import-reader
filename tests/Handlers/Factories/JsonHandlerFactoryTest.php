<?php

namespace DMT\Test\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\Factories\JsonHandlerFactory;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use PHPUnit\Framework\TestCase;

class JsonHandlerFactoryTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $contents = file_get_contents(__DIR__ . '/../../files/programming.json');

        $factory = new JsonHandlerFactory();
        $handler = $factory->createFromString($contents, ['path' => '.'], []);
        $handler->setPointer();

        $this->assertInstanceOf(JsonReaderHandler::class, $handler);
        $this->assertStringStartsWith('{', $handler->read()->current());
    }

    public function testCreateFromStream(): void
    {
        $stream = fopen(__DIR__ . '/../../files/programming.json', 'r');

        $factory = new JsonHandlerFactory();
        $handler = $factory->createFromStream($stream, ['path' => '.'], []);
        $handler->setPointer();

        $this->assertInstanceOf(JsonReaderHandler::class, $handler);
        $this->assertStringStartsWith('{', $handler->read()->current());

        fclose($stream);
    }

    public function testCreateFromFile(): void
    {
        $factory = new JsonHandlerFactory();
        $handler = $factory->createFromFile(__DIR__ . '/../../files/programming.json', ['path' => '.'], []);
        $handler->setPointer();

        $this->assertInstanceOf(JsonReaderHandler::class, $handler);
        $this->assertStringStartsWith('{', $handler->read()->current());
    }
}
