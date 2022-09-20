<?php

namespace DMT\Import\Reader;

use Closure;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use Iterator;

interface ReaderInterface
{
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
