<?php

namespace DMT\Test\Import\Reader\Decorators\Json;

use DMT\Import\Reader\Decorators\Json\JsonToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Test\Import\Reader\Fixtures\Language;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

class JsonToObjectDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param stdClass $currentRow
     * @param array $mapping
     * @param Language $expected
     */
    public function testDecorate(stdClass $currentRow, array $mapping, Language $expected): void
    {
        $decorator = new JsonToObjectDecorator(Language::class, $mapping);

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public function provideRow(): iterable
    {
        return [
            'simple object' => [
                (object)['name' => 'php', 'since' => '1995', 'by' => 'Rasmus Lerdorf'],
                ['name' => 'name', 'since' => 'since', 'by' => 'author'],
                new Language('php', 1995, 'Rasmus Lerdorf'),
            ],
            'nested objects mapping' => [
                (object)[
                    'language' => 'php',
                    'by' => (object)['author' => 'Rasmus Lerdorf', 'year' => 1995]
                ],
                ['language' => 'name', 'by.author' => 'author', 'by.year' => 'since'],
                new Language('php', 1995, 'Rasmus Lerdorf'),
            ],
        ];
    }

    /**
     * @dataProvider provideFailure
     *
     * @param stdClass $currentRow
     * @param ExceptionInterface|RuntimeException $exception
     * @return void
     */
    public function testFailure(stdClass $currentRow, ExceptionInterface $exception)
    {
        $this->expectExceptionObject($exception);

        $decorator = new JsonToObjectDecorator(
            Language::class,
            ['name' => 'name', 'year' => 'since', 'by' => 'author']
        );

        $decorator->decorate($currentRow);
    }

    public function provideFailure(): iterable
    {
        $message = 'Can not set %s on %s';

        return [
            'set null on property type string' => [
                (object)['name' => '', 'year' => 1970, 'by' => null],
                DecoratorException::create($message, 'author', Language::class),
            ],
            'set null on property type int' => [
                (object)['name' => '', 'year' => null, 'by' => ''],
                DecoratorException::create($message, 'since', Language::class),
            ],
        ];
    }
}
