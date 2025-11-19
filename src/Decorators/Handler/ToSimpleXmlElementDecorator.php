<?php

namespace DMT\Import\Reader\Decorators\Handler;

use DMT\Import\Reader\Decorators\HandlerDecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorException;
use SimpleXMLElement;
use Throwable;

final readonly class ToSimpleXmlElementDecorator implements HandlerDecoratorInterface
{
    /**
     * @param string|null $namespace
     * @param int $options
     */
    public function __construct(private ?string $namespace = null, private int $options = 0)
    {
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
            return new SimpleXMLElement($currentRow, $this->options, false, $this->namespace ?? '');
        } catch (Throwable $exception) {
            throw new DecoratorException('Invalid xml', 0, $exception);
        }
    }
}
