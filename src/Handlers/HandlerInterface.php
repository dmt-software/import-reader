<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\ReaderReadException;

interface HandlerInterface
{
    /**
     * Set the pointer to the right part of the file.
     *
     * @param int $skip The amount ot items to skip.
     * @return void
     * @throws ReaderReadException
     */
    public function setPointer(int $skip = 0): void;

    /**
     * Read through a file.
     *
     * @return string|string[]
     * @throws ExceptionInterface
     */
    public function read(): iterable;
}
