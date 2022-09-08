<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\Pointers\PointerInterface;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use XMLReader;

final class XmlReaderHandler implements HandlerInterface
{
    private XMLReader $reader;
    private PointerInterface $pointer;
    /** @var SanitizerInterface[] */
    private array $sanitizers = [];

    /**
     * @param XMLReader $reader
     * @param PointerInterface $pointer
     * @param SanitizerInterface ...$sanitizers
     */
    public function __construct(
        XMLReader          $reader,
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
        $processed = 0;
        do {
            if (!$xml = $this->reader->readOuterXml()) {
                throw new UnreadableException('XML can not be read');
            }
            yield $processed++ => $this->sanitize($xml);

            if ($this->reader->next($this->reader->localName) === false) {
                break;
            }
        } while (true);
    }

    private function sanitize(string $currentRow): string
    {
        foreach ($this->sanitizers as $sanitizer) {
            $currentRow = $sanitizer->sanitize($currentRow);
        }

        return $currentRow;
    }
}
