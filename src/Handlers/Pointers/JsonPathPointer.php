<?php

namespace DMT\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Exceptions\UnreadableException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

final class JsonPathPointer implements PointerInterface
{
    private string $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param JsonReader $reader
     * @return void
     * @throws ReaderReadException
     * @throws UnreadableException
     */
    public function setPointer($reader): void
    {
        $paths = explode('.', $this->path);

        try {
            foreach ($paths as $depth => $path) {
                while ($reader->read($path ?: null)) {
                    if ($depth + 1 === $reader->depth()) {
                        break;
                    }
                }
            }
        } catch (Exception $exception) {
            throw new UnreadableException('Unable to read');
        }

        if ($reader->type() != JsonReader::ARRAY) {
            throw new ReaderReadException('Path not found');
        }

        $reader->read();
    }
}
