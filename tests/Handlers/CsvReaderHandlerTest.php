<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use PHPUnit\Framework\TestCase;
use SplFileObject;

class CsvReaderHandlerTest extends TestCase
{
    public function testSetPointer(): void
    {
        $handler = new CsvReaderHandler(new SplFileObject(__DIR__ . '/../files/planes.csv'));
        $handler->setPointer(2);

        $this->assertIsArray($handler->read()->current());
    }

    public function testSetPointerFailure(): void
    {
        $this->expectExceptionObject(UnreadableException::eof());

        $handler = new CsvReaderHandler(new SplFileObject(__DIR__ . '/../files/planes.csv'));
        $handler->setPointer(4);
    }

    public function testRead(): void
    {
        $handler = new CsvReaderHandler(new SplFileObject(__DIR__ . '/../files/planes.csv'));

        foreach ($handler->read() as $row => $values) {
            $this->assertIsArray($values);
        }

        $this->assertSame(4, $row);
    }
}
