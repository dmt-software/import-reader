<?php

namespace DMT\Test\Import\Reader\Decorators\Reader;

use ArrayObject;
use DMT\Import\Reader\Decorators\Reader\GenericReaderDecorator;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use PHPUnit\Framework\TestCase;

class GenericToObjectDecoratorTest extends TestCase
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
        $decorator = new GenericReaderDecorator();

        $this->assertEquals($expected, $decorator->apply($row));
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
     * @param DecoratorApplyException $exception
     * @return void
     */
    public function testFailure($row, DecoratorApplyException $exception): void
    {
        $this->expectExceptionObject($exception);

        $decorator = new GenericReaderDecorator();
        $decorator->apply($row);
    }

    public function provideFailure(): iterable
    {
        return [
            'null' => [null, new DecoratorApplyException('Type mismatch')],
            'object' => [$this, new DecoratorApplyException('Type mismatch')],
            'csv-string' => ['title;author', new DecoratorApplyException('Type mismatch')],
        ];
    }
}
