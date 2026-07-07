<?php

declare(strict_types=1);

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
final readonly class JsonPathPointer implements PointerInterface
{
    public function __construct(private string $path = '')
    {
    }

    /**
     * @param JsonReader $reader
     * @throws Exception
     */
    public function seek($reader, int $skip): void
    {
        $paths = explode('.', $this->path);
        $depth = $this->path !== '' && $this->path !== '0' ? count($paths) : 0;

        try {
            foreach ($paths as $path) {
                while ($reader->read()) {
                    if ($reader->name() == $path) {
                        if ($depth == $reader->depth() || $depth === 0) {
                            break 2;
                        }

                        break;
                    }
                }
            }
        } catch (Exception $exception) {
            throw UnreadableException::unreadable('json', $exception);
        }

        if ($reader->type() == JsonReader::ARRAY && $this->path && !str_ends_with($this->path, '.')) {
            $reader->read();
        }

        if ($reader->type() != JsonReader::OBJECT) {
            if ($path !== '' && $path !== '0' && $reader->name() == $path) {
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
