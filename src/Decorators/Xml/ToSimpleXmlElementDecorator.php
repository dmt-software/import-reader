<?php

namespace DMT\Import\Reader\Decorators\Xml;

use DMT\Import\Reader\Decorators\ReaderDecoratorInterface;
use SimpleXMLElement;

class ToSimpleXmlElementDecorator implements ReaderDecoratorInterface
{
    private int $options = 0;
    private string $namespace = '';

    /**
     * @param int $options
     * @param string $namespace
     */
    public function __construct(int $options = 0, string $namespace = '')
    {
        $this->options = $options;
        $this->namespace = $namespace;
    }

    public function apply($currentRow): object
    {
        return new SimpleXMLElement($currentRow, $this->options, false, $this->namespace);
    }
}
