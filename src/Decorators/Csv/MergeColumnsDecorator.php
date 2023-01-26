<?php

namespace DMT\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\DecoratorInterface;

/**
 * Class MergeColumnsDecorator
 *
 * This merges several column values into one (extra) column as an array, even when the columns are none existent.
 *
 * This can be called before or after the columns are mapped with the ColumnMappingDecorator.
 * When the column name is omitted the merge result is appended to the current row.
 */
class MergeColumnsDecorator implements DecoratorInterface
{
    private array $columns;
    private ?string $columnName = null;

    /**
     * @param string[] $columns The columns to merge.
     * @param string|null $columnName The name of the new column.
     */
    public function __construct(array $columns, string $columnName = null)
    {
        $this->columns = $columns;
        $this->columnName = $columnName;
    }

    /**
     * Apply the column mapping.
     *
     * @param ArrayObject|object $currentRow the row from a csv.
     *
     * @return ArrayObject|object The decorated row.
     */
    public function decorate(object $currentRow): object
    {
        $columns = array_fill_keys($this->columns, null);
        $values = array_replace($columns, array_intersect_key($currentRow->getArrayCopy(), $columns));

        $currentRow[$this->columnName] = array_values($values);

        return $currentRow;
    }
}
