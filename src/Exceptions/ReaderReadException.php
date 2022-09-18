<?php

namespace DMT\Import\Reader\Exceptions;

use RuntimeException;

class ReaderReadException extends RuntimeException implements ExceptionInterface
{
    public static function readError(int $position): self
    {
        return self::create('Reader error at position %d', $position);
    }

    public static function create(string $message, ...$args): self
    {
        return new self(vsprintf($message, $args));
    }
}
