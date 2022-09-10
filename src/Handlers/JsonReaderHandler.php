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


    public function setPointer(int $skip = 0): void
    {
        $this->pointer->setPointer($this->reader);

        $depth = max($this->reader->depth() -1, 0);
        $position = 0;
        while (++$position <= $skip) {
            $this->reader->next();

            if ($this->reader->depth() < $depth || !$this->reader->value()) {
                throw new ReaderReadException('End of file reached');
            }
        }
    }

    public function read(): iterable
    {
        $depth = max($this->reader->depth() - 1, 0);
        $processed = 0;

        do {
            $json = json_encode($this->reader->value());

            foreach ($this->sanitizers as $sanitizer) {
                $json = $sanitizer->sanitize($json);
            }

            yield ++$processed => $json;
        } while ($this->reader->next() && $this->reader->depth() > $depth);
    }
}
