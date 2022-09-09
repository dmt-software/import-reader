<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\ReaderReadException;

interface HandlerInterface
{
    /**
     * Set the pointer to the right part of the file.
     *
     * @param int $offset The first line or part to read.
     * @return void
     * @throws ReaderReadException
     */
    public function setPointer(int $offset = 0): void;

    /**
     * Read through a file.
     *
     * @return string|string[]
     * @throws ExceptionInterface
     */
    public function read(): iterable;
}
