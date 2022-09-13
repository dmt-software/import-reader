<?php

namespace DMT\Test\Import\Reader\Fixtures;

class Program
{
    /**
     * Type of license needed.
     *
     * @var string
     */
    public string $license;

    /**
     * A list of languages.
     *
     * @var array
     */
    public array $languages;

    /**
     * @param string $license
     * @param array $languages
     */
    public function __construct(string $license, array $languages)
    {
        $this->license = $license;
        $this->languages = $languages;
    }
}
