<?php

namespace DMT\Import\Reader\Decorators\Handler;

use DMT\Import\Reader\Decorators\HandlerDecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorException;
use SimpleXMLElement;
use Throwable;

final class ToSimpleXmlElementDecorator implements HandlerDecoratorInterface
{
    private int $options = 0;
    private ?string $namespace;

    /**
     * @param string|null $namespace
     * @param int $options
     */
    public function __construct(string $namespace = null, int $options = 0)
    {
        $this->options = $options;
        $this->namespace = $namespace;
    }

    /**
     * Apply to the raw row.
     *
     * @param string $currentRow The current part of the xml read.
     * @return object|SimpleXMLElement
     * @throws DecoratorException When a invalid xml is given.
     */
    public function decorate($currentRow): object
    {
        try {
            return new SimpleXMLElement($currentRow, $this->options, false, $this->namespace);
        } catch (Throwable $exception) {
            throw new DecoratorException('Invalid xml', 0, $exception);
        }
    }
}
