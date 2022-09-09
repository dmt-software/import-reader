<?php

namespace DMT\Import\Reader;

use DMT\Import\Reader\Decorators\DecoratorInterface;
use DMT\Import\Reader\Decorators\GenericToObjectDecorator;
use DMT\Import\Reader\Exceptions\DecoratorApplyException;
use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Handlers\HandlerInterface;
use function PHPUnit\Framework\classHasAttribute;

class Reader
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
     * People are encouraged to use or create a decorator that returns a data transfer object (DTO).
     *
     * @param int $offset
     * @return iterable
     * @throws ExceptionInterface
     */
    public function read(int $offset = 0): iterable
    {
        $this->handler->setPointer($offset);

        if (count($this->decorators) === 0) {
            $this->decorators = [new GenericToObjectDecorator()];
        }

        foreach ($this->handler->read() as $position => $currentRow) {
            try {
                foreach ($this->decorators as $decorator) {
                    $currentRow = $decorator->apply($currentRow);
                }

                yield $position + $offset => $currentRow;
            } catch (DecoratorApplyException $exception) {
                trigger_error('Skipped row ' . ($position + $offset), E_USER_WARNING);
            }
        }
    }
}
