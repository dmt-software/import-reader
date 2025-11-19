<?php

namespace DMT\Import\Reader\Decorators;

use SimpleXMLElement;

interface XmlDecoratorInterface
{
    /**
     * @param SimpleXMLElement $currentRow The row received from an earlier applied decorator.
     * @return SimpleXMLElement The decorated row.
     */
    public function decorate(SimpleXMLElement $currentRow): SimpleXMLElement;
}
