<?php

namespace DMT\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Decorators\GenericToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;

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
     * @param ArrayObject|object $currentRow the row from a csv.
     *
     * @return ArrayObject|object The decorated row.
     * @throws DecoratorApplyException When a column from mapping is not found in the row.
     */
    public function apply(object $currentRow): object
    {
        $replace = [];
        foreach ($this->mapping as $key => $column) {
            if (!$column) {
                continue;
            }

            $col = is_int($key) ? 'col' . ($key + 1) : $key;
            if (!isset($currentRow[$col])) {
                throw DecoratorApplyException::create('Mapped column %s not found', $key);
            }

            $replace[$column] = $currentRow[$col];
        }
        $currentRow->exchangeArray($replace);

        return $currentRow;
    }
}
