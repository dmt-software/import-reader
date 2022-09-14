<?php

namespace DMT\Import\Reader\Decorators;

use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use JMS\Serializer\Exception\Exception;
use JMS\Serializer\SerializerInterface;

/**
 * Deserialize to Object.
 *
 * This uses JMS serializer to transform a xml or json string into an object.
 * To enable this JMS serializer must be installed (`composer require jms/serializer`).
 */
class DeserializeToObjectDecorator implements ToObjectDecoratorInterface
{
    public const TYPE_XML = 'xml';
    public const TYPE_JSON = 'json';

    private SerializerInterface $serializer;
    private string $fqcn;
    private ?string $type;

    /**
     * @param SerializerInterface $serializer
     * @param string $fqcn
     * @param string|null $format
     */
    public function __construct(SerializerInterface $serializer, string $fqcn, string $format = null)
    {
        $this->serializer = $serializer;
        $this->fqcn = $fqcn;
        $this->type = $format;
    }

    /**
     * @inheritDoc
     */
    public function apply($currentRow): object
    {
        try {
            return $this->serializer->deserialize($currentRow, $this->fqcn, $this->getType($currentRow));
        } catch (Exception $exception) {
            throw new DecoratorApplyException('Deserialization fails', 0, $exception);
        }
    }

    /**
     * This type is determined once based on the content of the current row.
     *
     * @param string $currentRow the current row.
     * @return string|null
     */
    private function getType(string $currentRow): ?string
    {
        if (is_string($this->type)) {
            return $this->type;
        }

        if (preg_match('~^\<([^\>]+).*\>~ms', trim($currentRow))) {
            $this->type = self::TYPE_XML;
        } elseif (preg_match('~^(\[|\{).*(\]|\})$~ms', trim($currentRow))) {
            $this->type = self::TYPE_JSON;
        }

        return $this->type;
    }
}
