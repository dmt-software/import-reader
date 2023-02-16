<?php

namespace DMT\Import\Reader;

use Closure;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Tokenizer;
use Iterator;
use pcrov\JsonReader\JsonReader;

interface ReaderInterface
{
    public const JSON_FLOATS_AS_STRINGS = JsonReader::FLOATS_AS_STRINGS;
    public const XML_DROP_NAMESPACES = Tokenizer::XML_DROP_NAMESPACES;
    public const XML_USE_CDATA = Tokenizer::XML_USE_CDATA;

    /**
     * Read through a file.
     *
     * @param int $skip The number of lines or items to skip.
     * @param Closure|null $filter A callback filter to apply.
     * @return Iterator A list of items retrieved from a file.
     * @throws ReaderReadException When the reader can not continue to read from file.
     */
    public function read(int $skip = 0, Closure $filter = null): Iterator;
}
