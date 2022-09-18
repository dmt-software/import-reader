<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use PHPUnit\Framework\TestCase;
use XMLReader;

class XmlReaderHandlerTest extends TestCase
{
    public function testRead(): void
    {
        $xml = '<import><type>json</type><type>xml</type><type>csv</type></import>';

        $xmlReader = new XMLReader();
        $xmlReader->XML($xml);

        $handler = new XmlReaderHandler($xmlReader, new XmlPathFilePointer('import/type'));
        $handler->setPointer(0);

        foreach ($handler->read() as $row => $values) {
            $this->assertStringContainsString('<type>', $values);
        }

        $this->assertSame(3, $row);
    }
}
