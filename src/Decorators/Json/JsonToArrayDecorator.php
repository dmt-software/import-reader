<?php

namespace DMT\Import\Reader\Decorators\Json;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use stdClass;

class JsonToArrayDecorator implements DecoratorInterface
{
    /**
     * Transform the rows to an ArrayObject.
     *
     * @param object|stdClass $currentRow The row received from an earlier decorator.
     * @return ArrayObject
     */
    public function decorate(object $currentRow): ArrayObject
    {
        $currentRow = json_decode(json_encode($currentRow), true);

        return new ArrayObject($currentRow, ArrayObject::ARRAY_AS_PROPS);
    }
}
