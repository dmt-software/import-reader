<?php

namespace DMT\Test\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\Pointers\JsonPathPointer;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JsonPathPointerTest extends TestCase
{
    /**
     *
     * @param string $json
     * @param string $path
     * @param int $skip
     * @param array $expected
     * @throws Exception
     */
    #[DataProvider('provideJson')]
    public function testSetPointer(string $json, string $path, int $skip, array $expected): void
    {
        $reader = new JsonReader();
        $reader->json($json);

        $pointer = new JsonPathPointer($path);
        $pointer->seek($reader, $skip);

        $this->assertSame($expected, $reader->value());
    }

    public static function provideJson(): iterable
    {
        $jsonString = file_get_contents(__DIR__ . '/../../files/programming.json');
        $json = json_decode($jsonString, true);
        $languages = ['languages' => $json[1]['languages']]; // {"languages": [{"name": ....

        return [
            ['{"objects":' . $jsonString . "}", '', 0, ['objects' => $json]],
            [$jsonString, '.', 0,  $json[0]],
            [$jsonString, '.', 1,  $json[1]],
            [$jsonString, '.languages', 0, $json[0]['languages'][0]],
            [json_encode($languages), 'languages', 0, $languages['languages'][0]],
        ];
    }

    /**
     *
     * @param string $json
     * @param string $path
     * @param ExceptionInterface|RuntimeException $exception
     */
    #[DataProvider('provideFailure')]
    public function testFailures(string $json, string $path, int $skip, ExceptionInterface $exception): void
    {
        $this->expectExceptionObject($exception);

        $reader = new JsonReader();
        $reader->json($json);

        $pointer = new JsonPathPointer($path);
        $pointer->seek($reader, $skip);
    }

    public static function provideFailure(): iterable
    {
        $jsonString = file_get_contents(__DIR__ . '/../../files/programming.json');

        return [
            [$jsonString, '.lang', 0, UnreadableException::pathNotFound('.lang')],
            [$jsonString, '.languages', 2, UnreadableException::eof()],
            [$jsonString, '.license', 0, UnreadableException::illegalValue('open source')],
            ['{"dum"' . $jsonString, 'dum', 0, UnreadableException::unreadable('json')]
        ];
    }
}
