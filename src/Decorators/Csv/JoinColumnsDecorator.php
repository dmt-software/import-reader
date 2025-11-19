<?php

namespace DMT\Import\Reader\Decorators\Csv;

use ArrayObject;
use DMT\Import\Reader\Decorators\CsvDecoratorInterface;
use DMT\Import\Reader\Decorators\DecoratorInterface;

final readonly class JoinColumnsDecorator implements DecoratorInterface, CsvDecoratorInterface
{
    private array $columns;

    public function __construct(
        array               $columns,
        private null|string $columnName = null,
        private string      $separator = ' '
    ) {
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

        $currentRow[$this->columnName] = implode($this->separator, array_filter($values));

        return $currentRow;
    }
}
