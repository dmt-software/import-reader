<?php

namespace DMT\Test\Import\Reader\Decorators\Xml;

use ArrayObject;
use DMT\Import\Reader\Decorators\Xml\XmlToArrayDecorator;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class XmlToArrayDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param SimpleXMLElement $row
     * @param ArrayObject $expected
     */
    public function testDecorate(SimpleXMLElement $row, ArrayObject $expected): void
    {
        $decorator = new XmlToArrayDecorator();

        $this->assertEquals($expected, $decorator->decorate($row));
    }

    public function provideRow(): iterable
    {
        return [
            'xml element (set value only)' => [
                simplexml_load_string('<type>java</type>'),
                new ArrayObject(['java'])
            ],
            'structured xml' => [
                simplexml_load_string('<lang><type>python</type></lang>'),
                new ArrayObject(['type' => 'python'])
            ],
            'xml with namespace' => [
                simplexml_load_string('<lang xmlns:ns="some-uri"><ns:type>php</ns:type></lang>', null, 0, 'some-uri'),
                new ArrayObject(['type' => 'php'])
            ],
            'nested xml elements' => [
                simplexml_load_string('<root><lang><type>go</type></lang></root>'),
                new ArrayObject(['lang' => ['type' => 'go']])
            ]
        ];
    }

    public function testDecorateWithMapping(): void
    {
        $row = simplexml_load_string('<xml>
            <name>John Do</name>
            <role>Developer</role>
            <language>php</language>
            <language>javascript</language>
            <experience><years>5</years></experience>
        </xml>');

        $decorator = new XmlToArrayDecorator([
            'name' => 'name',
            'experience/years' => 'experience',
            'language' => 'languages'
        ]);

        $this->assertEquals(
            new ArrayObject([
                'name' => 'John Do',
                'experience' => '5',
                'languages' => ['php', 'javascript']
            ]),
            $decorator->decorate($row)
        );
    }
}
