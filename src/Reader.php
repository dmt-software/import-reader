<?php

namespace DMT\Import\Reader;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Decorators\Handler\GenericHandlerDecorator;
use DMT\Import\Reader\Exceptions\DecoratorException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Handlers\HandlerInterface;
use Iterator;

final class Reader
{
    private HandlerInterface $handler;
    /** @var DecoratorInterface[] */
    private array $decorators = [];

    /**
     * @param HandlerInterface $handler
     * @param DecoratorInterface ...$decorators
     */
    public function __construct(HandlerInterface $handler, DecoratorInterface ...$decorators)
    {
        $this->handler = $handler;
        $this->decorators = $decorators;
    }

    /**
     * Read through a file.
     *
     * By default, php objects will be returned like a stdClass, ArrayObject or SimpleXmlElement.
     * People are encouraged to use or create a decorator that returns a data transfer object (DTO) or value object.
     *
     * @param int $skip
     * @return Iterator
     * @throws ReaderReadException
     */
    public function read(int $skip = 0): Iterator
    {
        $this->handler->setPointer($skip);

        if (count($this->decorators) === 0) {
            $this->decorators = [new GenericHandlerDecorator()];
        }

        $position = -1;
        try {
            foreach ($this->handler->read() as $position => $currentRow) {
                try {
                    foreach ($this->decorators as $decorator) {
                        $currentRow = $decorator->decorate($currentRow);
                    }

                    yield $position + $skip => $currentRow;
                } catch (DecoratorException $exception) {
                    trigger_error('Skipped row ' . ($position + $skip), E_USER_WARNING);
                }
            }
        } catch (ExceptionInterface $exception) {
            throw ReaderReadException::readError(++$position + $skip);
        }
    }
}
