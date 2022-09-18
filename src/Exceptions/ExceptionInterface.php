<?php

namespace DMT\Import\Reader\Exceptions;

interface ExceptionInterface
{
    public static function create(string $message, ...$args): self;
}
