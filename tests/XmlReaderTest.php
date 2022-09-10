<?php

namespace DMT\Test\Import\Reader;

use DMT\Import\Reader\Decorators\GenericToObjectDecorator;
use DMT\Import\Reader\Handlers\Pointers\XmlPathPointer;
use DMT\Import\Reader\Handlers\Sanitizers\EncodingSanitizer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\Import\Reader\Reader;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use XMLReader;

class XmlReaderTest extends TestCase
{
    use TestForIntegration;

    /**
     * @dataProvider provideXmlFile
     *
     * @param string $file
     * @return void
     */
    public function testImportXml(string $file): void
    {
        $reader = new Reader(
            new XmlReaderHandler(
                XMLReader::open($file, 'UTF-8'),
                new XmlPathPointer('cars/car'),
                new EncodingSanitizer('UTF-8', 'ASCII//TRANSLIT')
            ),
            new GenericToObjectDecorator()
        );

        foreach ($reader->read(1) as $row => $car) {
            $this->assertInstanceOf(SimpleXMLElement::class, $car);
            $this->assertNotContains('ë', array_map('strval', iterator_to_array($car->models->model)));
        }

        $this->assertSame(3, $row);
    }

    public function provideXmlFile(): iterable
    {
        $file = __DIR__ . '/files/cars.xml';

        return [
            'local file' => [$file],
            'stream' => ['file://' . realpath($file)],
        ];
    }
}