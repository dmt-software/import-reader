<?php

namespace DMT\Test\Import\Reader\Fixtures;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("program")
 */
class Program
{
    /**
     * @JMS\XmlElement(cdata=false)
     * @JMS\Type("string")
     *
     * Type of license needed.
     *
     * @var string
     */
    public string $license;

    /**
     * @JMS\XmlList(entry="language", inline=false)
     * @JMS\Type("array<DMT\Test\Import\Reader\Fixtures\Language>")
     *
     * A list of languages.
     *
     * @var array|Language[]
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
