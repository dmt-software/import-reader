<?php

namespace DMT\Import\Reader\Handlers\Pointers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use XMLReader;

/**
 * Helper to determine the elements to iterate over.
 *
 * This class resolves the following path format:
 *   /{root-local-name}/{child-local-name}/{child-of-child-local-name}
 *
 * @example
 *
 * <Blog>
 *   <Post>
 *     <Comments/>
 *     <Comments/>
 *   </Post>
 * </Blog>
 *
 * `$resolver = new SimplePathResolver('/Blog/Post/Comments');`
 *
 * Will set the pointer for the reader to the first Comments element.
 */
class XmlPathPointer implements PointerInterface
{
    /**
     * The path to iterate from.
     *
     * @var string
     */
    private string $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Set the pointer to the first element in the xml according the given path.
     *
     * @param XMLReader $reader The file reader to use.
     * @return void
     * @throws UnreadableException
     */
    public function setPointer($reader): void
    {
        if (!$reader instanceof XMLReader) {
            throw new UnreadableException('Unable to read');
        }

        $paths = preg_split('~/~', $this->path, -1, PREG_SPLIT_NO_EMPTY);
        $stack = [];

        do {
            if ($reader->nodeType === XMLReader::END_ELEMENT) {
                array_pop($stack);
            } elseif ($reader->nodeType === XMLReader::ELEMENT && !$reader->isEmptyElement) {
                $stack[] = $reader->localName;
            }

            if ($paths == $stack) {
                break;
            }
        } while ($reader->read() !== false);

        if ($paths != $stack) {
            throw new UnreadableException('Path not found');
        }
    }
}
