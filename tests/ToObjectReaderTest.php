<?php

namespace DMT\Test\Import\Reader;

use Closure;
use DMT\Import\Reader\ReaderBuilder;
use DMT\Import\Reader\ToObjectReader;
use DMT\Test\Import\Reader\Fixtures\Car;
use DMT\Test\Import\Reader\Fixtures\Language;
use DMT\Test\Import\Reader\Fixtures\Plane;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ToObjectReaderTest extends TestCase
{
    #[DataProvider('provideFile')]
    public function testReadToObject(string $file, array $options): void
    {
        $reader = new ToObjectReader((new ReaderBuilder())->createHandler($file, $options), $options);

        foreach ($reader->read() as $object) {
            $this->assertInstanceOf($options['class'], $object);
        }
    }

    public static function provideFile(): iterable
    {
        return [
            [
                __DIR__ . '/files/cars.xml', [
                    'path' => '/cars/car',
                    'class' => Car::class,
                    'mapping' => ['make' => 'make', 'models/model' => 'model']
                ]
            ],
            [
                __DIR__ . '/files/programming.json', [
                    'path' => '.languages',
                    'class' => Language::class,
                    'mapping' => ['name' => 'name', 'since' => 'since', 'by' => 'author']
                ]
            ],
            [
                __DIR__ . '/files/planes.csv', [
                    'class' => Plane::class,
                    'mapping' => ['col1' => 'type', 'col2' => 'speed', 'col3' => 'seats'],
                ]
            ],
        ];
    }

    #[DataProvider('provideFileWithFilter')]
    public function testReadToArrayWithFilter(string $file, array $options, Closure $filter, int $unExpectedKey): void
    {
        $reader = new ToObjectReader((new ReaderBuilder())->createHandler($file, $options), $options);

        $keys = [];
        foreach ($reader->read(0, $filter) as $key => $object) {
            $keys[] = $key;
            $this->assertInstanceOf($options['class'], $object);
        }

        $this->assertNotContains($unExpectedKey, $keys);
    }

    public static function provideFileWithFilter(): iterable
    {
        $files = self::provideFile();

        return [
            array_merge(
                $files[0], [
                function (Car $car, int $key) {
                    return $key <> 2;
                },
                2,
            ]),
            array_merge(
                $files[2], [
                function (Plane $plane) {
                    $headers = array_keys(get_object_vars($plane));
                    $headers[0] = 'make&model';

                    return !$headers == array_values(get_object_vars($plane));
                },
                1,
            ]),
            array_merge(
                $files[1], [
                function (Language $language) {
                    return $language->name !== 'javascript';
                },
                1,
            ]),
        ];
    }
}
