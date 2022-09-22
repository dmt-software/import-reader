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
