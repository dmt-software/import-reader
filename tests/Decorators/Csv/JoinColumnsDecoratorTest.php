<?php

namespace DMT\Test\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\JoinColumnsDecorator;
use PHPUnit\Framework\TestCase;

class JoinColumnsDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param ArrayObject $currentRow
     * @param array $columns
     * @param string $columnName
     * @param string|null $separator
     * @param ArrayObject $expected
     *
     * @return void
     */
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

    public function provideRow(): iterable
    {
        return [
            'indexed columns with default separator' => [
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f']),
                [1, 2],
                'col4',
                null,
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f', 'col4' => 'e f'])
            ],
            'named columns with default separator inherits column order' => [
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f']),
                ['col3', 'col1'],
                'col4',
                null,
                new ArrayObject(['col1' => 'd', 'col2' => 'e', 'col3' => 'f', 'col4' => 'f d'])
            ],
            'override existing column' => [
                new ArrayObject(['d' => 'abc', 'e' => 'foo', 'f' => '4']),
                ['d', 'f'],
                'e',
                null,
                new ArrayObject(['d' => 'abc', 'e' => 'abc 4', 'f' => '4'])
            ],
            'join with set separator' => [
                new ArrayObject(['h' => '3', 'i' => 'bar', 'j' => '6']),
                ['i', 'j'],
                'k',
                ',',
                new ArrayObject(['h' => '3', 'i' => 'bar', 'j' => '6', 'k' => 'bar,6'])
            ],
        ];
    }
}
