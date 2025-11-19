<?php

namespace DMT\Test\Import\Reader\Decorators\Handler;

use DMT\Import\Reader\Decorators\Handler\ToSimpleXmlElementDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class ToSimpleXmlElementDecoratorTest extends TestCase
{
    #[DataProvider('provideXml')]
    public function testDecorate(string $currentRow, string $namespace = null)
    {
        $bookXml = (new ToSimpleXmlElementDecorator($namespace))->decorate($currentRow);

        $this->assertNotEmpty(strval($bookXml->title));
        $this->assertInstanceOf(SimpleXMLElement::class, $bookXml->title);
        $this->assertInstanceOf(SimpleXMLElement::class, $bookXml->author);
    }

    public static function provideXml(): iterable
    {
        return [
            [
                '<book><title>Some title</title><author/></book>',
            ],
            [
                '<ns1:book xmlns:ns1="example-ns"><ns1:title>Some title</ns1:title><ns1:author/></ns1:book>',
                'example-ns'
            ],
        ];
    }

    #[DataProvider('provideFailure')]
    #[WithoutErrorHandler]
    public function testFailure(mixed $currentRow, ExceptionInterface|Exception $exception): void
    {
        $this->expectExceptionObject($exception);

        set_error_handler(static fn() => null);

        $decorator = new ToSimpleXmlElementDecorator();
        $decorator->decorate($currentRow);
    }

    public static function provideFailure(): iterable
    {
        return [
            ['', DecoratorException::create('Invalid xml')],
            ['{"book":{}}', DecoratorException::create('Invalid xml')],
            [['col1' => 'title'], DecoratorException::create('Invalid xml')],
        ];
    }
}
