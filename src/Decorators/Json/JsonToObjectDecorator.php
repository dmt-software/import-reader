<?php

namespace DMT\Import\Reader\Decorators\Json;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use Error;
use ReflectionClass;
use ReflectionException;
use stdClass;

class JsonToObjectDecorator implements DecoratorInterface
{
    private string $fqcn;
    private array $mapping;

    /**
     * @param string $fqcn The fully qualified class name.
     * @param array $mapping The property in json object (using dotted path) to property mapping.
     */
    public function __construct(string $fqcn, array $mapping)
    {
        $this->fqcn = $fqcn;
        $this->mapping = $mapping;
    }

    /**
     * Apply transforming into a DTO.
     *
     * This tries to initiate and populate a DataTransferObject.
     *
     * @param stdClass|object $currentRow The current csv row.
     *
     * @return object Instance of an object according to type stored in fqcn.
     * @throws DecoratorApplyException When the initialization of the object failed.
     * @throws ReflectionException
     */
    public function apply(object $currentRow): object
    {
        $entity = (new ReflectionClass($this->fqcn))->newInstanceWithoutConstructor();

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
                $entity->$property = $value;
            } catch (Error $e) {
                throw DecoratorApplyException::create('Can not set %s on %s', $property, $this->fqcn);
            }
        }

        return $entity;
    }
}
