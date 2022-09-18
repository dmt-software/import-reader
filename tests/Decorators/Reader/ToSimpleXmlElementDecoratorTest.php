<?php

namespace DMT\Test\Import\Reader\Decorators\Reader;

use DMT\Import\Reader\Decorators\Reader\ToSimpleXmlElementDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class ToSimpleXmlElementDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideXml
     *
     * @param string $currentRow
     * @param string|null $namespace
     */
    public function testDecorate(string $currentRow, string $namespace = null)
    {
        $bookXml = (new ToSimpleXmlElementDecorator($namespace))->decorate($currentRow);

        $this->assertNotEmpty(strval($bookXml->title));
        $this->assertInstanceOf(SimpleXMLElement::class, $bookXml->title);
        $this->assertInstanceOf(SimpleXMLElement::class, $bookXml->author);
    }

    public function provideXml(): iterable
    {
        return [
            'xml' => [
                '<book><title>Some title</title><author/></book>',
            ],
            'xml with namespace' => [
                '<ns1:book xmlns:ns1="example-ns"><ns1:title>Some title</ns1:title><ns1:author/></ns1:book>',
                'example-ns'
            ],
        ];
    }

    /**
     * @dataProvider provideFailure
     *
     * @param string $currentRow
     * @param ExceptionInterface $exception
     */
    public function testFailure($currentRow, ExceptionInterface $exception): void
    {
        $this->expectExceptionObject($exception);

        $decorator = new ToSimpleXmlElementDecorator();
        $decorator->decorate($currentRow);
    }

    public function provideFailure(): iterable
    {
        return [
            'empty xml' => ['', DecoratorException::create('Invalid xml')],
            'json' => ['{"book":{}}', DecoratorException::create('Invalid xml')],
            'csv' => [['col1' => 'title'], DecoratorException::create('Invalid xml')],
        ];
    }
}
