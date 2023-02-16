<?php

namespace DMT\Test\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\StringParser;
use DMT\XmlParser\Tokenizer;
use PHPUnit\Framework\TestCase;

class XmlReaderHandlerTest extends TestCase
{
    public function testRead(): void
    {
        $xml = '<import><type>json</type><type>xml</type><type>csv</type></import>';

        $parser = new Parser(
            new Tokenizer(
                new StringParser($xml),
                $config['encoding'] ?? null,
                $config['flags'] ?? 0
            )
        );

        $handler = new XmlReaderHandler($parser, new XmlPathFilePointer('import/type'));
        $handler->setPointer(0);

        foreach ($handler->read() as $row => $values) {
            $this->assertStringContainsString('<type>', $values);
        }

        $this->assertSame(3, $row);
    }
}
