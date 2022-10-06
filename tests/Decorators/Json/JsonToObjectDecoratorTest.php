<?php

namespace DMT\Test\Import\Reader\Decorators\Json;

use DMT\Import\Reader\Decorators\Json\JsonToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Test\Import\Reader\Fixtures\Language;
use DMT\Test\Import\Reader\Fixtures\Program;
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

    public function testDecorateToArrayValues(): void
    {
        $decorator = new JsonToObjectDecorator(Program::class, ['license' => 'license', 'language' => 'languages']);

        /** @var Program $program */
        $program = $decorator->decorate(json_decode('{"license": "open source", "language": "php"}'));

        $this->assertSame('open source', $program->license);
        $this->assertIsArray($program->languages);
        $this->assertContains('php', $program->languages);
    }

    public function testDecorateFromArrayValues(): void
    {
        $decorator = new JsonToObjectDecorator(Program::class, ['licenses' => 'license', 'languages' => 'languages']);

        /** @var Program $program */
        $program = $decorator->decorate(json_decode('{"licenses": ["MIT", "GNU"], "languages": []}'));

        $this->assertSame('MIT', $program->license);
        $this->assertIsArray($program->languages);
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
