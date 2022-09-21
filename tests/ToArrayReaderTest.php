<?php

namespace DMT\Test\Import\Reader;

use Closure;
use DMT\Import\Reader\ReaderBuilder;
use DMT\Import\Reader\ToArrayReader;
use PHPUnit\Framework\TestCase;

class ToArrayReaderTest extends TestCase
{
    /**
     * @dataProvider provideFile
     *
     * @param string $file
     * @param array $options
     */
    public function testReadToArray(string $file, array $options): void
    {
        $reader = new ToArrayReader((new ReaderBuilder())->createHandler($file, $options), $options);

        foreach ($reader->read() as $array) {
            $this->assertIsArray($array);
        }
    }

    public function provideFile(): iterable
    {
        return [
            'xml' => [__DIR__ . '/files/cars.xml', ['path' => '/cars/car']],
            'json' => [__DIR__ . '/files/programming.json', ['path' => '.']],
            'csv' => [__DIR__ . '/files/planes.csv', ['mapping' => ['make&model', 'speed', 'seats']]],
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
        $reader = new ToArrayReader((new ReaderBuilder())->createHandler($file, $options), $options);

        $keys = [];
        foreach ($reader->read(0, $filter) as $key => $array) {
            $keys[] = $key;
            $this->assertIsArray($array);
        }

        $this->assertNotContains($unExpectedKey, $keys);
    }

    public function provideFileWithFilter(): iterable
    {
        $files = $this->provideFile();

        return [
            'xml skip one' => array_merge(
                $files['xml'], [
                function (array $car) {
                    return $car['make'] !== 'Fiat';
                },
                2,
            ]),
            'csv skip header' => array_merge(
                $files['csv'], [
                function (array $plane) {
                    return !array_keys($plane) == array_values($plane);
                },
                1,
            ]),
            'json skip one' => array_merge(
                $files['json'], [
                function (array $language, int $key) {
                    return $key <> 2;
                },
                2,
            ]),
        ];
    }
}
