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
    /**
     * @JMS\XmlElement(cdata=false)
     * @JMS\Type("string")
     *
     * The programming language name.
     *
     * @var string
     */
    protected string $name;

    /**
     * @JMS\XmlElement(cdata=false)
     * @JMS\Type("int")
     *
     * The year it was first released.
     *
     * @var int
     */
    protected int $since;

    /**
     * @JMS\XmlElement(cdata=false)
     * @JMS\SerializedName("by")
     * @JMS\Type("string")
     *
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

    public function __get(string $property)
    {
        return property_exists($this, $property) ? $this->$property : null;
    }
}
