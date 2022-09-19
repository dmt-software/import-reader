<?php

namespace DMT\Test\Import\Reader\Decorators;

use ArrayObject;
use DMT\Import\Reader\Decorators\ToArrayDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use PHPUnit\Framework\TestCase;

class ToArrayDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param object $row
     * @param ArrayObject $expected
     */
    public function testDecorate(object $row, ArrayObject $expected): void
    {
        $decorator = new ToArrayDecorator();

        $this->assertEquals($expected, $decorator->decorate($row));
    }

    public function provideRow(): iterable
    {
        return [
            'xml' => [
                simplexml_load_string('<root><foo>bar</foo></root>'),
                new ArrayObject(['foo' => 'bar'])
            ],
            'json' => [
                json_decode('{"lorem": "ipsum"}'),
                new ArrayObject(['lorem' => 'ipsum'])
            ],
            'csv (pass through)' => [
                $arrayObject = new ArrayObject(['col1' => 'value']), $arrayObject
            ],
        ];
    }

    public function testFailure(): void
    {
        $this->expectException(DecoratorException::class);

        (new ToArrayDecorator())->decorate($this);
    }
}
