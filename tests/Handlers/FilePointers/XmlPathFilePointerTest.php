<?php

namespace DMT\Test\Import\Reader\Handlers\FilePointers;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\FileParser;
use DMT\XmlParser\Source\StringParser;
use DMT\XmlParser\Tokenizer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class XmlPathFilePointerTest extends TestCase
{
    /**
     * @dataProvider provideXml
     *
     * @param string $file
     * @param string $path
     * @param int $skip
     * @param string $expected
     */
    public function testSetPointer(string $file, string $path, int $skip, string $expected): void
    {
        $reader = new Parser(
            new Tokenizer(
                new FileParser($file),
                $config['encoding'] ?? null,
                $config['flags'] ?? 0
            )
        );
        $pointer = new XMLPathFilePointer($path);
        $pointer->seek($reader, $skip);

        $this->assertEquals($expected, $reader->parseXml());
    }

    public function provideXml(): iterable
    {
        $file = __DIR__ . '/../../files/cars.xml';
        $xmlString = trim(preg_replace("~(?<=\>)\s+~", '', file_get_contents($file)));
        $xml = simplexml_load_string($xmlString);

        return [
            'full contents' => [$file, '', 0, $xmlString],
            'first car in xml' => [$file, 'cars/car', 0, $xml->car[0]->asXML()],
            'third car in xml' => [$file, 'cars/car', 2, $xml->car[2]->asXML()],
            'first model of first car in xml' => [
                $file,
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

        $reader = new Parser(
            new Tokenizer(
                new StringParser($xml),
                $config['encoding'] ?? null,
                $config['flags'] ?? 0
            )
        );

        $pointer = new XMLPathFilePointer($path);
        $pointer->seek($reader, $skip);
    }

    public function provideFailure(): iterable
    {
        $xmlString = trim(preg_replace("~\r\n~", "\n", file_get_contents(__DIR__ . '/../../files/cars.xml')));

        return [
            'path not found' => [$xmlString, 'car/models', 0, UnreadableException::pathNotFound('car/models')],
            'end of file reached' => [$xmlString, 'cars/car/models/model', 4, UnreadableException::eof()],
        ];
    }
}
