<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;

/**
 * Csv reader handler.
 *
 * This class handles the reading of a csv file for import.
 */
final class CsvReaderHandler implements HandlerInterface
{
    /** @var resource */
    private $reader;
    private array $csvControl;
    /** @var SanitizerInterface[] */
    private array $sanitizers = [];
    private ?array $currentRow = null;

    /**
     * @param resource $reader
     * @param SanitizerInterface ...$sanitizers
     */
    public function __construct($reader, array $csvControl = [], SanitizerInterface ...$sanitizers)
    {
        $this->reader = $reader;
        $this->sanitizers = $sanitizers;
        $this->csvControl = [
            $csvControl['delimiter'] ?? ',',
            $csvControl['enclosure'] ?? '"',
            $csvControl['escape'] ?? '\\'
        ];
    }

    /**
     * Set file pointer.
     *
     * This sets the file pointer to a specific row.
     *
     * @param int $skip The rows to skip.
     *
     * @throws UnreadableException When the end of the file is reached.
     */
    public function setPointer(int $skip = 0): void
    {
        for ($i = 0; $i <= $skip; $i++) {
            $this->currentRow = fgetcsv($this->reader, null, ...$this->csvControl) ?: null;

            if (feof($this->reader)) {
                throw UnreadableException::eof();
            }
        }
    }

    /**
     * Read the file.
     *
     * Empty csv lines will be ignored.
     * During the reading process the handler might sanitize the rows retrieved from the file.
     *
     * @return iterable
     *
     * @see SanitizerInterface
     */
    public function read(): iterable
    {
        $processed = 0;
        do {
            $currentRow = $this->currentRow;
            if ($currentRow && array_filter($currentRow)) {
                foreach ($this->sanitizers as $sanitizer) {
                    $currentRow = $sanitizer->sanitize($currentRow);
                }
                yield ++$processed => $currentRow;
            }

            $this->currentRow = fgetcsv($this->reader, null, ...$this->csvControl) ?: null;
        } while (!feof($this->reader));

        fclose($this->reader);
    }
}
