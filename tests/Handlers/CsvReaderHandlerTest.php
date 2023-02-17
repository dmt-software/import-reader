<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use PHPUnit\Framework\TestCase;

class CsvReaderHandlerTest extends TestCase
{
    public function testSetPointer(): void
    {
        $handler = new CsvReaderHandler(fopen(__DIR__ . '/../files/planes.csv', 'r'));
        $handler->setPointer(2);

        $this->assertIsArray($handler->read()->current());
    }

    public function testSetPointerFailure(): void
    {
        $this->expectExceptionObject(UnreadableException::eof());

        $handler = new CsvReaderHandler(fopen(__DIR__ . '/../files/planes.csv', 'r'));
        $handler->setPointer(4);
    }

    public function testRead(): void
    {
        $handler = new CsvReaderHandler(fopen(__DIR__ . '/../files/planes.csv', 'r'));

        foreach ($handler->read() as $row => $values) {
            $this->assertIsArray($values);
        }

        $this->assertSame(4, $row);
    }
}
