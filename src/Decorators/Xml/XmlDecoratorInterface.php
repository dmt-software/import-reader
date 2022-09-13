<?php

namespace DMT\Import\Reader\Decorators\Xml;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use SimpleXMLElement;

interface XmlDecoratorInterface extends DecoratorInterface
{
    /**
     * Apply the decorator to the row.
     *
     * @param SimpleXMLElement $currentRow The row received from an earlier applied decorator.
     * @return object The decorated row.
     * @throws DecoratorApplyException
     */
    public function apply(SimpleXMLElement $currentRow): object;
}
