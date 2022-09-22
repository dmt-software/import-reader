<?php

namespace DMT\Import\Reader\Decorators;

use ArrayObject;
use DMT\Import\Reader\Decorators\Json\JsonToArrayDecorator;
use DMT\Import\Reader\Decorators\Xml\XmlToArrayDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use SimpleXMLElement;
use stdClass;

final class ToArrayDecorator implements DecoratorInterface
{
    private ?DecoratorInterface $typeDecorator = null;

    /**
     * Transform the rows to an ArrayObject.
     *
     * @param object $currentRow The row received from an earlier decorator.
     * @return ArrayObject
     */
    public function decorate(object $currentRow): ArrayObject
    {
        if ($currentRow instanceof ArrayObject) {
            return $currentRow;
        }

        $this->typeDecorator ??= $this->getDecoratorForType($currentRow);

        return $this->typeDecorator->decorate($currentRow);
    }

    /**
     * Get the to object decorator suited for the current row.
     *
     * @param object $currentRow The current row retrieved from the file.
     * @return XmlToArrayDecorator|JsonToArrayDecorator The eligible *ToObjectDecorator.
     * @throws DecoratorException When the current row can not be decorated.
     */
    private function getDecoratorForType(object $currentRow): DecoratorInterface
    {
        if ($currentRow instanceof SimpleXMLElement) {
            return new XmlToArrayDecorator();
        }

        if ($currentRow instanceof stdClass) {
            return new JsonToArrayDecorator();
        }

        throw DecoratorException::create('Unsupported type');
    }
}
