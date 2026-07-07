<?php

declare(strict_types=1);

namespace DMT\Test\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ColumnMappingDecoratorTest extends TestCase
{
    #[DataProvider('provideMapping')]
    public function testApply(array $mapping, ArrayObject $expected): void
    {
        $decorator = new ColumnMappingDecorator($mapping);

        $row = new ArrayObject([
            'col1' => 'John',
            'col2' => 'Do',
            'col3' => 'male',
            'col4' => 'Main Street 12015',
            'col5' => 'New York',
        ], ArrayObject::ARRAY_AS_PROPS);

        $this->assertEquals($expected, $decorator->decorate($row));
    }

    #[DataProvider('provideMapping')]
    public function testFailure(array $mapping, ArrayObject $row): void
    {
        $this->expectException(DecoratorException::class);

        $row = new ArrayObject([
            'col1' => 'John',
            'col2' => 'Do',
        ], ArrayObject::ARRAY_AS_PROPS);

        new ColumnMappingDecorator($mapping)->decorate($row);
    }

    public static function provideMapping(): iterable
    {
        return [
            [
                ['col1' => 'name', 'col4' => 'address'],
                new ArrayObject(['name' => 'John', 'address' => 'Main Street 12015'])
            ],
            [
                ['name', null, 'sex'],
                new ArrayObject(['name' => 'John', 'sex' => 'male'])
            ],
            [
                [1 => 'lastName', 4 => 'city'],
                new ArrayObject(['lastName' => 'Do', 'city' => 'New York'])
            ],
        ];
    }
}
