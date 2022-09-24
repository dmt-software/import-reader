<?php

namespace DMT\Import\Reader;

use Closure;
use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Decorators\HandlerDecoratorInterface;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Handlers\HandlerInterface;
use InvalidArgumentException;
use Iterator;

/**
 * Reader.
 *
 * This reads a file into lines or chunks and returns them.
 *
 * By default, the reader returns an object from PHP's core.
 *    - xml, returns a SimpleXmlElement
 *    - json, returns a stdClass
 *    - csv, returns an ArrayObject (with ARRAY_AS_PROPS)
 *
 * Depending on the configured decorators, the reader might return a different kind of object.
 */
final class Reader implements ReaderInterface
{
    private HandlerInterface $handler;
    /** @var DecoratorInterface[] */
    private array $decorators = [];

    /**
     * Reader.
     *
     * @param HandlerInterface $handler
     * @param DecoratorInterface ...$decorators
     */
    public function __construct(HandlerInterface $handler, DecoratorInterface ...$decorators)
    {
        if (!$decorators || !$decorators[0] instanceof HandlerDecoratorInterface) {
            array_unshift($decorators, new GenericHandlerDecorator());
        }

        $this->handler = $handler;
        $this->decorators = $decorators;
    }

    /**
     * Read through a file.
     *
     * By default, php objects will be returned like a stdClass, ArrayObject or SimpleXmlElement.
     * People are encouraged to use or create a decorator that returns a data transfer object (DTO) or value object.
     *
     * @param int $skip The number of lines or items to skip.
     * @param Closure|null $filter A callback filter to apply.
     * @return Iterator A list of items retrieved from a file.
     * @throws ReaderReadException When the reader can not continue to read from file.
     * @throws InvalidArgumentException When the reader is misconfigured.
     */
    public function read(int $skip = 0, Closure $filter = null): Iterator
    {
        $this->handler->setPointer($skip);

        $filter ??= fn($currentRow, $key) => true;
        $position = -1;
        try {
            foreach ($this->handler->read() as $position => $currentRow) {
                try {
                    foreach ($this->decorators as $decorator) {
                        $currentRow = $decorator->decorate($currentRow);
                    }

                    $key = $position + $skip;
                    if ($filter($currentRow, $key)) {
                        yield $key => $currentRow;
                    }
                } catch (DecoratorException $exception) {
                    trigger_error('Skipped row ' . ($position + $skip), E_USER_WARNING);
                }
            }
        } catch (ExceptionInterface $exception) {
            throw ReaderReadException::readError(++$position + $skip);
        }
    }
}
