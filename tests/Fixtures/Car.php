<?php

namespace DMT\Test\Import\Reader\Fixtures;

class Car
{
    public string $make;
    public string $model;

    /**
     * @param string $make
     * @param string $model
     */
    public function __construct(string $make, string $model)
    {
        $this->make = $make;
        $this->model = $model;
    }
}
