<?php

namespace DMT\Import\Reader\Exceptions;

use Throwable;

interface ExceptionInterface extends Throwable
{
    public static function create(string $message, ...$args): self;
}
