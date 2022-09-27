# Import Reader

The reader is designed to go through a file to return chunks of its contents as objects without a high memory usage.

## Installation
`composer require dmt-software/import-reader`

## Usage

### Create a reader

The reader can be created manually or the [reader builder](docs/reader-builder.md) can be used to create a (default) 
reader or a pre-configured one. 

```php
use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Reader;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use pcrov\JsonReader\JsonReader;

$internalReader = new JsonReader();
$internalReader->open('/path/to/some.json');

/** @var DecoratorInterface[] $decorators */
/** @var SanitizerInterface[] $sanitizers */
$reader = new Reader(
    new JsonReaderHandler($internalReader, ...$sanitizers),
    new GenericHandlerDecorator(), 
    ...$decorators
);
```
Visit the [reader handler](docs/reader-handler.md) documentation for more information on the handlers internal readers
and [sanitizers](docs/output-control.md#sanitizers).

### Adding decorators

Once a default reader is created, it is possible to add extra decorators to apply on each object that is returned by
the read method. 

```php
use DMT\Import\Reader\Decorators\ToObjectDecorator;
use DMT\Import\Reader\Reader;

/** @var Reader $reader */
$reader->addDecorator(new ToObjectDecorator(Customer::class, ['id' => 'id', 'name' => 'fullName', ]));

foreach ($reader->read() as $customer) {
    // import customer;
}
```
More on decorators see the [documentation](docs/output-control.md#decorators).

### Adding filters

Besides controlling the output by using a decorator a part of the object stream the reader returns can be skipped or 
filtered.

```php
use DMT\Import\Reader\Reader;

/** start on item 4 */ 
$skip = 3;
/** skip all objects that has no id */
$filter = function (object $current) { 
    return isset($current->id); 
}

foreach ($reader->read($skip, $filter) as $item) {
    // import item
}
```
Visit the [filters section](docs/output-control.md#filters) for more information about filter callbacks.

## Error Handling

### UnreadableException

This is thrown when the given file can not be read. This can have several causes:

 * file is unreadable
 * can not set file pointer
 * end of file reached whilst set file pointer
 * file pointer is set to the wrong return type 

```php
use DMT\Import\Reader\Exceptions\UnreadableException;

try {
    $readerBuilder->build($file, $options = []);
} catch (UnreadableException $exception) {
    // file can not be processed
}
```

### ReaderReadException

This can happen when a single chunk can not be read from the file. It will stop execution of the reading process.

```php
use DMT\Import\Reader\Exceptions\ReaderReadException;

try {
    foreach ($reader->read() as $n => $object) {
        // import object
    }
} catch (ReaderReadException $exception) {
    // execution stopped, after $n rows
}
```

### DecoratorException

This exception happens silently. It triggers a user warning and continues the reading process. Depending on you server 
configuration this warning is ignored or send to STDOUT or STDERR.   

### Other Exceptions or Errors

Any other kind of failures are (most likely) problems that are caused by configuration faults or when this software 
is implemented incorrect.
