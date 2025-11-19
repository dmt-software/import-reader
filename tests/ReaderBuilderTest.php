<?php

namespace DMT\Test\Import\Reader;

use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\CsvReaderHandler;
use DMT\Import\Reader\Handlers\JsonReaderHandler;
use DMT\Import\Reader\Handlers\Sanitizers\SanitizerInterface;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\Import\Reader\Reader;
use DMT\Import\Reader\ReaderBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ReaderBuilderTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $protocol = new class() {
            private $mapping = [
                'stream_open' => true,
                'stream_eof' => false,
                'url_stat' => [],
            ];
            public function __call(string $func, array $args) {
                return $this->mapping[$func] ?? null;
            }
        };

        stream_wrapper_register('dummy', get_class($protocol));
    }

    public function testCreateHandlerFailure()
    {
        $this->expectExceptionObject(UnreadableException::unreadable('dummy://cars.cxml'));

        $builder = new ReaderBuilder();
        $builder->createHandler('dummy://cars.cxml', []);
    }

    public function testAddSanitizer(): void
    {
        $this->getMockBuilder(SanitizerInterface::class)
            ->setMockClassName('MockSanitizer')
            ->getMock();

        $builder = new ReaderBuilder();
        $builder->addSanitizer('mock', 'MockSanitizer');

        $handler = $builder->createHandler(__DIR__ . '/files/programming.json', ['mock' => '']);
        $this->assertContainsOnlyInstancesOf('MockSanitizer', $this->getPropertyValue($handler, 'sanitizers'));
    }

    /**
     *
     * @param string $file
     * @param array $options
     * @param string $expected
     */
    #[DataProvider('provideOptions')]
    public function testBuild(string $file, array $options, string $expected): void
    {
        $reader = (new ReaderBuilder())->build($file, $options);

        $this->assertInstanceOf(Reader::class, $reader);
        $this->assertInstanceOf($expected, $this->getPropertyValue($reader, 'handler'));
    }

    public static function provideOptions(): iterable
    {
        return [
            [__DIR__ . '/files/cars.xml', [], XmlReaderHandler::class],
            [
                __DIR__ . '/files/cars.xml',
                ['handler' => JsonReaderHandler::class],
                JsonReaderHandler::class
            ]
        ];
    }

    public static function tearDownAfterClass(): void
    {
        stream_wrapper_unregister('dummy');

        parent::tearDownAfterClass();
    }

    private function getPropertyValue(object $object, $property)
    {
        $reader = new ReflectionProperty($object, $property);

        return $reader->getValue($object);
    }
}
