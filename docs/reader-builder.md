# Reader builder

## Usage 

### create a (default) reader

Use the _build_ method to obtain a default reader from [options](#configuration).

 * file: the file or protocol wrapper to read
 * options: the config options, like:
   * encoding: the file encoding (when it is not UTF-8).
   * mapping: csv column number to headers value.
   * namespace: xml namespace of the element returned.

```php
$reader = $builder->build($file, $options = []);

foreach ($reader->read() as $object) { /* import object */ }
```

This reader will return a different PHP build-in object based on the type of file to import:
 * csv - returns an ArrayObject for each row.
 * json - returns a stdClass for each object (as json_decode does).
 * xml - returns a SimpleXMLElement for each element.

> NOTE: if a callback is used to filter out items while reading, make sure it is handling the correct type.

### create an objects reader

Instead of depending on the PHP build-in objects for each iteration, a data transfer object or value object can be 
returned by the reader. This can be achieved by using the _buildToObjectReader_.

 * file: the file or protocol wrapper to read
 * options: the config options, like:
   * class: the fully qualified class name of the object. 
   * encoding: the file encoding (when it is not UTF-8).
   * mapping: the column, path or xpath to property mapping.
   * namespace: xml namespace of the element returned.

```php
class User
{
    public int $id;
    public string $username;
    public string $email;
} 

$reader = $builder->buildToObjectReader($file, $options = [
    'class' => User::class,
    'mapping' => [
        '@id' => 'id',
        'name' => 'username',
        'email/address' => 'email',
    ]
]);

foreach ($reader->read() as $object) { /* import User */ }
```

Instead of using the simple path to property mapping, [jms-serializer](http://jmsyst.com/libs/serializer) can be used 
to deserialize json or xml into an object. 

 * file: the file or protocol wrapper to read
 * options: the config options, like:
   * class: the fully qualified class name of the object.
   * encoding: the file encoding (when it is not UTF-8).

```php
$reader = $builder->buildToObjectReader($file, $options = ['class' => User::class], SerializerBuilder::create());

foreach ($reader->read() as $object) { /* import User */ }

```

### create an array reader

The _buildToArrayReader_ can be used to read through a file to return an array on each iteration.

 * file: the file or protocol wrapper to read
 * options: the config options, like:
   * encoding: the file encoding (when it is not UTF-8) 
   * mapping: csv column number to headers value
   * namespace: xml namespace

```php
$reader = $builder->buildToArrayReader($file, $options = []);

foreach ($reader->read() as $array) { /* import array */ } 
```
> NOTE: namespace is mandatory when the elements are within a certain namespace, or an empty array might be returned.

## Configuration

Depending on the type of reader requested the following options are available: 

| **option** | **usage**                                  | **value**                                          |
|------------|--------------------------------------------|----------------------------------------------------|
| handler    | the handler to use                         | \<string\> class name of the handler               |
| delimiter  | control character for csv import           | \<string\> delimiter character                     |
| enclosure  | control character for csv import           | \<string\> enclosure character                     |
| escape     | control character for csv import           | \<string\> escape character                        |
| path       | the path of the items to read              | \<string\> path to the first item to read          |
| flags      | xml or json options                        | \<int\> bitmask options                            |
| class      | the object to return                       | \<string\> the class name of the objects to return |
| mapping    | column or object property mapping          | \<array\> mapping to key or property               |
| encoding   | sanitizer to fix encoding                  | \<string\> the encoding of the import file         |
| trim       | sanitizer to trim characters from raw data | \<array\> containing the chars and direction       |
| \<custom\> | a custom sanitizer added to the builder    | \<mixed\> should match the constructor arguments   |

### adding a custom sanitizer

The config options can be extended by added by your own custom sanitizers. To add (or override) a sanitizer the 
_addSanitizer_ method must be called.

```php
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;

/** @var SanitizerInterface $decodeUrlSanitizer */
$builder->addSanitizer('url-decode', $decodeUrlSanitizer);

$reader = $builder->build($file, $options = ['url-decode' => $raw = true|false])
```

See also the [sanitizer documentation](output-control.md#sanitizers).