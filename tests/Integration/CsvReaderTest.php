<?php

namespace DMT\Test\Import\Reader\Integration;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Decorators\Csv\CsvToObjectDecorator;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use DMT\Import\Reader\Reader;
use DMT\Test\Import\Reader\Fixtures\Plane;
use PHPUnit\Framework\TestCase;

class CsvReaderTest extends TestCase
{
    use TestForIntegration;

    /**
     * @dataProvider provideCsvFile
     *
     * @param string $file
     * @return void
     */
    public function testImportCsv(string $file): void
    {
        $reader = new Reader(
            $this->handlerFactory->createCsvReaderHandler($file, [], new TrimSanitizer('.', TrimSanitizer::TRIM_RIGHT)),
            new GenericHandlerDecorator(),
            new ColumnMappingDecorator(
                [
                    'col1' => 'make&model',
                    'col2' => 'seats',
                    4 => 'wingspan',
                    7 => 'year',
                ]
            )
        );

        foreach ($reader->read(1) as $row => $plane) {
            $this->assertInstanceOf(ArrayObject::class, $plane);
            $this->assertStringEndsNotWith('.', $plane->wingspan);
        }

        $this->assertTrue($this->logger->hasWarningThatContains('Skipped row 3'));
        $this->assertSame(4, $row);
    }

    public function provideCsvFile(): iterable
    {
        $file = __DIR__ . '/../files/planes.csv';
        $temp = tempnam(sys_get_temp_dir(), 'php');
        $data = file_get_contents($file);
        $data .= PHP_EOL;
        $handle = fopen($temp, 'w');
        fwrite($handle, $data);
        fclose($handle);

        return [
            'local file' => [$file],
            'stream' => ['file://' . realpath($file)],
            'file with new line' => [$temp],
        ];
    }

    public function testReadCsvToDataTransferObjects()
    {
        $reader = new Reader(
            $this->handlerFactory->createCsvReaderHandler(
                __DIR__ . '/../files/planes.csv',
                [],
                new TrimSanitizer('.', TrimSanitizer::TRIM_RIGHT)
            ),
            new GenericHandlerDecorator(),
            new CsvToObjectDecorator(Plane::class, [
                'col1' => 'type',
                'col2' => 'speed',
                'col3' => 'seats',
                'col8' => 'year',
            ])
        );

        foreach ($reader->read(1) as $row => $plane) {
            $this->assertInstanceOf(Plane::class, $plane);
        }

        $this->assertTrue($this->logger->hasWarningThatContains('Skipped row 3'));
        $this->assertSame(4, $row);
    }
}
