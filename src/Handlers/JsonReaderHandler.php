<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\Pointers\PointerInterface;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

final class JsonReaderHandler implements HandlerInterface
{
    /**
     * @var array<SanitizerInterface>
     */
    private array $sanitizers = [];

    /**
     * @param JsonReader $reader
     * @param PointerInterface $pointer
     * @param SanitizerInterface[] $sanitizers
     */
    public function __construct(
        private readonly JsonReader         $reader,
        private readonly PointerInterface   $pointer,
        SanitizerInterface ...$sanitizers
    ) {
        $this->sanitizers = $sanitizers;
    }

    public function setPointer(int $skip = 0): void
    {
        $this->pointer->seek($this->reader, $skip);
    }

    public function read(): iterable
    {
        $depth = max($this->reader->depth() - 1, 0);
        $processed = 0;

        do {
            try {
                $json = json_encode($this->reader->value());

                foreach ($this->sanitizers as $sanitizer) {
                    $json = $sanitizer->sanitize($json);
                }

                yield ++$processed => $json;
            } catch (Exception $exception) {
                throw UnreadableException::unreadable('json', $exception);
            }
        } while ($this->reader->next() && $this->reader->depth() > $depth);

        $this->reader->close();
    }
}
