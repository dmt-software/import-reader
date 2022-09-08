<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Handlers\Pointers\PointerInterface;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use pcrov\JsonReader\JsonReader;

final class JsonReaderHandler implements HandlerInterface
{
    private JsonReader $reader;
    private PointerInterface $pointer;
    /** @var SanitizerInterface[] */
    private array $sanitizers = [];

    /**
     * @param JsonReader $reader
     * @param PointerInterface $pointer
     * @param SanitizerInterface[] $sanitizers
     */
    public function __construct(
        JsonReader         $reader,
        PointerInterface   $pointer,
        SanitizerInterface ...$sanitizers
    ) {
        $this->reader = $reader;
        $this->pointer = $pointer;
        $this->sanitizers = $sanitizers;
    }


    public function setPointer(int $offset = 0): void
    {
        $this->pointer->setPointer($this->reader);

        $position = -1;
        while ($position <= $offset - 1) {
            if (($data = $this->read()) === null) {
                throw new ReaderReadException('End of file reached');
            }
            [$position => $data] = $data;
        }
    }

    public function read(): iterable
    {
        $depth = max($this->reader->depth() - 1, 0);
        $processed = 0;

        do {
            yield $processed++ => $this->sanitize($this->reader->value());
        } while ($this->reader->next() && $this->reader->depth() > $depth);
    }

    private function sanitize(string $currentRow): string
    {
        foreach ($this->sanitizers as $sanitizer) {
            $currentRow = $sanitizer->sanitize($currentRow);
        }

        return $currentRow;
    }
}
