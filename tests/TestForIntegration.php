<?php

namespace DMT\Test\Import\Reader;

use DMT\Import\Reader\Exceptions\ExceptionInterface;
use DMT\Import\Reader\Handlers\HandlerFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

/**
 * @property TestLogger $logger
 */
trait TestForIntegration
{
    protected HandlerFactory $handlerFactory;
    private LoggerInterface $logger;
    private $originalErrorHandler = null;

    /**
     * @group integration
     */
    public function setUp(): void
    {
        $this->handlerFactory = new HandlerFactory();
        $this->logger = new TestLogger();
        $this->originalErrorHandler = set_error_handler(
            function ($code, $message, $file, $line, $context) {
                /** @var ExceptionInterface $exception */
                $exception = $context['exception'];

                $this->logger->warning(sprintf('%s: %s', $message, $exception->getMessage()));

                return true;
            },
            E_USER_WARNING
        );
    }

    public function tearDown(): void
    {
        set_error_handler($this->originalErrorHandler);
    }
}
