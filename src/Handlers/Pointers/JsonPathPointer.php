<?php

namespace DMT\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use pcrov\JsonReader\Exception;
use pcrov\JsonReader\JsonReader;

/**
 * Helper to determine the elements to iterate over.
 *
 * This class resolves a dotted separated path within the json file.
 * The given path should point to an object or a list of objects to iterate over.
 */
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
     * @param int $skip
     * @throws Exception
     */
    public function seek($reader, int $skip): void
    {
        $paths = explode('.', $this->path);
        $depth = $this->path ? count($paths) : 0;

        try {
            foreach ($paths as $path) {
                while ($reader->read()) {
                    if ($reader->name() == $path) {
                        if ($depth == $reader->depth() || $depth == 0) {
                            break 2;
                        }
                        break;
                    }
                }
            }
        } catch (Exception $exception) {
            throw UnreadableException::unreadable('json', $exception);
        }

        if ($reader->type() == JsonReader::ARRAY && $this->path && substr($this->path, -1) != '.') {
            $reader->read();
        }

        if ($reader->type() != JsonReader::OBJECT) {
            if (!empty($path) && $reader->name() == $path) {
                throw UnreadableException::illegalValue($reader->value());
            }
            throw UnreadableException::pathNotFound($this->path);
        }

        $depth = max($reader->depth() -1, 0);
        $position = 0;
        while (++$position <= $skip) {
            try {
                $reader->next();

                if ($reader->depth() < $depth || !$reader->value()) {
                    throw UnreadableException::eof();
                }
            } catch (Exception $exception) {
                throw UnreadableException::unreadable('json', $exception);
            }
        }
    }
}
