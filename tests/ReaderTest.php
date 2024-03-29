<?php

namespace DMT\Test\Import\Reader;

use ArrayObject;
use DMT\Import\Reader\Decorators\Handler\ToSimpleXmlElementDecorator;
use DMT\Import\Reader\Decorators\Xml\XmlElementListDecorator;
use DMT\Import\Reader\Exceptions\ReaderReadException;
use DMT\Import\Reader\Exceptions\UnreadableException;
use DMT\Import\Reader\Handlers\HandlerInterface;
use DMT\Import\Reader\Handlers\Pointers\XmlPathPointer;
use DMT\Import\Reader\Handlers\XmlReaderHandler;
use DMT\Import\Reader\Reader;
use DMT\XmlParser\Parser;
use DMT\XmlParser\Source\StringParser;
use DMT\XmlParser\Tokenizer;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use stdClass;
use Throwable;

class ReaderTest extends TestCase
{
    /**
     * @dataProvider provideRows
     *
     * @param array $rows
     * @param string $expected
     */
    public function testRead(array $rows, string $expected): void
    {
        $reader = new Reader($this->getReaderHandler($rows));

        foreach ($reader->read() as $row => $value) {
            $this->assertInstanceOf($expected, $value);
        }

        $this->assertSame(count($rows), $row);
    }

    public function testReadWithIterableList(): void
    {
        $xml = '<import>
            <types>
                <type>json</type>
                <type>xml</type>
            </types>
            <types>
                <type>csv</type>
            </types>
        </import>';

        $parser = new Parser(
            new Tokenizer(
                new StringParser($xml),
                $config['encoding'] ?? null,
                $config['flags'] ?? 0
            )
        );

        $reader = new Reader(
            new XmlReaderHandler($parser, new XmlPathPointer('import/types')),
            new ToSimpleXmlElementDecorator('import/types'),
            new XmlElementListDecorator('type')
        );

        foreach ($reader->read() as $xmlElement) {
            $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        }
    }

    /**
     * @dataProvider provideRows
     *
     * @param array $rows
     * @param string $expected
     */
    public function testReadWithFilter(array $rows, string $expected): void
    {
        $noCsvFilter = function($row) {
            return !in_array('csv', (array)$row);
        };

        $reader = new Reader($this->getReaderHandler($rows));

        foreach ($reader->read(0, $noCsvFilter) as $value) {
            $this->assertInstanceOf($expected, $value);
            $this->assertNotContains('csv', get_object_vars($value));
        }
    }

    /**
     * @dataProvider provideRows
     *
     * @param array $rows
     * @param string $expected
     */
    public function testReaderSkipRows(array $rows, string $expected): void
    {
        $reader = new Reader($this->getReaderHandler($rows));

        foreach ($reader->read(2) as $row => $value) {
            $this->assertInstanceOf($expected, $value);
            $this->assertGreaterThanOrEqual(3, $row);
        }
    }

    public function provideRows(): iterable
    {
        return [
            'csv' => [[['id', 'type'], ['1', 'csv'],['2', 'json'],['3', 'xml']], ArrayObject::class],
            'xml' => [['<type>json</type>','<type>xml</type>','<type>csv</type>'], SimpleXMLElement::class],
            'json' => [['{"type": "json"}','{"type": "xml"}','{"type": "csv"}'], stdClass::class],
        ];
    }

    public function testReadFailure(): void
    {
        $this->expectExceptionObject(ReaderReadException::readError(2));

        $reader = new Reader($this->getReaderHandler(['{"type": "json"}', UnreadableException::unreadable('json')]));

        foreach ($reader->read() as $value) {
            $this->assertInstanceOf(stdClass::class, $value);
        }
    }

    private function getReaderHandler(array $returnValues)
    {
        $handler = $this->getMockBuilder(HandlerInterface::class)
            ->onlyMethods(['setPointer', 'read'])
            ->getMockForAbstractClass();

        $handler
            ->expects($this->once())
            ->method('setPointer');

        $handler
            ->expects($this->once())
            ->method('read')
            ->willReturnCallback(function () use ($returnValues) {
                $position = 0;
                foreach ($returnValues as $value) {
                    if ($value instanceof Throwable) {
                        throw $value;
                    }
                    yield ++$position => $value;
                }
            });

        return $handler;
    }
}
