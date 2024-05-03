<?php

namespace DMT\Test\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\MergeColumnsDecorator;
use PHPUnit\Framework\TestCase;

class MergeColumnsDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param ArrayObject $currentRow
     * @param array $columns
     * @param string|null $columnName
     * @param ArrayObject $expected
     *
     * @return void
     */
    public function testApply(ArrayObject $currentRow, array $columns, ?string $columnName, ArrayObject $expected): void
    {
        $decorator = new MergeColumnsDecorator($columns, $columnName);

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public function provideRow(): iterable
    {
        return [
            'indexed columns' => [
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f']),
                [1, 2],
                'col4',
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f', 'col4' => ['e', 'f']])
            ],
            'associative array override existing column' => [
                new ArrayObject(['d' => 2, 'e' => 'foo', 'f' => 4]),
                ['d', 'f'],
                'e',
                new ArrayObject(['d' => 2, 'e' => [2, 4], 'f' => 4])
            ],
            'associative array with new named column' => [
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6]),
                ['i', 'j'],
                'k',
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6, 'k' => ['bar', 6]])
            ],
            'result inherit column order from mapping' => [
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6]),
                ['j', 'i'],
                'k',
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6, 'k' => [6, 'bar']])
            ],
            'array with none existing column mapping' => [
                new ArrayObject(['col1' => 1, 'col2' => 2, 'col3' => 4]),
                ['col4', 'col5'],
                'col4',
                new ArrayObject(['col1' => 1, 'col2' => 2, 'col3' => 4, 'col4' => []])
            ],
            'array with partial existing columns' => [
                new ArrayObject(['2^0' => 1, '2^1' => 2, '2^2' => 4, '2^3' => 8]),
                ['2^0', '2^2', '2^4'],
                '2',
                new ArrayObject(['2^0' => 1, '2^1' => 2, '2^2' => 4, '2^3' => 8, '2' => [1, 4]])
            ],
        ];
    }
}
