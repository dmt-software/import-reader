<?php

namespace DMT\Test\Import\Reader\Fixtures;

use JMS\Serializer\Annotation as JMS;

/**
 * @property string $name
 * @property int $since
 * @property string $author
 */
class Language
{
    public function __construct(
        /**
         * @JMS\XmlElement(cdata=false)
         * @JMS\Type("string")
         *
         * The programming language name.
         */
        protected string $name,
        /**
         * @JMS\XmlElement(cdata=false)
         * @JMS\Type("int")
         *
         * The year it was first released.
         */
        protected int $since,
        /**
         * @JMS\XmlElement(cdata=false)
         * @JMS\SerializedName("by")
         * @JMS\Type("string")
         *
         * The author of the language.
         */
        protected string $author
    )
    {
    }

    /**
     * Set a value.
     *
     * @param mixed $value
     */
    public function __set(string $property, $value): void
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    public function __get(string $property)
    {
        return property_exists($this, $property) ? $this->$property : null;
    }
}
