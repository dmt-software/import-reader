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
 *
 * @template T
 */
final readonly class CsvToObjectDecorator implements DecoratorInterface
{
    /**
     * @param class-string<T> $className The fully qualified class name.
     * @param array $mapping The csv column to object property mapping.
     */
    public function __construct(private string $className, private array $mapping)
    {
    }

    /**
     * Apply transforming into a DTO.
     *
     * This tries to initiate and populate a DataTransferObject.
     *
     * {@inheritDoc}
     *
     * @return T
     * @throws DecoratorException|ReflectionException
     */
    public function decorate(object $currentRow): object
    {
        $entity = (new ReflectionClass($this->className))->newInstanceWithoutConstructor();

        foreach ($this->mapping as $key => $property) {
            try {
                $value = $currentRow[$key] ?? null;
                if (property_exists($entity, $property) || method_exists($entity, '__set')) {
                    $entity->$property = $value;
                }
            } catch (Error) {
                throw DecoratorException::create('Can not set %s on %s', $property, $this->className);
            }
        }

        return $entity;
    }
}
