# Controlling the reader output

The order of controlling the reader flow is as follows:
 1. [sanitize](#sanitizers)
 2. [decorate](#decorators)
 3. [filter](#filters)

## Sanitizers

Sanitizers are used to fix the raw value returned by a [ReaderHandler](reader-handler.md).
They can be used to fix encodings, trim white space etc. Sanitizers are independent on the type of reader handler, so
they will be applied to the raw output from the inner reader (array of string).
Custom created sanitizers should handle both string and array output from the inner readers.

```php
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;

class MyCustomSanitizer implements SanitizerInterface
{
    public function sanitize($currentRow)
    {
        if (is_array($currentRow)) {
            // apply to all array values in current row 
        } else {
            // apply to current row
        }
        
        return $currentRow;
    }
}
```
The reader handler can be constructed with multiple sanitizers, if so the sanitizers are applied in the order they are
set.

## Decorators

### Handler decorator
The handler decorator ensures the raw return value from the handler is transformed into an object. This is always the 
first decorator in the chain of decorators. If the reader is constructed without a decorator for the handler output, the
`GenericHandlerDecorator` is used.

 * GenericHandlerDecorator - default decorator will return an ArrayObject, stdClass or SimpleXMLElement.
 * DeserializeToObjectDecorator - uses [jms-serializer](https://jmsyst.com/libs/serializer) to transform json/xml 
   strings into complex objects.
 * ToSimpleXmlElementDecorator - will return a SimpleXMLElement that requires a namespace to obtain a property from it.  

### Decorator
Other decorators are handled in "first comes, first served" order. They can be used to modify the output of the reader. 

```php
use DMT\Import\Reader\Decorators\Csv\ColumnMappingDecorator;
use DMT\Import\Reader\Reader;

/** @var Reader $reader */
$reader->addDecorator(new ColumnMappingDecorator(['col1' => 'id', 'col4' => 'name']));

/** @var array $arrayObject */
foreach ($reader->read() as $arrayObject) {
    printf('user %d: %s', $arrayObject->id, $arrayObject->name); // prints something like "user 1: John Do" 
}
```

 * ColumnMappingDecorator - maps columns from ArrayObject to column names from csv output.
 * CsvToObjectDecorator - transforms an ArrayObject into a value object or data transfer object (DTO).
 * JsonToArrayDecorator - transforms the stdClass objects from json output into an ArrayObject.
 * JsonToObjectDecorator - transforms the json object output into a value object or DTO.
 * XmlToArrayDecorator - transforms a SimpleXMLElement into an ArrayObject.
 * XmlToObjectDecorator - transforms the xml SimpleXMLElement output into a value object or DTO.
 * ToArrayDecorator - delegates to a *ToArrayDecorator depending on the type of data received.
 * ToObjectDecorator - generic *ToObjectDecorator that delegates to the correct one according to the type of object. 

## Filters

Filters can be used to ignore or skip certain iterations. 

```php
use DMT\Import\Reader\Decorators\ToObjectDecorator;
use DMT\Import\Reader;

$reader->addDecorator(
    new ToObjectDecorator(
        Customer::class, [
            'name/@id' => 'number',
            'name' => 'name',
        ]
    )
);

$callbackFilter = function (Customer $customer) {
    return $customer->number !== '';
};

foreach ($reader->read(0, $callbackFilter) as $key => $customer) {
    // process customer having a customer number
}
```

> NOTE: Filters must expect the format of the last executed decorator, with one exception:
> the ToArrayReader, as it returns an iteration of arrays. 
