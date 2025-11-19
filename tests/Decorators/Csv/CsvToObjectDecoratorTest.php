<?php

namespace DMT\Test\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\CsvToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Test\Import\Reader\Fixtures\Language;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CsvToObjectDecoratorTest extends TestCase
{
    #[DataProvider('provideRow')]
    public function testDecorate(ArrayObject $currentRow, array $mapping, Language $expected): void
    {
        $decorator = new CsvToObjectDecorator(Language::class, $mapping);

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public static function provideRow(): iterable
    {
        return [
            [
                new ArrayObject(['col1' => 'php', 'col2' => '1995', 'col3' => 'Rasmus Lerdorf']),
                ['col1' => 'name', 'col2' => 'since', 'col3' => 'author'],
                new Language('php', 1995, 'Rasmus Lerdorf'),
            ],
            [
                new ArrayObject(['language' => 'C#', 'author' => 'Anders Hejlsberg', 'year' => '2000']),
                ['language' => 'name', 'year' => 'since', 'author' => 'author'],
                new Language('C#', 2000, 'Anders Hejlsberg'),
            ],
        ];
    }

    #[DataProvider('provideFailure')]
    public function testFailure(ArrayObject $currentRow, ExceptionInterface|Exception $exception)
    {
        $this->expectExceptionObject($exception);

        $decorator = new CsvToObjectDecorator(
            Language::class,
            ['col1' => 'name', 'col2' => 'since', 'col3' => 'author']
        );

        $decorator->decorate($currentRow);
    }

    public static function provideFailure(): iterable
    {
        $message = 'Can not set %s on %s';

        return [
            [
                new ArrayObject(['col1' => null, 'col2' => '1970', 'col3' => '']),
                DecoratorException::create($message, 'name', Language::class),
            ],
            [
                new ArrayObject(['col1' => '', 'col2' => null, 'col3' => '']),
                DecoratorException::create($message, 'since', Language::class),
            ],
        ];
    }
}
