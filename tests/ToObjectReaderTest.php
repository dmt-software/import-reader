<?php

namespace DMT\Test\Import\Reader;

use Closure;
use DMT\Import\Reader\ReaderBuilder;
use DMT\Import\Reader\ToObjectReader;
use DMT\Test\Import\Reader\Fixtures\Car;
use DMT\Test\Import\Reader\Fixtures\Language;
use DMT\Test\Import\Reader\Fixtures\Plane;
use PHPUnit\Framework\TestCase;

class ToObjectReaderTest extends TestCase
{
    /**
     * @dataProvider provideFile
     *
     * @param string $file
     * @param array $options
     */
    public function testReadToObject(string $file, array $options): void
    {
        $reader = new ToObjectReader((new ReaderBuilder())->createHandler($file, $options), $options);

        foreach ($reader->read() as $object) {
            $this->assertInstanceOf($options['class'], $object);
        }
    }

    public function provideFile(): iterable
    {
        return [
            'xml' => [
                __DIR__ . '/files/cars.xml', [
                    'path' => '/cars/car',
                    'class' => Car::class,
                    'mapping' => ['make' => 'make', 'models' => 'model']
                ]
            ],
            'json' => [
                __DIR__ . '/files/programming.json', [
                    'path' => '.languages',
                    'class' => Language::class,
                    'mapping' => ['name' => 'name', 'since' => 'since', 'by' => 'author']
                ]
            ],
            'csv' => [
                __DIR__ . '/files/planes.csv', [
                    'class' => Plane::class,
                    'mapping' => ['col1' => 'type', 'col2' => 'speed', 'col3' => 'seats'],
                ]
            ],
        ];
    }

    /**
     * @dataProvider provideFileWithFilter
     *
     * @param string $file
     * @param array $options
     * @param Closure $filter
     * @param int $unExpectedKey
     */
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

    public function provideFileWithFilter(): iterable
    {
        $files = $this->provideFile();

        return [
            'xml skip one' => array_merge(
                $files['xml'], [
                function (Car $car, int $key) {
                    return $key <> 2;
                },
                2,
            ]),
            'csv skip header' => array_merge(
                $files['csv'], [
                function (Plane $plane) {
                    $headers = array_keys(get_object_vars($plane));
                    $headers[0] = 'make&model';

                    return !$headers == array_values(get_object_vars($plane));
                },
                1,
            ]),
            'json skip one' => array_merge(
                $files['json'], [
                function (Language $language) {
                    return $language->name !== 'javascript';
                },
                1,
            ]),
        ];
    }
}
