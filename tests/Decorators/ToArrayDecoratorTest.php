<?php

namespace DMT\Test\Import\Reader\Decorators;

use ArrayObject;
use DMT\Import\Reader\Decorators\ToArrayDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ToArrayDecoratorTest extends TestCase
{
    #[DataProvider('provideRow')]
    public function testDecorate(object $row, ArrayObject $expected): void
    {
        $decorator = new ToArrayDecorator();

        $this->assertEquals($expected, $decorator->decorate($row));
    }

    public static function provideRow(): iterable
    {
        return [
            [
                simplexml_load_string('<root><foo>bar</foo></root>'),
                new ArrayObject(['foo' => 'bar'])
            ],
            [
                json_decode('{"lorem": "ipsum"}'),
                new ArrayObject(['lorem' => 'ipsum'])
            ],
            [
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
