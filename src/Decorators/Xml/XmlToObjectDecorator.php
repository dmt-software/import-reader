<?php

namespace DMT\Import\Reader\Decorators\Xml;

use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use Error;
use ReflectionClass;
use ReflectionException;
use SimpleXMLElement;

class XmlToObjectDecorator implements XmlDecoratorInterface
{
    private string $fqcn;
    private array $mapping;

    /**
     * @param string $fqcn The fully qualified class name.
     * @param array $mapping The element xpath to property mapping.
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
     * @param SimpleXMLElement $currentRow The current csv row.
     *
     * @return object Instance of an object according to type stored in fqcn.
     * @throws DecoratorApplyException When the initialization of the object failed.
     * @throws ReflectionException
     */
    public function apply(SimpleXMLElement $currentRow): object
    {
        $object = new ReflectionClass($this->fqcn);
        $entity = $object->newInstanceWithoutConstructor();

        foreach ($this->mapping as $key => $property) {
            try {
                if (property_exists($entity, $property) || method_exists($entity, '__set')) {
                    $value = $currentRow->xpath($key);

                    if ($object->getProperty($property)->getType()->getName() == 'array') {
                        $value = $this->normalizeNodeList($value);
                    } else {
                        $value = $value ? strval($value[0]) : null;
                    }

                    $entity->$property = $value;
                }
            } catch (Error $e) {
                throw DecoratorApplyException::create('Can not set %s on %s', $property, $this->fqcn);
            }
        }

        return $entity;
    }

    private function normalizeNodeList($value): array
    {
        if (!is_array($value) || count($value) == 0) {
            return [];
        }

        if (count($value[0]->children()) > 0) {
            foreach ($value as &$elem) {
                $elem = array_map('strval', $elem->xpath('*'));
            }
        } else {
            $value = array_map('strval', $value);
        }

        return $value;
    }
}
