<?php

namespace DMT\Import\Reader\Decorators\Reader;

use ArrayObject;
use DMT\Import\Reader\Decorators\ReaderDecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;

/**
 * Generic to object decorator
 *
 * This decorator is the default if no decorator is configured within the reader.
 * Depending on the type of file/stream to read it returns the following object types:
 *
 *   - xml -> an instance of SimpleXMLElement
 *   - csv -> an instance of ArrayObject with col{number} keys accessible as properties (starting with col1)
 *   - json -> a stdClass containing the decoded json string
 *
 * Custom type of readers should use their own Decorator to provide a collection of objects.
 */
final class GenericReaderDecorator implements ReaderDecoratorInterface
{
    public const TYPE_XML = 'xml';
    public const TYPE_CSV = 'csv';
    public const TYPE_JSON = 'json';
    public const UNDEFINED_TYPE = null;

    private ?string $type = self::UNDEFINED_TYPE;

    public function apply($currentRow): object
    {
        $type = $this->getType($currentRow);

        if ($type === self::TYPE_XML) {
            $currentRow = simplexml_load_string($currentRow);
        } elseif ($type === self::TYPE_JSON) {
            $currentRow = json_decode($currentRow) ?? false;
        } elseif ($type === self::TYPE_CSV) {
            $currentKeys = array_map('sprintf', array_fill_keys($currentRow, 'col%d'), range(1, count($currentRow)));
            $currentRow = new ArrayObject(array_combine($currentKeys, $currentRow), ArrayObject::ARRAY_AS_PROPS);
        }

        if (!is_object($currentRow) || $type === self::UNDEFINED_TYPE) {
            throw DecoratorApplyException::create('Type mismatch');
        }

        return $currentRow;
    }

    /**
     * This type is determined once based on the content of the current row.
     *
     * @param mixed $currentRow the current row.
     * @return string|null
     */
    private function getType($currentRow): ?string
    {
        if (is_string($this->type)) {
            return $this->type;
        }

        if (is_array($currentRow) && $currentRow !== []) {
            return $this->type = self::TYPE_CSV;
        }

        if (!is_string($currentRow)) {
            return self::UNDEFINED_TYPE;
        }

        if (preg_match('~^\<([^\>]+).*\>~ms', trim($currentRow))) {
            $this->type = self::TYPE_XML;
        } elseif (preg_match('~^(\[|\{).*(\]|\})$~ms', trim($currentRow))) {
            $this->type = self::TYPE_JSON;
        }

        return $this->type;
    }
}
