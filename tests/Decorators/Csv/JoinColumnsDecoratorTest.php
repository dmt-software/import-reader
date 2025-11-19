<?php

namespace DMT\Test\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\JoinColumnsDecorator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JoinColumnsDecoratorTest extends TestCase
{
    #[DataProvider('provideRow')]
    public function testDecorate(
        ArrayObject $currentRow,
        array $columns,
        string $columnName,
        ?string $separator,
        ArrayObject $expected
    ): void {
        $decorator = new JoinColumnsDecorator(...array_filter([$columns, $columnName, $separator]));

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public static function provideRow(): iterable
    {
        return [
            [
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f']),
                [1, 2],
                'col4',
                null,
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f', 'col4' => 'e f'])
            ],
            [
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f']),
                ['col3', 'col1'],
                'col4',
                null,
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f', 'col4' => 'f d'])
            ],
            [
                new ArrayObject(['d' => 'abc', 'e' => 'foo', 'f' => '4']),
                ['d', 'f'],
                'e',
                null,
                new ArrayObject(['d' => 'abc', 'e' => 'abc 4', 'f' => '4'])
            ],
            [
                new ArrayObject(['h' => '3', 'i' => 'bar', 'j' => '6']),
                ['i', 'j'],
                'k',
                ',',
                new ArrayObject(['h' => '3', 'i' => 'bar', 'j' => '6', 'k' => 'bar,6'])
            ],
        ];
    }
}
