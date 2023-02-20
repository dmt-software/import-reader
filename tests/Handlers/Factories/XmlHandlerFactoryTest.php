<?php

namespace DMT\Test\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\Factories\XmlHandlerFactory;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use PHPUnit\Framework\TestCase;

class XmlHandlerFactoryTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $contents = file_get_contents(__DIR__ . '/../../files/cars.xml');

        $factory = new XmlHandlerFactory();
        $handler = $factory->createFromString($contents, ['path' => '/cars/car'], []);
        $handler->setPointer();

        $this->assertInstanceOf(XmlReaderHandler::class, $handler);
        $this->assertStringStartsWith('<car>', $handler->read()->current());
    }

    public function testCreateFromStream(): void
    {
        $stream = fopen(__DIR__ . '/../../files/cars.xml', 'r');

        $factory = new XmlHandlerFactory();
        $handler = $factory->createFromStream($stream, ['path' => '/cars/car'], []);
        $handler->setPointer();

        $this->assertInstanceOf(XmlReaderHandler::class, $handler);
        $this->assertStringStartsWith('<car>', $handler->read()->current());

        fclose($stream);
    }

    public function testCreateFromFile(): void
    {
        $factory = new XmlHandlerFactory();
        $handler = $factory->createFromFile(__DIR__ . '/../../files/cars.xml', ['path' => '/cars/car'], []);
        $handler->setPointer();

        $this->assertInstanceOf(XmlReaderHandler::class, $handler);
        $this->assertStringStartsWith('<car>', $handler->read()->current());
    }
}
