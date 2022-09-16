<?php

namespace DMT\Import\Reader\Decorators\Reader;

use DMT\Import\Reader\Decorators\ReaderDecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use SimpleXMLElement;
use Throwable;

final class ToSimpleXmlElementDecorator implements ReaderDecoratorInterface
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
     * @throws DecoratorApplyException When a invalid xml is given.
     */
    public function apply($currentRow): object
    {
        try {
            return new SimpleXMLElement($currentRow, $this->options, false, $this->namespace);
        } catch (Throwable $exception) {
            throw new DecoratorApplyException('Invalid xml', 0, $exception);
        }
    }
}
