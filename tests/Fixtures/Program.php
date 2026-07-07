<?php

declare(strict_types=1);

namespace DMT\Test\Import\Reader\Fixtures;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\XmlRoot("program")
 */
class Program
{
    public function __construct(
        /**
         * @JMS\XmlElement(cdata=false)
         * @JMS\Type("string")
         *
         * Type of license needed.
         */
        public string $license,
        /**
         * @JMS\XmlList(entry="language", inline=false)
         * @JMS\Type("array<DMT\Test\Import\Reader\Fixtures\Language>")
         *
         * A list of languages.
         */
        public array $languages
    )
    {
    }
}
