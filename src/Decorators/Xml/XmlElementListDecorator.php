<?php

namespace DMT\Import\Reader\Decorators\Xml;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use Error;
use Generator;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class XmlElementListDecorator implements DecoratorInterface
{
    public function __construct(private readonly string $path)
    {
    }

    /**
     * @param object|SimpleXMLElement $currentRow
     *
     * @return Generator
     */
    public function decorate(object $currentRow): Generator
    {
        try {
            $currentRows = $currentRow->xpath($this->path);

            if ($currentRows === false) {
                throw new RuntimeException();
            }
        } catch (Throwable) {
            throw new RuntimeException('Invalid xpath expression');
        }

        foreach ($currentRows as $currentRow) {
            yield $currentRow;
        }
    }
}
