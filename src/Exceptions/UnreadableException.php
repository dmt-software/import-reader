<?php

namespace DMT\Import\Reader\Exceptions;

use RuntimeException;
use Throwable;

class UnreadableException extends RuntimeException implements ExceptionInterface
{
    public static function unreadable(string $typeOrFile, Throwable $throwable = null): self
    {
        return self::create('Unable to read %s', $typeOrFile, $throwable);
    }

    public static function eof(): self
    {
        return self::create('End of file reached');
    }

    public static function illegalValue($value): self
    {
        return self::create('Illegal return type %s', gettype($value));
    }

    public static function pathNotFound(string $path): self
    {
        return self::create('Path %s not found', $path);
    }

    public static function create(string $message, ...$args): self
    {
        $code = 0;
        $previous = null;
        if (end($args) instanceof Throwable) {
            $previous = array_pop($args);
        }
        $args = array_merge($args);

        return new self(vsprintf($message, $args), $code, $previous);
    }
}
