<?php

namespace DMT\Test\Import\Reader\Integration;

use DMT\Import\Reader\Decorators\Json\JsonToObjectDecorator;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use DMT\Import\Reader\Helpers\SourceHelper;
use DMT\Import\Reader\Reader;
use DMT\Test\Import\Reader\Fixtures\Language;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use stdClass;

#[Group('integration')]
class JsonReaderTest extends TestCase
{
    use TestForIntegration;

    /**
     *
     * @param string|resource $file
     * @return void
     */
    #[DataProvider('provideJsonFile')]
    public function testImportJson($file)
    {
        $reader = new Reader(
            $this->handlerFactory->createReaderHandler(
                JsonReaderHandler::class,
                $file,
                SourceHelper::detect($file),
                ['path' => '.'],
                [new TrimSanitizer()]
            ),
            new GenericHandlerDecorator()
        );

        foreach ($reader->read() as $row => $programming) {
            $this->assertNotNull($programming->languages ?? null);
            $this->assertContainsOnlyInstancesOf(stdClass::class, $programming->languages);
        }

        $this->assertSame(2, $row);
    }

    public static function provideJsonFile(): iterable
    {
        $file = __DIR__ . '/../files/programming.json';

        return [
            [$file],
            ['file://' . realpath($file)],
            [fopen($file, 'r')],
            [file_get_contents($file)],
        ];
    }

    public function testReadJsonIntoDataTransferObjects()
    {
        $reader = new Reader(
            $this->handlerFactory->createReaderHandler(
                JsonReaderHandler::class,
                __DIR__ . '/../files/programming.json',
                SourceHelper::SOURCE_TYPE_FILE,
                ['path' => '.languages']
            ),
            new GenericHandlerDecorator(),
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
