<?php

namespace DMT\Test\Import\Reader\Handlers\FilePointers;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use XMLReader;

class XmlPathFilePointerTest extends TestCase
{
    /**
     * @dataProvider provideXml
     *
     * @param string $xml
     * @param string $path
     * @param int $skip
     * @param string $expected
     */
    public function testSetPointer(string $xml, string $path, int $skip, string $expected): void
    {
        $reader = new XMLReader();
        $reader->XML($xml, 'UTF-8');

        $pointer = new XMLPathFilePointer($path);
        $pointer->seek($reader, $skip);

        $this->assertEquals($expected, $reader->readOuterXml());
    }

    public function provideXml(): iterable
    {
        $xmlString = trim(preg_replace("~\r\n~", "\n", file_get_contents(__DIR__ . '/../../files/cars.xml')));
        $xml = simplexml_load_string($xmlString);

        return [
            'full contents' => [$xmlString, '', 0, $xmlString],
            'first car in xml' => [$xmlString, 'cars/car', 0, $xml->car[0]->asXML()],
            'third car in xml' => [$xmlString, 'cars/car', 2, $xml->car[2]->asXML()],
            'first model of first car in xml' => [
                $xmlString,
                'cars/car/models/model',
                0,
                $xml->car[0]->models->model[0]->asXML()
            ],
        ];
    }

    /**
     * @dataProvider provideFailure
     *
     * @param string $xml
     * @param string $path
     * @param int $skip
     * @param ExceptionInterface|RuntimeException $exception
     */
    public function testFailures(string $xml, string $path, int $skip, ExceptionInterface $exception): void
    {
        $this->expectExceptionObject($exception);

        $reader = new XMLReader();
        $reader->XML($xml, 'UTF-8');

        $pointer = new XMLPathFilePointer($path);
        $pointer->seek($reader, $skip);
    }

    public function provideFailure(): iterable
    {
        $xmlString = trim(preg_replace("~\r\n~", "\n", file_get_contents(__DIR__ . '/../../files/cars.xml')));

        return [
            'path not found' => [$xmlString, 'car/models', 0, UnreadableException::pathNotFound('car/models')],
            'end of file reached' => [$xmlString, 'cars/car/models/model', 4, UnreadableException::eof()],
            'error reading xml' => ['<?xml>' . $xmlString, 'xml', 0, UnreadableException::unreadable('xml')],
        ];
    }
}
