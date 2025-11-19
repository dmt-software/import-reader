<?php

namespace DMT\Test\Import\Reader\Decorators\Handler;

use ArrayObject;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GenericHandlerDecoratorTest extends TestCase
{
    #[DataProvider('provideRow')]
    public function testDecorate($row, object $expected): void
    {
        $decorator = new GenericHandlerDecorator();

        $this->assertEquals($expected, $decorator->decorate($row));
    }

    public static function provideRow(): iterable
    {
        $xml  = '<book><title>Some title</title><author/></book>';
        $json = '{"book": {"title": "Some title", "author": null}}';
        $csv  = ['col1' => 'Some title', 'col2' => null];

        return [
            [$xml, simplexml_load_string($xml)],
            [$json, json_decode($json)],
            [array_values($csv), new ArrayObject($csv)],
        ];
    }

    #[DataProvider('provideFailure')]
    public function testFailure(mixed $row, DecoratorException $exception): void
    {
        $this->expectExceptionObject($exception);

        $decorator = new GenericHandlerDecorator();
        $decorator->decorate($row);
    }

    public static function provideFailure(): iterable
    {
        return [
            [null, new DecoratorException('Type mismatch')],
            [new \stdClass(), new DecoratorException('Type mismatch')],
            ['title;author', new DecoratorException('Type mismatch')],
        ];
    }
}
