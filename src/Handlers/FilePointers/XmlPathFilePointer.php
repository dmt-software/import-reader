<?php

namespace DMT\Import\Reader\Handlers\FilePointers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use Throwable;
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
 * Path /Blog/Post/Comments will set the pointer for the reader to the first Comments element.
 */
final class XmlPathFilePointer implements FilePointerInterface
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
    public function __construct(string $path = '')
    {
        $this->path = $path;
    }

    /**
     * Set the pointer to the first element in the xml according the given path.
     *
     * @param XMLReader $reader The file reader to use.
     * @param int $skip
     * @throws UnreadableException
     */
    public function seek($reader, int $skip): void
    {
        if ($this->path == '') {
            $reader->read();
            return;
        }

        $paths = preg_split('~/~', $this->path, -1, PREG_SPLIT_NO_EMPTY);
        $stack = [];

        try {
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
        } catch (Throwable $exception) {
            throw UnreadableException::unreadable('xml', $exception);
        }

        if ($paths != $stack) {
            throw UnreadableException::pathNotFound($this->path);
        }

        $position = 0;
        while ($position++ < $skip) {
            if (!$reader->readOuterXml()) {
                throw UnreadableException::eof();
            }
            $reader->next($reader->localName);
        }
    }
}
