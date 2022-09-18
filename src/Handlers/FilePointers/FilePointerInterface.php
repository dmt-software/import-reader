<?php

namespace DMT\Import\Reader\Handlers\FilePointers;

use DMT\Import\Reader\Exceptions\UnreadableException;

interface FilePointerInterface
{
    /**
     * Set the file pointer to the first possible chunk to read.
     *
     * @param object $reader The inner reader that reads the file.
     * @param int $skip
     *
     * @throws UnreadableException
     */
    public function seek($reader, int $skip): void;
}
