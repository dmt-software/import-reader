<?php

declare(strict_types=1);

namespace DMT\Test\Import\Reader\Fixtures;

class Car
{
    public function __construct(public string $make, public string $model)
    {
    }
}
