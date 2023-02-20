<?php

namespace DMT\Test\Import\Reader\Handlers\Factories;

use DMT\Import\Reader\Handlers\Factories\CallbackHandlerFactory;
use DMT\Import\Reader\Handlers\HandlerFactory;
use DMT\Import\Reader\Handlers\HandlerInterface;
use PHPUnit\Framework\TestCase;

class CallbackHandlerFactoryTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $factory = new CallbackHandlerFactory(
            function (string $source, array $config, array $sanitizers): HandlerInterface {
                $handler = $this->getMockBuilder(HandlerInterface::class)
                    ->onlyMethods(['read'])
                    ->getMockForAbstractClass();
                $handler
                    ->expects($this->once())
                    ->method('read')
                    ->willReturnCallback(fn() => yield from explode(PHP_EOL, $source, 2));

                return $handler;
            }
        );
        $handler = $factory->createFromString(file_get_contents(__DIR__ . '/../../files/plain.txt'), [], []);

        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertSame('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $handler->read()->current());
    }

    public function testCreateFromStream(): void
    {
        $factory = new CallbackHandlerFactory(
            function ($resource, array $config, array $sanitizers): HandlerInterface {
                $handler = $this->getMockBuilder(HandlerInterface::class)
                    ->onlyMethods(['read'])
                    ->getMockForAbstractClass();
                $handler
                    ->expects($this->once())
                    ->method('read')
                    ->willReturnCallback(fn() => yield trim(fgets($resource)));

                return $handler;
            }
        );

        $stream = fopen(__DIR__ . '/../../files/plain.txt', 'r');
        $handler = $factory->createFromStream($stream, [], []);

        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertSame('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $handler->read()->current());

        fclose($stream);
    }

    public function testCreateFromFile(): void
    {
        $factory = new CallbackHandlerFactory(
            function (string $file, array $config, array $sanitizers): HandlerInterface {
                $handler = $this->getMockBuilder(HandlerInterface::class)
                    ->onlyMethods(['read'])
                    ->getMockForAbstractClass();
                $handler
                    ->expects($this->once())
                    ->method('read')
                    ->willReturnCallback(fn() => yield from array_map('trim', file($file)));

                return $handler;
            }
        );

        $handler = $factory->createFromFile(__DIR__ . '/../../files/plain.txt', [], []);

        $this->assertInstanceOf(HandlerInterface::class, $handler);
        $this->assertSame('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $handler->read()->current());
    }
}
