<?php

namespace DMT\Test\Import\Reader;

use DMT\Import\Reader\Decorators\Reader\GenericReaderDecorator;
use DMT\Import\Reader\Handlers\Sanitizers\EncodingSanitizer;
use DMT\Import\Reader\Reader;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

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
            $this->handlerFactory->createXmlReaderHandler(
                $file,
                ['encoding' => 'UTF-8', 'path' => 'cars/car'],
                new EncodingSanitizer('UTF-8', 'ASCII//TRANSLIT')
            ),
            new GenericReaderDecorator()
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
