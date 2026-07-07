<?php

declare(strict_types=1);

namespace DMT\Test\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\MergeColumnsDecorator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MergeColumnsDecoratorTest extends TestCase
{
    #[DataProvider('provideRow')]
    public function testApply(ArrayObject $currentRow, array $columns, ?string $columnName, ArrayObject $expected): void
    {
        $decorator = new MergeColumnsDecorator($columns, $columnName);

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public static function provideRow(): iterable
    {
        return [
            [
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f']),
                [1, 2],
                'col4',
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f', 'col4' => ['e', 'f']])
            ],
            [
                new ArrayObject(['d' => 2, 'e' => 'foo', 'f' => 4]),
                ['d', 'f'],
                'e',
                new ArrayObject(['d' => 2, 'e' => [2, 4], 'f' => 4])
            ],
            [
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6]),
                ['i', 'j'],
                'k',
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6, 'k' => ['bar', 6]])
            ],
            [
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6]),
                ['j', 'i'],
                'k',
                new ArrayObject(['h' => 3, 'i' => 'bar', 'j' => 6, 'k' => [6, 'bar']])
            ],
            [
                new ArrayObject(['col1' => 1, 'col2' => 2, 'col3' => 4]),
                ['col4', 'col5'],
                'col4',
                new ArrayObject(['col1' => 1, 'col2' => 2, 'col3' => 4, 'col4' => []])
            ],
            [
                new ArrayObject(['2^0' => 1, '2^1' => 2, '2^2' => 4, '2^3' => 8]),
                ['2^0', '2^2', '2^4'],
                '2',
                new ArrayObject(['2^0' => 1, '2^1' => 2, '2^2' => 4, '2^3' => 8, '2' => [1, 4]])
            ],
        ];
    }
}
