<?php

namespace DMT\Import\Reader\Decorators\Csv;

use DMT\Import\Reader\Decorators\DecoratorInterface;

class JoinColumnsDecorator implements DecoratorInterface
{
    private array $columns;
    private string $columnName;
    private string $separator;

    /**
     * @param string[] $columns The columns to merge.
     * @param string|null $columnName The name of the new column.
     * @param string $separator The separator to join the columns.
     */
    public function __construct(array $columns, string $columnName, string $separator = ' ')
    {
        foreach ($columns as &$column) {
            if (is_int($column)) {
                $column = 'col' . ($column + 1);
            }
        }

        $this->columns = $columns;
        $this->columnName = $columnName;
        $this->separator = $separator;
    }

    /**
     * @inheritDoc
     */
    public function decorate(object $currentRow): object
    {
        $columns = array_fill_keys($this->columns, null);
        $values = array_replace($columns, array_intersect_key($currentRow->getArrayCopy(), $columns));

        $currentRow[$this->columnName] = implode($this->separator, array_filter($values));

        return $currentRow;
    }
}
