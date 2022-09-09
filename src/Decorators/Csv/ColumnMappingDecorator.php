<?php

namespace DMT\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Decorators\GenericToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use InvalidArgumentException;

/**
 * Column mapper.
 *
 * This maps the numbered columns read from a csv to descriptive columns.
 * It replaces the current column names with the ones mapped, while removing any unmapped columns.
 *
 * The column mapping can be an associative array where the keys are named with col{n} and their new column name or
 * a normal array where the columns are replaced based on their index.
 *
 * @see GenericToObjectDecorator
 */
class ColumnMappingDecorator implements DecoratorInterface
{
    private array $mapping = [];

    /**
     * @param array $mapping
     */
    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Apply the column mapping.
     *
     * @param ArrayObject $currentRow
     *
     * @return ArrayObject|object
     * @throws DecoratorApplyException When a column from mapping is not found in the row.
     */
    public function apply($currentRow): object
    {
        if (!$currentRow instanceof ArrayObject) {
            $type = is_object($currentRow) ? get_class($currentRow) : gettype($currentRow);
            throw new InvalidArgumentException(
                sprintf('Current row should be an ArrayObject, %s provided', $type)
            );
        }

        $replace = [];
        foreach ($this->mapping as $key => $column) {
            if (!$column) {
                continue;
            }

            $col = $key;
            if (is_int($key)) {
                $col = 'col' . ($key + 1);
            }

            if (!isset($currentRow->{$col})) {
                throw DecoratorApplyException::create('Mapped column %s not found', $key);
            }

            $replace[$column] = $currentRow->{$col};
        }
        $currentRow->exchangeArray($replace);

        return $currentRow;
    }
}
