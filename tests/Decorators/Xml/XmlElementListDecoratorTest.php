<?php

namespace DMT\Test\Import\Reader\Decorators\Xml;

use DMT\Import\Reader\Decorators\Xml\XmlElementListDecorator;
use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class XmlElementListDecoratorTest extends TestCase
{
    public function testDecorate(): void
    {
        $currentRow = simplexml_load_string('
            <program>
                <license>open source</license>
                <languages>
                    <language><name>javascript</name><since>1995</since><by>Brendan Eich</by></language>
                    <language><name>php</name><since>1995</since><by>Rasmus Lerdorf</by></language>
                </languages>
            </program>');

        $decorator = new XmlElementListDecorator('languages/language');
        $currentRow = $decorator->decorate($currentRow);

        $this->assertInstanceOf(Generator::class, $currentRow);
        $this->assertCount(2, iterator_to_array($currentRow));
    }

    public function testDecorateEmpty(): void
    {
        $currentRow = simplexml_load_string('
            <program>
                <license>open source</license>
                <languages>
                    <language><name>javascript</name><since>1995</since><by>Brendan Eich</by></language>
                    <language><name>php</name><since>1995</since><by>Rasmus Lerdorf</by></language>
                </languages>
            </program>');

        $decorator = new XmlElementListDecorator('languages/not-found');
        $currentRow = $decorator->decorate($currentRow);

        $this->assertInstanceOf(Generator::class, $currentRow);
        $this->assertCount(0, iterator_to_array($currentRow));
    }

    public function testDecoratorFailure(): void
    {
        $currentRow = simplexml_load_string('
            <program>
                <license>open source</license>
                <languages>
                    <language><name>javascript</name><since>1995</since><by>Brendan Eich</by></language>
                    <language><name>php</name><since>1995</since><by>Rasmus Lerdorf</by></language>
                </languages>
            </program>');

        $this->expectExceptionObject(new RuntimeException('Invalid xpath expression'));

        $decorator = new XmlElementListDecorator('[error()="');
        $decorator->decorate($currentRow)->current();

    }
}
