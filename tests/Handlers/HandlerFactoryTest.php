<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\FilePointers\JsonPathFilePointer;
use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\XmlParser\Parser;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SplFileObject;
use XMLReader;

class HandlerFactoryTest extends TestCase
{
    public function testCreateCsvReaderHandler(): void
    {
        $csvControl = [
            'delimiter' => ';',
            'enclosure' => "'"
        ];

        $handler = (new HandlerFactory())->createReaderHandler(CsvReaderHandler::class, 'php://memory', $csvControl);

        /** @var SplFileObject $innerReader */
        $innerReader = $this->getPropertyValue($handler, 'reader');

        $this->assertInstanceOf(CsvReaderHandler::class, $handler);
        $this->assertSame(SplFileObject::READ_CSV, $innerReader->getFlags());
        $this->assertContains($csvControl['delimiter'], $innerReader->getCsvControl());
        $this->assertContains($csvControl['enclosure'], $innerReader->getCsvControl());
    }

    public function testCreateXmlReaderHandler(): void
    {
        $path = 'some/element';
        $handler = (new HandlerFactory())
            ->createReaderHandler(XmlReaderHandler::class, 'php://memory', compact('path'));
        $pointer = $this->getPropertyValue($handler, 'pointer');

        $this->assertInstanceOf(XmlReaderHandler::class, $handler);
        $this->assertInstanceOf(Parser::class, $this->getPropertyValue($handler, 'reader'));
        $this->assertInstanceOf(XmlPathFilePointer::class, $pointer);
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
        $this->assertInstanceOf(JsonPathFilePointer::class, $pointer);
        $this->assertSame($path, $this->getPropertyValue($pointer, 'path'));
    }

    public function testCreateCustomReaderHandler(): void
    {
        $this->getMockBuilder(CustomReaderHandlerStub::class)
            ->setMockClassName('CustomHandler')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $handler = (new HandlerFactory())
            ->createReaderHandler('CustomHandler', 'php://memory');

        $this->assertInstanceOf(SplFileObject::class, $handler->reader);
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
