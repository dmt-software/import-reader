<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use SplFileObject;

/**
 * Csv reader handler.
 *
 * This class handles the reading of a csv file for import.
 */
final class CsvReaderHandler implements HandlerInterface
{
    private SplFileObject $reader;
    /** @var SanitizerInterface[] */
    private array $sanitizers = [];

    /**
     * @param SplFileObject $reader
     * @param SanitizerInterface ...$sanitizers
     */
    public function __construct(SplFileObject $reader, SanitizerInterface ...$sanitizers)
    {
        $reader->setFlags(SplFileObject::READ_CSV);

        $this->reader = $reader;
        $this->sanitizers = $sanitizers;
    }

    /**
     * Set file pointer.
     *
     * This sets the file to a specific row.
     *
     * @param int $offset The starting row.
     *
     * @throws ReaderReadException When the end of the file is reached.
     */
    public function setPointer(int $offset = 0): void
    {
        $this->reader->seek($offset);

        if ($this->reader->eof()) {
            throw new ReaderReadException('End of file reached');
        }
    }

    /**
     * Read throw the file.
     *
     * Empty csv lines will be ignored.
     *
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
            $currentRow = $this->reader->current();

            if (array_filter($currentRow)) {
                foreach ($this->sanitizers as $sanitizer) {
                    $currentRow = $sanitizer->sanitize($currentRow);
                }
                yield ++$processed => $currentRow;
            }

            $this->reader->next();
        } while (!$this->reader->eof());
    }
}
