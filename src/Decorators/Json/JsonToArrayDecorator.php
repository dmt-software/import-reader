<?php

namespace DMT\Import\Reader\Decorators\Json;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use stdClass;

final class JsonToArrayDecorator implements DecoratorInterface
{
    private ?array $mapping;

    /**
     * @param array|null $mapping
     */
    public function __construct(array $mapping = null)
    {
        $this->mapping = $mapping ?: null;
    }

    /**
     * Transform the rows to an ArrayObject.
     *
     * @param object|stdClass $currentRow The row received from an earlier decorator.
     * @return ArrayObject
     */
    public function decorate(object $currentRow): ArrayObject
    {
        $currentRow = json_decode(json_encode($currentRow), true);

        if (!is_null($this->mapping)) {
            $result = [];
            foreach ($this->mapping as $paths => $key) {
                $value = $currentRow;
                $paths = explode('.', $paths);
                foreach ($paths as $path) {
                    $value = $value[$path] ?? null;
                }
                $result[$key] = $value;
            }
            $currentRow = $result;
        }

        return new ArrayObject($currentRow, ArrayObject::ARRAY_AS_PROPS);
    }
}
