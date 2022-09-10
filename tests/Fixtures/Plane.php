<?php

namespace DMT\Test\Import\Reader\Fixtures;

class Plane
{
    /**
     * The make and model
     *
     * @var string
     */
    public string $type;

    /**
     * The top speed of the plane.
     *
     * @var string
     */
    public string $speed;

    /**
     * The amount of passengers it can carry.
     *
     * @var string
     */
    public string $seats;

    /**
     * Year manufacturing started.
     *
     * @var int
     */
    public int $year;
}
