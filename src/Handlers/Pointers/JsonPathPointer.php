<?php

namespace DMT\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\ReaderReadException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

final class JsonPathPointer implements PointerInterface
{
    private string $path;

    /**
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        $this->path = $path;
    }

    /**
     * @param JsonReader $reader
     * @return void
     * @throws ReaderReadException
     */
    public function setPointer($reader): void
    {
        $paths = explode('.', $this->path);
        $depth = $this->path ? count($paths) : 0;

        try {
            foreach ($paths as $path) {
                while ($reader->read($path ?: null)) {
                    if ($depth == $reader->depth()) {
                        break;
                    }
                }
            }
        } catch (Exception $exception) {
            throw new ReaderReadException('Unable to read');
        }

        if ($reader->type() != JsonReader::ARRAY && $reader->type() != JsonReader::OBJECT) {
            throw new ReaderReadException('Path not found');
        }

        $reader->read();
    }
}
