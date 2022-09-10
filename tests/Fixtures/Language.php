<?php

namespace DMT\Test\Import\Reader\Fixtures;

class Language
{
    /**
     * The programming language name.
     *
     * @var string
     */
    protected string $name;

    /**
     * The year it was first released.
     *
     * @var int
     */
    protected int $since;

    /**
     * The author of the language.
     *
     * @var string
     */
    protected string $author;

    /**
     * @param string $name
     * @param int $since
     * @param string $by
     */
    public function __construct(string $name, int $since, string $by)
    {
        $this->name = $name;
        $this->since = $since;
        $this->author = $by;
    }

    /**
     * Set a value.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set(string $property, $value): void
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}
