<?php

namespace DMT\Test\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\CsvToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Test\Import\Reader\Fixtures\Language;
use PHPUnit\Framework\TestCase;

class CsvToObjectDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param ArrayObject $currentRow
     * @param Language $expected
     */
    public function testDecorate(ArrayObject $currentRow, array $mapping, Language $expected): void
    {
        $decorator = new CsvToObjectDecorator(Language::class, $mapping);

        $this->assertEquals($expected, $decorator->apply($currentRow));
    }

    public function provideRow(): iterable
    {
        return [
            'column mapping' => [
                new ArrayObject(['col1' => 'php', 'col2' => '1995', 'col3' => 'Rasmus Lerdorf']),
                ['col1' => 'name', 'col2' => 'since', 'col3' => 'author'],
                new Language('php', 1995, 'Rasmus Lerdorf'),
            ],
            'named mapping' => [
                new ArrayObject(['language' => 'C#', 'author' => 'Anders Hejlsberg', 'year' => '2000']),
                ['language' => 'name', 'year' => 'since', 'author' => 'author'],
                new Language('C#', 2000, 'Anders Hejlsberg'),
            ],
        ];
    }

    /**
     * @dataProvider provideFailure
     *
     * @param ArrayObject $currentRow
     * @param ExceptionInterface $exception
     * @return void
     */
    public function testFailure(ArrayObject $currentRow, ExceptionInterface $exception)
    {
        $this->expectExceptionObject($exception);

        $decorator = new CsvToObjectDecorator(
            Language::class,
            ['col1' => 'name', 'col2' => 'since', 'col3' => 'author']
        );

        $decorator->apply($currentRow);
    }

    public function provideFailure(): iterable
    {
        $message = 'Can not set %s on %s';

        return [
            'set null on property type string' => [
                new ArrayObject(['col1' => null, 'col2' => '1970', 'col3' => '']),
                DecoratorApplyException::create($message, 'name', Language::class),
            ],
            'set null on property type int' => [
                new ArrayObject(['col1' => '', 'col2' => null, 'col3' => '']),
                DecoratorApplyException::create($message, 'since', Language::class),
            ],
        ];
    }
}
