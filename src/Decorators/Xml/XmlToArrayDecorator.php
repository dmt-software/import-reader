<?php

namespace DMT\Import\Reader\Decorators\Xml;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use SimpleXMLElement;

class XmlToArrayDecorator implements DecoratorInterface
{
    /**
     * Transform the rows into ArrayObject instances.
     *
     * @param object|SimpleXMLElement $currentRow The current row.
     * @return object|ArrayObject
     */
    public function decorate(object $currentRow): ArrayObject
    {
        $currentRow = $this->simpleXmlElementToArray($currentRow);

        return new ArrayObject($currentRow, ArrayObject::ARRAY_AS_PROPS);
    }

    private function simpleXmlElementToArray($xml): array
    {
        $result = (array)$xml;

        foreach ($result as $key => $value) {
            if ($value instanceof SimpleXMLElement) {
                $result[$key] = $this->simpleXmlElementToArray($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->simpleXmlElementToArray($value);
            }
        }

        return $result;
    }
}
