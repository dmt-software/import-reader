<?php

namespace DMT\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorException;
use Error;
use ReflectionClass;
use ReflectionException;

/**
 * Decorator to transform a row into a Data Transfer or a Value Object.
 */
final class CsvToObjectDecorator implements DecoratorInterface
{
    private string $className;
    private array $mapping;

    /**
     * @param string $className The fully qualified class name.
     * @param array $mapping The csv column to object property mapping.
     */
    public function __construct(string $className, array $mapping)
    {
        $this->className = $className;
        $this->mapping = $mapping;
    }

    /**
     * Apply transforming into a DTO.
     *
     * This tries to initiate and populate a DataTransferObject.
     *
     * @param ArrayObject|object $currentRow The current csv row.
     *
     * @return object Instance of an object according to type stored in fqcn.
     * @throws DecoratorException When the initialization of the object failed.
     * @throws ReflectionException
     */
    public function decorate(object $currentRow): object
    {
        $entity = (new ReflectionClass($this->className))->newInstanceWithoutConstructor();

        foreach ($this->mapping as $key => $property) {
            try {
                $value = isset($currentRow[$key]) ? $currentRow[$key] : null;
                if (property_exists($entity, $property) || method_exists($entity, '__set')) {
                    $entity->$property = $value;
                }
            } catch (Error $e) {
                throw DecoratorException::create('Can not set %s on %s', $property, $this->className);
            }
        }

        return $entity;
    }
}
