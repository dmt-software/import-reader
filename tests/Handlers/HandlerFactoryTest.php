<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\FilePointers\JsonPathFilePointer;
use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
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

        $handler = (new HandlerFactory())->createCsvReaderHandler('php://memory', $csvControl);

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
        $handler = (new HandlerFactory())->createXmlReaderHandler('php://memory', compact('path'));
        $pointer = $this->getPropertyValue($handler, 'pointer');

        $this->assertInstanceOf(XmlReaderHandler::class, $handler);
        $this->assertInstanceOf(XMLReader::class, $this->getPropertyValue($handler, 'reader'));
        $this->assertInstanceOf(XmlPathFilePointer::class, $pointer);
        $this->assertSame($path, $this->getPropertyValue($pointer, 'path'));
    }

    public function testCreateJsonReaderHandler(): void
    {
        $path = 'some.object';
        $handler = (new HandlerFactory())->createJsonReaderHandler('php://memory', compact('path'));
        $pointer = $this->getPropertyValue($handler, 'pointer');

        $this->assertInstanceOf(JsonReaderHandler::class, $handler);
        $this->assertInstanceOf(JsonReader::class, $this->getPropertyValue($handler, 'reader'));
        $this->assertInstanceOf(JsonPathFilePointer::class, $pointer);
        $this->assertSame($path, $this->getPropertyValue($pointer, 'path'));
    }

    private function getPropertyValue(object $object, $property)
    {
        $reader = new ReflectionProperty($object, $property);
        $reader->setAccessible(true);

        return $reader->getValue($object);
    }
}
