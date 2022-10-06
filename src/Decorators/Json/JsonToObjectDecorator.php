<?php

namespace DMT\Import\Reader\Decorators\Json;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorException;
use Error;
use ReflectionClass;
use ReflectionException;
use stdClass;

final class JsonToObjectDecorator implements DecoratorInterface
{
    private string $className;
    private array $mapping;

    /**
     * @param string $className The fully qualified class name.
     * @param array $mapping The property in json object (using dotted path) to property mapping.
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
     * @param stdClass|object $currentRow The current json object.
     *
     * @return object Instance of an object according to type stored in className property.
     * @throws DecoratorException When the initialization of the object failed.
     * @throws ReflectionException
     */
    public function decorate(object $currentRow): object
    {
        $object = new ReflectionClass($this->className);
        $entity = $object->newInstanceWithoutConstructor();

        foreach ($this->mapping as $key => $property) {
            try {
                if (!property_exists($entity, $property) && !method_exists($entity, '__set')) {
                    continue;
                }
                $value = $currentRow;
                $paths = explode('.', $key);
                foreach ($paths as $path) {
                    $value = $value->$path ?? null;
                }

                $type = $object->getProperty($property)->getType()->getName();
                if ($value && !is_array($value) && $type == 'array') {
                    $value = [$value];
                } elseif (is_array($value) && $type != 'array') {
                    [$value] = $value;
                }

                $entity->$property = $value;
            } catch (Error $e) {
                throw DecoratorException::create('Can not set %s on %s', $property, $this->className);
            }
        }

        return $entity;
    }
}
