# Import Reader

The reader is designed to go through a file to return chunks of its contents as objects without a high memory usage.

## Installation
`composer require dmt-software/import-reader`

## Usage

```php
use DMT\Import\Reader\Reader;
use DMT\Import\Reader\Handlers\HandlerFactory;

/** @var HandlerFactory $factory */
$reader = new Reader(
    $factory->createJsonReaderHandler(
        'example.json', 
        ['path' => '.']
    )
); 

foreach ($reader->read() as $i => $element) {
    // process the received decoded json
}
```

### Sanitizers and Decorators

Both sanitizers and decorators are processed in order of their appearance, but their usage is different.

_Sanitizers_ will be applied to the raw value returned by the inner reader of a handler. They prepare the raw 
data before they are returned by that handler. No sanitizers are set be default. 

_Decorators_ will be applied to each value returned by the handler. There are two kind of decodators:
 - Handler decorators (transform the value from a handler into an object)
 - Object decorators (will be applied to a value received from another decorator)

By default, the `GenericHandlerDecorator` is used to transform the handler return values into an object.

### Customized objects

By default, the reader returns an object from PHP's core.    
 - xml, returns a SimpleXmlElement
 - json, returns a stdClass
 - csv, returns an ArrayObject (with ARRAY_AS_PROPS)

Although they all could be accessed as an object with public properties, it is highly encouraged to create a value 
object of data transfer object that represents the received row from the reader.
Each of the formats will have their own `*ToObjectDecorator` to achieve this.

```php
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Decorators\Xml\XmlToObjectDecorator;
use DMT\Import\Reader\Reader;
use DMT\Import\Reader\Handlers\HandlerFactory;

class UserValueObject
{
    public int $id;
    public string $username;
}

/** @var HandlerFactory $factory */
$reader = new Reader(
    $factory->createXmlReaderHandler(
        'user.xml', 
        ['path' => 'users/user']
    ),
    new GenericHandlerDecorator(),
    new XmlToObjectDecorator(UserValueObject::class, ['id' => 'id', 'email' => 'username'])
); 

foreach ($reader->read() as $i => $user) {
    // each user is now an instance of UserValueObject 
}
```

## Supported formats

This reader can process a csv, xml of json file or a file stream wrapper to such files.

Any other type of format, to process a fix length file for instance, can be created by implementing the 
`HandlerInterface`.

