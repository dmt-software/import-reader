<?php

namespace DMT\Test\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\Factories\CsvHandlerFactory;
use PHPUnit\Framework\TestCase;

class CsvHandlerFactoryTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $factory = new CsvHandlerFactory();
        $handler = $factory->createFromString(file_get_contents(__DIR__ . '/../../files/planes.csv'), [], []);

        $this->assertInstanceOf(CsvReaderHandler::class, $handler);
        $this->assertIsArray($handler->read()->current());
    }

    public function testCreateFromStream(): void
    {
        $factory = new CsvHandlerFactory();
        $handler = $factory->createFromStream($stream = fopen(__DIR__ . '/../../files/planes.csv', 'r'), [], []);

        $this->assertInstanceOf(CsvReaderHandler::class, $handler);
        $this->assertIsArray($handler->read()->current());

        fclose($stream);
    }

    public function testCreateFromFile(): void
    {
        $factory = new CsvHandlerFactory();
        $handler = $factory->createFromFile(__DIR__ . '/../../files/planes.csv', [], []);

        $this->assertInstanceOf(CsvReaderHandler::class, $handler);
        $this->assertIsArray($handler->read()->current());
    }
}
