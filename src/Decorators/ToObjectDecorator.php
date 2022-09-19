<?php

namespace DMT\Import\Reader\Decorators;

use ArrayObject;
use DMT\Import\Reader\Decorators\Csv\CsvToObjectDecorator;
use DMT\Import\Reader\Decorators\Json\JsonToObjectDecorator;
use DMT\Import\Reader\Decorators\Xml\XmlToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use SimpleXMLElement;
use stdClass;

/**
 * To object decorator.
 *
 * This decorator will detect which decorator to use to transform the received row into an object.
 */
final class ToObjectDecorator implements DecoratorInterface
{
    private string $className;
    private array $mapping;
    private ?DecoratorInterface $typeDecorator = null;

    /**
     * @param string $className The fully qualified class name to initiate.
     * @param array $mapping The row keys to object property mapping.
     */
    public function __construct(string $className, array $mapping)
    {
        $this->className = $className;
        $this->mapping = $mapping;
    }

    /**
     * Transform the row into a value object or DTO.
     *
     * @param object $currentRow The current row to decorate
     * @return object An instance of class <$this->fqcn>
     */
    public function decorate(object $currentRow): object
    {
        $this->typeDecorator ??= $this->getDecoratorForType($currentRow);

        return $this->typeDecorator->decorate($currentRow);
    }

    /**
     * Get the to object decorator suited for the current row.
     *
     * @param object $currentRow The current row retrieved from the file.
     * @return DecoratorInterface The eligible *ToObjectDecorator.
     * @throws DecoratorException When the current row can not be decorated.
     */
    private function getDecoratorForType(object $currentRow): DecoratorInterface
    {
        if ($currentRow instanceof ArrayObject) {
            return new CsvToObjectDecorator($this->className, $this->mapping);
        }

        if ($currentRow instanceof SimpleXMLElement) {
            return new XmlToObjectDecorator($this->className, $this->mapping);
        }

        if ($currentRow instanceof stdClass) {
            return new JsonToObjectDecorator($this->className, $this->mapping);
        }

        throw DecoratorException::create('Unsupported type');
    }
}