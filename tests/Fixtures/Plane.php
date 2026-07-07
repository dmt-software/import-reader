<?php

declare(strict_types=1);

namespace DMT\Test\Import\Reader\Fixtures;

class Plane
{
    /**
     * The make and model
     */
    public string $type;

    /**
     * The top speed of the plane.
     */
    public string $speed;

    /**
     * The amount of passengers it can carry.
     */
    public string $seats;

    /**
     * Year manufacturing started.
     */
    public int $year;
}
