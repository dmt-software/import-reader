<?php

namespace DMT\Test\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\Pointers\JsonPathPointer;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JsonPathPointerTest extends TestCase
{
    /**
     * @dataProvider provideJson
     *
     * @param string $json
     * @param string $path
     * @param int $skip
     * @param array $expected
     * @throws Exception
     */
    public function testSetPointer(string $json, string $path, int $skip, array $expected): void
    {
        $reader = new JsonReader();
        $reader->json($json);

        $pointer = new JsonPathPointer($path);
        $pointer->seek($reader, $skip);

        $this->assertSame($expected, $reader->value());
    }

    public function provideJson(): iterable
    {
        $jsonString = file_get_contents(__DIR__ . '/../../files/programming.json');
        $json = json_decode($jsonString, true);
        $languages = ['languages' => $json[1]['languages']]; // {"languages": [{"name": ....

        return [
            'full contents of an object' => ['{"objects":' . $jsonString . "}", '', 0, ['objects' => $json]],
            'first object in list' => [$jsonString, '.', 0,  $json[0]],
            'second object in list' => [$jsonString, '.', 1,  $json[1]],
            'array in first object from list' => [$jsonString, '.languages', 0, $json[0]['languages'][0]],
            'array in object' => [json_encode($languages), 'languages', 0, $languages['languages'][0]],
        ];
    }

    /**
     * @dataProvider provideFailure
     *
     * @param string $json
     * @param string $path
     * @param ExceptionInterface|RuntimeException $exception
     */
    public function testFailures(string $json, string $path, int $skip, ExceptionInterface $exception): void
    {
        $this->expectExceptionObject($exception);

        $reader = new JsonReader();
        $reader->json($json);

        $pointer = new JsonPathPointer($path);
        $pointer->seek($reader, $skip);
    }

    public function provideFailure(): iterable
    {
        $jsonString = file_get_contents(__DIR__ . '/../../files/programming.json');

        return [
            'path not found' => [$jsonString, '.lang', 0, UnreadableException::pathNotFound('.lang')],
            'end of file reached' => [$jsonString, '.languages', 2, UnreadableException::eof()],
            'path to scalar' => [$jsonString, '.license', 0, UnreadableException::illegalValue('open source')],
            'error reading json' => ['{"dum"' . $jsonString, 'dum', 0, UnreadableException::unreadable('json')]
        ];
    }
}
