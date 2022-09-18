<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\FilePointers\JsonPathFilePointer;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use pcrov\JsonReader\JsonReader;
use PHPUnit\Framework\TestCase;

class JsonReaderHandlerTest extends TestCase
{
    public function testRead(): void
    {
        $json = '[{"importType": "json"},{"importType": "xml"},{"importType": "csv"}]';

        $jsonReader = new JsonReader();
        $jsonReader->json($json);

        $handler = new JsonReaderHandler($jsonReader, new JsonPathFilePointer('.'));
        $handler->setPointer(0);

        foreach ($handler->read() as $row => $values) {
            $this->assertStringContainsString('"importType"', $values);
        }

        $this->assertSame(3, $row);
    }
}
