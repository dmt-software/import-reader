<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\Pointers\JsonPathPointer;
use DMT\Import\Reader\Handlers\Pointers\XmlPathPointer;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\XmlParser\Parser;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;

class HandlerFactoryTest extends TestCase
{
    public function testCreateCsvReaderHandler(): void
    {
        $csvControl = [
            'delimiter' => ';',
            'enclosure' => "'"
        ];

        $handler = (new HandlerFactory())->createReaderHandler(CsvReaderHandler::class, 'php://memory', $csvControl);

        $property = $this->getPropertyValue($handler, 'csvControl');

        $this->assertInstanceOf(CsvReaderHandler::class, $handler);
        $this->assertContains($csvControl['delimiter'], $property);
        $this->assertContains($csvControl['enclosure'], $property);
    }

    public function testCreateXmlReaderHandler(): void
    {
        $path = 'some/element';
        $handler = (new HandlerFactory())
            ->createReaderHandler(XmlReaderHandler::class, 'php://memory', compact('path'));
        $pointer = $this->getPropertyValue($handler, 'pointer');

        $this->assertInstanceOf(XmlReaderHandler::class, $handler);
        $this->assertInstanceOf(Parser::class, $this->getPropertyValue($handler, 'reader'));
        $this->assertInstanceOf(XmlPathPointer::class, $pointer);
        $this->assertSame($path, $this->getPropertyValue($pointer, 'path'));
    }

    public function testCreateJsonReaderHandler(): void
    {
        $path = 'some.object';
        $handler = (new HandlerFactory())
            ->createReaderHandler(JsonReaderHandler::class, 'php://memory', compact('path'));
        $pointer = $this->getPropertyValue($handler, 'pointer');

        $this->assertInstanceOf(JsonReaderHandler::class, $handler);
        $this->assertInstanceOf(JsonReader::class, $this->getPropertyValue($handler, 'reader'));
        $this->assertInstanceOf(JsonPathPointer::class, $pointer);
        $this->assertSame($path, $this->getPropertyValue($pointer, 'path'));
    }

    public function testNotRegisteredHandler(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can not initiate Some\\CustomHandler');

        (new HandlerFactory())->createReaderHandler("Some\\CustomHandler", 'php://memory');
    }

    public function testCreateCustomReaderHandlerWithCallback(): void
    {
        $handlerFactory = new HandlerFactory();
        $handlerFactory->addInitializeHandlerCallback('CustomHandler', function (string $file) {
            $reader = (object)compact('file');

            return $this->getMockBuilder(CustomReaderHandlerStub::class)
                ->setMockClassName('CustomHandler')
                ->setConstructorArgs([$reader])
                ->getMockForAbstractClass();
        });
        $handler = $handlerFactory->createReaderHandler('CustomHandler', 'php://memory');

        $this->assertEquals((object)['file' => 'php://memory'], $handler->reader);
    }

    private function getPropertyValue(object $object, $property)
    {
        $reader = new ReflectionProperty($object, $property);
        $reader->setAccessible(true);

        return $reader->getValue($object);
    }
}
