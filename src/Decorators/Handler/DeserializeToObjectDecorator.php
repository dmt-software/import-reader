<?php

namespace DMT\Import\Reader\Decorators\Handler;

use DMT\Import\Reader\Decorators\HandlerDecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorException;
use JMS\Serializer\Exception\Exception;
use JMS\Serializer\SerializerInterface;

/**
 * Deserialize to Object.
 *
 * This uses JMS serializer to transform a xml or json string into an object.
 * To enable this JMS serializer must be installed (`composer require jms/serializer`).
 */
final class DeserializeToObjectDecorator implements HandlerDecoratorInterface
{
    public const string TYPE_XML = 'xml';

    public const string TYPE_JSON = 'json';

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly string $fqcn,
        private ?string $type = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function decorate($currentRow): object
    {
        try {
            return $this->serializer->deserialize($currentRow, $this->fqcn, $this->getType($currentRow));
        } catch (Exception $exception) {
            throw new DecoratorException('Deserialization fails', 0, $exception);
        }
    }

    /**
     * This type is determined once based on the content of the current row.
     *
     * @param string $currentRow the current row.
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
