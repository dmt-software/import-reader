<?php

namespace DMT\Test\Import\Reader\Integration;

use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Handlers\Sanitizers\EncodingSanitizer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\Import\Reader\Helpers\SourceHelper;
use DMT\Import\Reader\Reader;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * @group integration
 */
class XmlReaderTest extends TestCase
{
    use TestForIntegration;

    /**
     * @dataProvider provideXmlFile
     *
     * @param string|resource $file
     * @return void
     */
    public function testImportXml($file): void
    {
        $reader = new Reader(
            $this->handlerFactory->createReaderHandler(
                XmlReaderHandler::class,
                $file,
                SourceHelper::detect($file),
                ['encoding' => 'UTF-8', 'path' => 'cars/car'],
                [new EncodingSanitizer('UTF-8', 'ASCII//TRANSLIT')]
            ),
            new GenericHandlerDecorator()
        );

        foreach ($reader->read(1) as $row => $car) {
            $this->assertInstanceOf(SimpleXMLElement::class, $car);
            $this->assertNotContains('ë', array_map('strval', iterator_to_array($car->models->model)));
        }

        $this->assertSame(3, $row);
    }

    public function provideXmlFile(): iterable
    {
        $file = __DIR__ . '/../files/cars.xml';

        return [
            'local file' => [$file],
            'file uri' => ['file://' . realpath($file)],
            'stream' => [fopen($file, 'r')],
            'contents' => [file_get_contents($file)],
        ];
    }
}
