# Import Reader

The reader is designed to go through a file to return chunks of its contents as objects without a high memory usage.

## Installation
`composer require dmt-software/import-reader`

## Usage

### Using the reader builder.

Read a file into a series of defined objects.
```php
use DMT\Import\Reader\ReaderBuilder;

$reader = (new ReaderBuilder())->build(
    'file://customers.xml', [
        'class' => Customer::class,
        'mapping' => [
            'name/@id' => 'number',
            'name' => 'name',
        ],
        'path' => '/customers/customer'
    ]
);

foreach ($reader->read() as $key => $customer) {
    // process customer
}
```
Visit the [reader builder](docs/reader-builder.md) documentation for configuration options.

### Manually create a reader

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
    new GenericHandlerDecorator(), ...$decorators
);
```
Visit the [reader handler](docs/reader-handler.md) documentation for more information on the handlers, internal readers
and sanitizers.

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
    // File can not be processed
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
