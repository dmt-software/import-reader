<?php

namespace DMT\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\CsvDecoratorInterface;
use DMT\Import\Reader\Decorators\DecoratorInterface;

/**
 * Class MergeColumnsDecorator
 *
 * This merges several column values into one (extra) column as an array.
 *
 * This can be called before or after the columns are mapped with the ColumnMappingDecorator.
 * When the column name is omitted the merge result is appended to the current row.
 */
final readonly class MergeColumnsDecorator implements DecoratorInterface, CsvDecoratorInterface
{
    private array $columns;

    public function __construct(array $columns, private null|string $columnName = null)
    {
        foreach ($columns as &$column) {
            if (is_int($column)) {
                $column = 'col' . ($column + 1);
            }
        }

        $this->columns = $columns;
    }

    /**
     * @inheritDoc
     */
    public function decorate(object $currentRow): ArrayObject
    {
        $columns = array_fill_keys($this->columns, null);
        $values = array_replace($columns, array_intersect_key($currentRow->getArrayCopy(), $columns));

        $currentRow[$this->columnName] = array_filter(array_values($values));

        return $currentRow;
    }
}
