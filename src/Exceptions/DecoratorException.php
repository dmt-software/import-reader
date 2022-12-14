<?php

namespace DMT\Import\Reader\Exceptions;

use RuntimeException;

class DecoratorException extends RuntimeException implements ExceptionInterface
{
    public static function create(string $message, ...$args): self
    {
        return new self(vsprintf($message, $args));
    }
}
