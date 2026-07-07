<?php

declare(strict_types=1);

namespace DMT\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\UnreadableException;

interface PointerInterface
{
    /**
     * Set the file pointer to the first possible chunk to read.
     *
     * @param object $reader The inner reader that reads the file.
     *
     * @throws UnreadableException
     */
    public function seek($reader, int $skip): void;
}
