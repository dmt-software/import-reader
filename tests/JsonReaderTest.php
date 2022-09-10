<?php

namespace DMT\Test\Import\Reader;

use DMT\Import\Reader\Decorators\GenericToObjectDecorator;
use DMT\Import\Reader\Decorators\Json\ToDataTransferObjectDecorator;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\Pointers\JsonPathPointer;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use DMT\Import\Reader\Reader;
use DMT\Test\Import\Reader\Fixtures\Language;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\TestCase;
use stdClass;

class JsonReaderTest extends TestCase
{
    use TestForIntegration;

    /**
     * @dataProvider provideJsonFile
     *
     * @param string $file
     * @return void
     */
    public function testImportJson(string $file)
    {
        $jsonReader = new JsonReader();
        $jsonReader->open(__DIR__ . '/files/programming.json');

        $reader = new Reader(
            new JsonReaderHandler(
                $jsonReader,
                new JsonPathPointer(),
                new TrimSanitizer()
            ),
            new GenericToObjectDecorator()
        );

        foreach ($reader->read() as $row => $programming) {
            $this->assertObjectHasAttribute('languages', $programming);
            $this->assertContainsOnlyInstancesOf(stdClass::class, $programming->languages);
        }

        $this->assertSame(2, $row);
    }

    public function provideJsonFile(): iterable
    {
        $file = __DIR__ . '/files/programming.json';

        return [
            'local file' => [$file],
            'stream' => ['file://' . realpath($file)],
        ];
    }

    public function testReadJsonIntoDataTransferObjects()
    {
        $jsonReader = new JsonReader();
        $jsonReader->open(__DIR__ . '/files/programming.json');

        $reader = new Reader(
            new JsonReaderHandler(
                $jsonReader,
                new JsonPathPointer('.languages'),
                new TrimSanitizer()
            ),
            new GenericToObjectDecorator(),
            new ToDataTransferObjectDecorator(Language::class, [
                'name' => 'name',
                'since' => 'since',
                'by' => 'author',
            ])
        );

        foreach ($reader->read() as $row => $language) {
            $this->assertInstanceOf(Language::class, $language);
        }

        $this->assertSame(2, $row);
    }
}
