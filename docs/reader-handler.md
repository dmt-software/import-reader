# Reader handlers

All reader handlers use an internal reader to read through a file and a series of sanitizers to apply to the raw values 
returned by these internal reader. All internal readers (should) support both a path to a file and a 
[protocol wrapper](https://www.php.net/manual/en/wrappers.php) to a file.   

## CsvReaderHandler

The csv reader handler internally uses fgetcsv to read through a file line by line. It can be configured to use the 
right csv control characters: "delimiter, enclosure and escape".

The sanitizers this handler receives are applied on each of the array values that is returned by the inner reader.

```php
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;

$csvReaderHandler = new CsvReaderHandler($innerReader, ['delimiter' => ';'], new TrimSanitizer()); 
```

## JsonReaderHandler

The reader handler to read json files uses a _[JsonReader](https://github.com/pcrov/JsonReader)_ as inner reader. It 
expects a _Pointer_ with a "dotted path" to point to the right elements to iterate from.

 * **.** (a single dot) - points to first object in an array of objects.
 * **root.elements** - points to the elements object array of the root.

```php
use DMT\Import\Reader\Handlers\Pointers\JsonPathPointer;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use pcrov\JsonReader\JsonReader;

$innerReader = new JsonReader();
$innerReader->open($jsonFile);

$jsonReaderHandler = new JsonReaderHandler($innerReader, new JsonPathPointer($path));
```

When the path is left empty the complete contents of the file is returned whilst reading.

> NOTE: The paths are always determined from the root of the file to import and should always point to an (array of) 
object(s)

## XmlReaderHandler

This handler to read through XML uses the build-in _XMLReader_. It requires a _Pointer_ to determine which elements 
to return during reading. This pointer is configured with  an xpath-like structure of node names starting from the root 
of the xml.

 * **/root/element** - points to the first element in root.

```php
use DMT\Import\Reader\Handlers\Pointers\XmlPathPointer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\FileParser;
use DMT\XmlParser\Tokenizer;

$innerReader = new Parser(new Tokenizer(new FileParser($xmlFile), $fileEncoding, $tokenizerOptions));

$jsonReaderHandler = new XmlReaderHandler($innerReader, new XmlPathPointer($path));
```

## HandlerFactory

### Creating Handlers

The handler factory can be used to construct a reader for a file type with certain options, as documented in the
reader builder [configuration](reader-builder.md#configuration).

```php
use DMT\Import\Reader\Handlers\CsvReaderHandler;

$factory->createReaderHandler(CsvReaderHandler::class, $file, $options = ['delimiter' => ';']); 
```

### Custom handlers

To enable creating a custom handler via the handler factory an instantiator it must be registered.   

```php
$handlerInitializeCallback = function (string $file) {
    $customReader = new CustomInnerReader($file)
    
    return new CustomReaderHandler($customReader);
};

$factory->addInitializeHandlerCallback(CustomReaderHandler::class, $handlerInitializeCallback);
```
