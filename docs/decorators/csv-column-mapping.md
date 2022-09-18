# Csv\\ColumnMappingDecorator

This decorator is always called after the `GenericToObjectDecorator`.

## Usage

```php
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Reader;
  
$mapping = [
   'col1' => 'name',
   'col4' => 'address',
];

/** @var CsvReaderHandler $handler */ 
$reader = new Reader(
    $handler,
    new GenericHandlerDecorator(),
    new ColumnDecorator($mapping)
);
```

## Alternative Mapping

```php
$mapping2 = [
   'name',    // replaces col1 with name
   null,      // skips col2
   null,      // skips col3
   'address', // replaces col4 with address
];

$mapping3 = [
   0 => 'name',    // replaces col1 with name
   3 => 'address', // replaces col4 with address
];
```

## Error handling

When a column is mapped but not found in the row that row is skipped by the reader.
