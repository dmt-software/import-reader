<?php

namespace DMT\Test\Import\Reader\Decorators;

use ArrayObject;
use DMT\Import\Reader\Decorators\ToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Test\Import\Reader\Fixtures\Car;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ToObjectDecoratorTest extends TestCase
{
    #[DataProvider('ProvideRow')]
    public function testDecorate(object $currentRow, array $mapping, object $expected): void
    {
        $decorator = new ToObjectDecorator(get_class($expected), $mapping);

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public static function provideRow(): iterable
    {
        return [
            [
                new ArrayObject(['col1' => 'Kia', 'col2' => 'Rio'], ArrayObject::ARRAY_AS_PROPS),
                ['col1' => 'make', 'col2' => 'model'],
                new Car('Kia', 'Rio')
            ],
            [
                json_decode('{"make": "Dacia", "model": "Dokker"}'),
                ['make' => 'make', 'model' => 'model'],
                new Car('Dacia', 'Dokker')
            ],
            [
                simplexml_load_string('<car><make>Mini</make><model>Cooper</model></car>'),
                ['make' => 'make', 'model' => 'model'],
                new Car('Mini', 'Cooper')
            ]
        ];
    }

    public function testFailure(): void
    {
        $this->expectException(DecoratorException::class);

        (new ToObjectDecorator(Car::class, []))->decorate(new Car('BMW', 'S3'));
    }
}
