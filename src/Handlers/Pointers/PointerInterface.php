<?php

namespace DMT\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\ReaderReadException;

interface PointerInterface
{
    /**
     * Set the file pointer to the first possible chunk to read.
     *
     * @param object $reader The inner reader that reads the file.
     * @return void
     * @throws ReaderReadException
     */
    public function setPointer($reader): void;
}
