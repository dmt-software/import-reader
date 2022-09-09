<?php

namespace DMT\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Exceptions\UnreadableException;

interface PointerInterface
{
    /**
     * Set the file pointer to the first possible chunk to read.
     *
     * @param object $reader The inner reader that reads the file.
     * @return void
     * @throws ReaderReadException
     * @throws UnreadableException
     */
    public function setPointer($reader): void;
}
