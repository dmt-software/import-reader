<?php

namespace DMT\Test\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\Pointers\XmlPathPointer;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\FileParser;
use DMT\XmlParser\Source\StringParser;
use DMT\XmlParser\Tokenizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class XmlPathPointerTest extends TestCase
{
    #[DataProvider('provideXml')]
    public function testSetPointer(string $file, string $path, int $skip, string $expected): void
    {
        $reader = new Parser(
            new Tokenizer\XmlReaderTokenizer(
                new FileParser($file),
                $config['encoding'] ?? null,
                $config['flags'] ?? 0
            )
        );
        $pointer = new XmlPathPointer($path);
        $pointer->seek($reader, $skip);

        $this->assertEquals($expected, $reader->parseXml());
    }

    public static function provideXml(): iterable
    {
        $file = __DIR__ . '/../../files/cars.xml';
        $xmlString = trim((string) preg_replace("~(?<=>)\s+~", '', file_get_contents($file)));
        $xml = simplexml_load_string($xmlString);

        return [
            [$file, '', 0, $xmlString],
            [$file, 'cars/car', 0, $xml->car[0]->asXML()],
            [$file, 'cars/car', 2, $xml->car[2]->asXML()],
            [
                $file,
                'cars/car/models/model',
                0,
                $xml->car[0]->models->model[0]->asXML()
            ],
        ];
    }

    /**
     *
     * @param ExceptionInterface|RuntimeException $exception
     */
    #[DataProvider('provideFailure')]
    public function testFailures(string $xml, string $path, int $skip, ExceptionInterface $exception): void
    {
        $this->expectExceptionObject($exception);

        $reader = new Parser(
            new Tokenizer\XmlReaderTokenizer(
                new StringParser($xml),
                $config['encoding'] ?? null,
                $config['flags'] ?? 0
            )
        );

        $pointer = new XmlPathPointer($path);
        $pointer->seek($reader, $skip);
    }

    public static function provideFailure(): iterable
    {
        $xmlString = trim((string) preg_replace("~\r\n~", "\n", file_get_contents(__DIR__ . '/../../files/cars.xml')));

        return [
            [$xmlString, 'car/models', 0, UnreadableException::pathNotFound('car/models')],
            [$xmlString, 'cars/car/models/model', 4, UnreadableException::eof()],
        ];
    }
}
