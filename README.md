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
@see the [reader builder](docs/reader-builder.md) for configuration options.
