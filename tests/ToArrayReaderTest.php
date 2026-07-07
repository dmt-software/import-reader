<?php

namespace DMT\Test\Import\Reader;

use Closure;
use DMT\Import\Reader\ReaderBuilder;
use DMT\Import\Reader\ToArrayReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ToArrayReaderTest extends TestCase
{
    #[DataProvider('provideFile')]
    public function testReadToArray(string $file, array $options): void
    {
        $reader = new ToArrayReader(new ReaderBuilder()->createHandler($file, $options), $options);

        foreach ($reader->read() as $array) {
            $this->assertIsArray($array);
        }
    }

    public static function provideFile(): iterable
    {
        return [
            [__DIR__ . '/files/cars.xml', ['path' => '/cars/car']],
            [__DIR__ . '/files/programming.json', ['path' => '.']],
            [__DIR__ . '/files/planes.csv', ['mapping' => ['make&model', 'speed', 'seats']]],
        ];
    }

    #[DataProvider('provideFileWithFilter')]
    public function testReadToArrayWithFilter(string $file, array $options, Closure $filter, int $unExpectedKey): void
    {
        $reader = new ToArrayReader(new ReaderBuilder()->createHandler($file, $options), $options);

        $keys = [];
        foreach ($reader->read(0, $filter) as $key => $array) {
            $keys[] = $key;
            $this->assertIsArray($array);
        }

        $this->assertNotContains($unExpectedKey, $keys);
    }

    public static function provideFileWithFilter(): iterable
    {
        $files = self::provideFile();

        return [
            array_merge(
                $files[0], [
                fn(array $car) => $car['make'] !== 'Fiat',
                2,
            ]),
            array_merge(
                $files[1], [
                fn(array $plane) => !array_keys($plane) == array_values($plane),
                1,
            ]),
            array_merge(
                $files[2], [
                fn(array $language, int $key) => $key !== 2,
                2,
            ]),
        ];
    }
}
