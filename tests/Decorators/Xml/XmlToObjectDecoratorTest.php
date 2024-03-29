<?php

namespace DMT\Test\Import\Reader\Decorators\Xml;

use DMT\Import\Reader\Decorators\Xml\XmlToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Test\Import\Reader\Fixtures\Language;
use DMT\Test\Import\Reader\Fixtures\Program;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use RuntimeException;
use SimpleXMLElement;

class XmlToObjectDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param SimpleXMLElement $currentRow
     * @param array $mapping
     * @param object $expected
     * @throws ReflectionException
     */
    public function testDecorate(SimpleXMLElement $currentRow, array $mapping, object $expected): void
    {
        $decorator = new XmlToObjectDecorator(get_class($expected), $mapping);

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public function provideRow(): iterable
    {
        return [
            'simple xml to object' => $this->getXmlRow(),
            'xml with namespace to object' => $this->getXmlWithNamespaceRow(),
            'xml to object with simple list' => $this->getXmlWithSimpleListRow(),
            'xml to object with complex list' => $this->getXmlWithComplexListRow()
        ];
    }

    /**
     * @dataProvider provideFailure
     *
     * @param SimpleXMLElement $currentRow
     * @param ExceptionInterface|RuntimeException $exception
     * @throws ReflectionException
     */
    public function testFailure(SimpleXMLElement $currentRow, ExceptionInterface $exception): void
    {
        $this->expectExceptionObject($exception);

        $decorator = new XmlToObjectDecorator(
            Program::class,
            ['license' => 'license', 'language' => 'languages'],
        );

        $decorator->decorate($currentRow);
    }

    public function provideFailure(): iterable
    {
        $message = 'Can not set %s on %s';

        return [
            'set null on property type string' => [
                simplexml_load_string('<program/>'),
                DecoratorException::create($message, 'license', Program::class),
            ],
            'set null on property type array' => [
                simplexml_load_string('<program><license/></program>'),
                DecoratorException::create($message, 'languages', Program::class),
            ],
        ];
    }

    /**
     * Maps element name to object property.
     *
     * @return array
     */
    private function getXmlRow(): array
    {
        $xml = simplexml_load_string('
            <language>
                <name>javascript</name>
                <since>1995</since>
                <by>Brendan Eich</by>
            </language>
        ');
        $mapping = ['name' => 'name', 'since' => 'since', 'by' => 'author'];
        $expected = new Language('javascript', 1995, 'Brendan Eich');

        return [$xml, $mapping, $expected];
    }

    /**
     * Maps ns:element to object property.
     *
     * @return array
     */
    private function getXmlWithNamespaceRow(): array
    {
        $xml = simplexml_load_string('
            <language xmlns:ns1="http://example.dev">
                <ns1:name>javascript</ns1:name>
                <ns1:year>1995</ns1:year>
                <ns1:by>Brendan Eich</ns1:by>
            </language>');
        $mapping = [
            '//*[local-name()="name"]' => 'name',
            '*[local-name()="year"]' => 'since',
            '*[namespace-uri()="http://example.dev" and local-name()="by"]' => 'author'
        ];
        $expected = new Language('javascript', 1995, 'Brendan Eich');

        return [$xml, $mapping, $expected];
    }

    /**
     * Maps a list of xml elements to an array for object property.
     *
     * @return array
     */
    private function getXmlWithSimpleListRow(): array
    {
        $xml = simplexml_load_string('
            <program>
                <license>open source</license>
                <language>javascript</language>
                <language>php</language>
            </program>');
        $mapping = ['license' => 'license', 'language' => 'languages'];
        $expected = new Program('open source', ['javascript', 'php']);

        return [$xml, $mapping, $expected];
    }

    /**
     * Maps a list of xml elements with child nodes to a list of arrays for object property.
     *
     * @return array
     */
    private function getXmlWithComplexListRow(): array
    {
        $xml = simplexml_load_string('
            <program>
                <license>open source</license>
                <languages>
                    <language><name>javascript</name><since>1995</since><by>Brendan Eich</by></language>
                    <language><name>php</name><since>1995</since><by>Rasmus Lerdorf</by></language>
                </languages>
            </program>');
        $mapping = ['license' => 'license', 'languages/language' => 'languages'];
        $expected = new Program(
            'open source',
            [
                ['javascript', '1995', 'Brendan Eich'],
                ['php', '1995', 'Rasmus Lerdorf'],
            ]
        );

        return [$xml, $mapping, $expected];
    }
}
