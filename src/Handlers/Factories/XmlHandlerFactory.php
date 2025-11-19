<?php

namespace DMT\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\Pointers\XmlPathPointer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\FileParser;
use DMT\XmlParser\Source\Parser as SourceParser;
use DMT\XmlParser\Source\StreamParser;
use DMT\XmlParser\Source\StringParser;
use DMT\XmlParser\Tokenizer;

class XmlHandlerFactory implements HandlerFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createFromStream($stream, array $config, array $sanitizers): XmlReaderHandler
    {
        return $this->create(new StreamParser($stream), $config, $sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromString(string $source, array $config, array $sanitizers): XmlReaderHandler
    {
        return $this->create(new StringParser($source), $config, $sanitizers);
    }

    /**
     * @inheritDoc
     */
    public function createFromFile(string $fileOrUri, array $config, array $sanitizers): XmlReaderHandler
    {
        return $this->create(new FileParser($fileOrUri), $config, $sanitizers);
    }

    private function create(SourceParser $parser, array $config, array $sanitizers): XmlReaderHandler
    {
        $encoding = $config['encoding'] ?? 'UTF-8';
        settype($encoding, 'array');

        if ($parser instanceof StringParser) {
            $tokenizer = new Tokenizer\XmlParserTokenizer($parser, current($encoding), $config['flags'] ?? 0);
        } else {
            $tokenizer = new Tokenizer\XmlReaderTokenizer($parser, current($encoding), $config['flags'] ?? 0);
        }

        $pointer = new XmlPathPointer($config['path'] ?? '');
        $fileHandler = new Parser($tokenizer);

        return new XmlReaderHandler($fileHandler, $pointer, ...$sanitizers);
    }
}
