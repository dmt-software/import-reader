<?php

namespace DMT\Import\Reader\Handlers;

use DMT\Import\Reader\Handlers\FilePointers\JsonPathFilePointer;
use DMT\Import\Reader\Handlers\FilePointers\XmlPathFilePointer;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use pcrov\JsonReader\JsonReader;
use SplFileObject;
use XMLReader;

final class HandlerFactory
{
    /**
     * Create a reader handler for a csv file.
     *
     * @param string $fileOrUri The file or wrapper uri that contains the file.
     * @param array $configuration Optional configuration with <delimiter>, <enclosure> and <escape> characters.
     * @param SanitizerInterface ...$sanitizers Optional sanitizers to apply on the raw values.
     * @return HandlerInterface
     */
    public function createCsvReaderHandler(
        string $fileOrUri,
        array $configuration = [],
        SanitizerInterface ...$sanitizers
    ): HandlerInterface {
        $configuration = [
            $configuration['delimiter'] ?? ',',
            $configuration['enclosure'] ?? '"',
            $configuration['escape'] ?? '\\'
        ];

        $fileHandler = new SplFileObject($fileOrUri);
        $fileHandler->setCsvControl(...$configuration);

        return new CsvReaderHandler($fileHandler, ...$sanitizers);
    }

    /**
     * Create a reader handler for a json file.
     *
     * @param string $fileOrUri The file or wrapper uri that contains the file.
     * @param array $config Optional configuration that contains the json <flags> and dotted <path> of the json.
     * @param SanitizerInterface ...$sanitizers $sanitizers Optional sanitizers to apply on the raw json string.
     * @return HandlerInterface
     */
    public function createJsonReaderHandler(
        string $fileOrUri,
        array $config = [],
        SanitizerInterface ...$sanitizers
    ): HandlerInterface {
        $fileHandler = new JsonReader($config['flags'] ?? 0);
        $fileHandler->open($fileOrUri);

        $pointer = new JsonPathFilePointer($config['path'] ?? '');

        return new JsonReaderHandler($fileHandler, $pointer, ...$sanitizers);
    }

    /**
     * Create a reader handler for a xml file.
     *
     * @param string $fileOrUri The file or wrapper uri that contains the file.
     * @param array $config Optional configuration that contains the <encoding>, <version> and <path> of the xml.
     * @param SanitizerInterface ...$sanitizers Optional sanitizers to apply on the string of xml.
     * @return HandlerInterface
     */
    public function createXmlReaderHandler(
        string $fileOrUri,
        array $config = [],
        SanitizerInterface ...$sanitizers
    ): HandlerInterface {
        $fileHandler = new XMLReader();
        $fileHandler->open($fileOrUri, $config['encoding'] ?? null, $config['flags'] ?? 0);

        $pointer = new XmlPathFilePointer($config['path'] ?? '');

        return new XmlReaderHandler($fileHandler, $pointer, ...$sanitizers);
    }
}
