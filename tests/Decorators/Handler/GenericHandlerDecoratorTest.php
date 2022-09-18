<?php

namespace DMT\Test\Import\Reader\Decorators\Handler;

use ArrayObject;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use PHPUnit\Framework\TestCase;

class GenericHandlerDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param string|array $row
     * @param object $expected
     * @return void
     */
    public function testDecorate($row, object $expected): void
    {
        $decorator = new GenericHandlerDecorator();

        $this->assertEquals($expected, $decorator->decorate($row));
    }

    public function provideRow(): iterable
    {
        $xml  = '<book><title>Some title</title><author/></book>';
        $json = '{"book": {"title": "Some title", "author": null}}';
        $csv  = ['col1' => 'Some title', 'col2' => null];

        return [
            'xml' => [$xml, simplexml_load_string($xml)],
            'json' => [$json, json_decode($json)],
            'csv' => [array_values($csv), new ArrayObject($csv)],
        ];
    }

    /**
     * @dataProvider provideFailure
     *
     * @param mixed $row
     * @param DecoratorException $exception
     * @return void
     */
    public function testFailure($row, DecoratorException $exception): void
    {
        $this->expectExceptionObject($exception);

        $decorator = new GenericHandlerDecorator();
        $decorator->decorate($row);
    }

    public function provideFailure(): iterable
    {
        return [
            'null' => [null, new DecoratorException('Type mismatch')],
            'object' => [$this, new DecoratorException('Type mismatch')],
            'csv-string' => ['title;author', new DecoratorException('Type mismatch')],
        ];
    }
}
