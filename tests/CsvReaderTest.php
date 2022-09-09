<?php

namespace DMT\Test\Import\Reader;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Decorators\GenericToObjectDecorator;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use DMT\Import\Reader\Reader;
use PHPUnit\Framework\TestCase;
use SplFileObject;

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
            new CsvReaderHandler(
                new SplFileObject($file),
                new TrimSanitizer('.', TrimSanitizer::TRIM_RIGHT)
            ),
            new GenericToObjectDecorator(),
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
        $file = __DIR__ . '/files/planes.csv';
        $temp = tempnam(sys_get_temp_dir(), 'php');
        $data = file_get_contents($file);
        $data .= PHP_EOL;
        $handle = fopen($temp, 'w');
        fwrite($handle, $data);
        fclose($handle);

        return  [
            'local file' => [$file],
            'stream' => ['file://' . realpath($file)],
            'file with new line' => [$temp],
        ];
    }
}
