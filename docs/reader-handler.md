# Reader handlers

All reader handlers use an internal reader to read through a file and a series of sanitizers to apply to the raw values 
returned by these internal reader. All internal readers (should) support both a path to a file and a 
[protocol wrapper](https://www.php.net/manual/en/wrappers.php) that points to a file.   

## CsvReaderHandler

The csv reader handler internally uses _SplFileObject_ with the READ_CSV flag to read through a file line by line. It 
can be configured to use the right csv control characters: "delimiter, enclosure and escape".

The sanitizers this handler receives are applied on each of the array values that is returned by the inner reader.

```php
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;

$innerReader = new SplFileObject($csvFile);
$innerReader->setCsvControl(';');

$csvReaderHandler = new CsvReaderHandler($innerReader, new TrimSanitizer()); 
```

## JsonReaderHandler

The reader handler to read json files uses a _[JsonReader](https://github.com/pcrov/JsonReader)_ as inner reader. It 
expects a _FilePointer_ with a "dotted path" to point to the right elements to iterate from.

 * **.** (a single dot) - points to first object in an array of objects.
 * **root.elements** - points to the elements object array of the root.  

```php
use DMT\Import\Reader\Handlers\FilePointers\JsonPathFilePointer;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use pcrov\JsonReader\JsonReader;

$innerReader = new JsonReader();
$innerReader->open($jsonFile);

$jsonReaderHandler = new JsonReaderHandler($innerReader, new JsonPathFilePointer($path));
```

When the path is left empty the complete contents of the file is returned whilst reading.

> NOTE: The paths are always determined from the root of the file to import and should always point to an (array of) 
object(s)

## XmlReaderHandler

This handler to read through XML uses the build-in _XMLReader_. It requires a _FilePointer_ to determine which elements 
to return during reading. This pointer is configured with  an xpath-like structure of node names starting from the root 
of the xml.

 * **/root/element** - points to the first element in root.

```php
use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;

$innerReader = new XMLReader();
$innerReader->open($xmlFile, $fileEncoding, $libxmlOptions);

$jsonReaderHandler = new XmlReaderHandler($innerReader, new XmlPathFilePointer($path));
```

## HandlerFactory

### Creating Handlers

The handler factory can be used to construct a reader for a file type with certain options, as documented in the
reader builder [configuration](reader-builder.md#configuration).

 * createCsvReaderHandler
 * createJsonReaderHandler
 * createXmlReaderHandler

```php
$factory->createCsvReaderHandler($file, $options = ['delimiter' => ';']); 
```

### Custom handlers

By default, any custom handler will use a _SplFileObject_ as inner reader if they are created by the handler factory. If
for some reason a different inner reader is needed the handler builder can be configured with a callback to initialize 
the handler with a different inner reader.

```php
$handlerInitializeCallback = function (string $file) {
    $customReader = new CustomInnerReader($file)
    
    return new CustomReaderHandler($customReader);
};

$factory->addInitializeHandlerCallback(CustomReaderHandler::class, $handlerInitializeCallback);
```
