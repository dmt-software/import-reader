<?php

namespace DMT\Import\Reader\Decorators\Xml;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use SimpleXMLElement;

final class XmlToArrayDecorator implements DecoratorInterface
{
    private ?array $mapping;

    /**
     * @param array|null $mapping
     */
    public function __construct(array $mapping = null)
    {
        $this->mapping = $mapping ?: null;
    }

    /**
     * Transform the rows into ArrayObject instances.
     *
     * @param object|SimpleXMLElement $currentRow The current row.
     * @return ArrayObject
     */
    public function decorate(object $currentRow): ArrayObject
    {
        if (!is_null($this->mapping)) {
            $result = [];
            foreach ($this->mapping as $path => $key) {
                $value = $currentRow->xpath($path);

                if (count($value) > 1) {
                    $result[$key] = $this->simpleXmlElementToArray($value);
                } else {
                    $result[$key] = $value ? strval($value[0]) : null;
                }
            }

            return new ArrayObject($result, ArrayObject::ARRAY_AS_PROPS);
        }

        $currentRow = $this->simpleXmlElementToArray($currentRow);

        return new ArrayObject($currentRow, ArrayObject::ARRAY_AS_PROPS);
    }

    private function simpleXmlElementToArray($xml)
    {
        $result = (array)$xml;

        foreach ($result as $key => $value) {
            if ($value instanceof SimpleXMLElement) {
                if (count($value->children()) == 0) {
                    $result[$key] =  strval($value);
                } else {
                    $result[$key] = $this->simpleXmlElementToArray($value);
                }
            } elseif (is_array($value)) {
                $result[$key] = $this->simpleXmlElementToArray($value);
            }
        }

        return $result;
    }
}
