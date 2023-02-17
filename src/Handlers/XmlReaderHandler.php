<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\Pointers\PointerInterface;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use DMT\XmlParser\Parser;

/**
 * Xml reader handler.
 *
 * This class handles the reading of a file into chunks of xml.
 */
final class XmlReaderHandler implements HandlerInterface
{
    private Parser $reader;
    private PointerInterface $pointer;
    /** @var SanitizerInterface[] */
    private array $sanitizers = [];

    /**
     * @param Parser $reader
     * @param PointerInterface $pointer
     * @param SanitizerInterface ...$sanitizers
     */
    public function __construct(
        Parser               $reader,
        PointerInterface     $pointer,
        SanitizerInterface   ...$sanitizers
    ) {
        $this->reader = $reader;
        $this->pointer = $pointer;
        $this->sanitizers = $sanitizers;
    }

    /**
     * Set file pointer.
     *
     * This sets the file to a specific element within the xml.
     *
     * @param int $skip The amount of elements to skip.
     *
     * @throws UnreadableException When the end of the file is reached.
     */
    public function setPointer(int $skip = 0): void
    {
        $this->pointer->seek($this->reader, $skip);
    }

    /**
     * Read the file.
     *
     * During the reading process the handler might sanitize the xml strings retrieved from the file.
     *
     * @return iterable
     *
     * @see SanitizerInterface
     */
    public function read(): iterable
    {
        $processed = 0;
        do {
            if (!$xml = $this->reader->parseXml()) {
                throw UnreadableException::unreadable('xml');
            }

            foreach ($this->sanitizers as $sanitizer) {
                $xml = $sanitizer->sanitize($xml);
            }

            yield ++$processed => $xml;

            if (!$this->reader->parse()) {
                break;
            }
        } while (true);
    }
}
