<?php

namespace DMT\Test\Import\Reader\Integration;

use DMT\Import\Reader\Decorators\Json\JsonToObjectDecorator;
use DMT\Import\Reader\Decorators\Reader\GenericReaderDecorator;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use DMT\Import\Reader\Reader;
use DMT\Test\Import\Reader\Fixtures\Language;
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
        $reader = new Reader(
            $this->handlerFactory->createJsonReaderHandler($file, ['path' => '.'], new TrimSanitizer()),
            new GenericReaderDecorator()
        );

        foreach ($reader->read() as $row => $programming) {
            $this->assertObjectHasAttribute('languages', $programming);
            $this->assertContainsOnlyInstancesOf(stdClass::class, $programming->languages);
        }

        $this->assertSame(2, $row);
    }

    public function provideJsonFile(): iterable
    {
        $file = __DIR__ . '/../files/programming.json';

        return [
            'local file' => [$file],
            'stream' => ['file://' . realpath($file)],
        ];
    }

    public function testReadJsonIntoDataTransferObjects()
    {
        $reader = new Reader(
            $this->handlerFactory->createJsonReaderHandler(
                __DIR__ . '/../files/programming.json',
                ['path' => '.languages'],
                new TrimSanitizer()
            ),
            new GenericReaderDecorator(),
            new JsonToObjectDecorator(Language::class, [
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
