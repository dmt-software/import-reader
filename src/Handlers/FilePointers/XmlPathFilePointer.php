<?php

namespace DMT\Import\Reader\Handlers\FilePointers;

use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\XmlParser\Node\Text;
use DMT\XmlParser\Parser;
use Throwable;

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
     * @param Parser $reader The file reader to use.
     * @param int $skip
     * @throws UnreadableException
     */
    public function seek($reader, int $skip): void
    {
        if ($this->path == '') {
            return;
        }

        $paths = preg_split('~/~', $this->path, -1, PREG_SPLIT_NO_EMPTY);
        $depth = 0;
        $stack = [];

        try {
            while ($node = $reader->parse()) {
                if ($node instanceof Text) {
                    continue;
                }

                if ($depth >= $node->depth()) {
                    array_pop($stack);
                }

                $depth = $node->depth() - 1;
                if ($depth <= count($paths)) {
                    $stack[$depth] = $node->localName;
                }
                if ($paths == $stack) {
                    break;
                }
            }
        } catch (Throwable $exception) {
            throw UnreadableException::unreadable('xml', $exception);
        }

        if ($paths != $stack) {
            throw UnreadableException::pathNotFound($this->path);
        }

        $position = 0;
        while ($position++ < $skip) {
            if (!$reader->parseXml()) {
                throw UnreadableException::eof();
            }
            if ($node->localName !== $reader->parse()->localName) {
                throw UnreadableException::eof();
            }
        }
    }
}
