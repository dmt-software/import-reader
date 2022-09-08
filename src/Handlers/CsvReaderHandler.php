<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Sanitizers\SanitizerInterface;
use SplFileObject;

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

    public function setPointer(int $offset = 0): void
    {
        $this->reader->seek($offset);
    }

    public function read(): iterable
    {
        $processed = 0;
        do {
            $currentRow = $this->reader->current();
            if (!array_filter($currentRow) && $this->reader->eof()) {
                break;
            }
            yield $processed++ => $this->sanitize($currentRow);

            $this->reader->next();
        } while (!$this->reader->eof());
    }

    private function sanitize(array $currentRow): array
    {
        foreach ($this->sanitizers as $sanitizer) {
            $currentRow = $sanitizer->sanitize($currentRow);
        }

        return $currentRow;
    }
}
