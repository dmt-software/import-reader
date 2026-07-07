<?php

namespace DMT\Test\Import\Reader\Decorators;

use ArrayObject;
use Closure;
use DMT\Import\Reader\Decorators\CallbackDecorator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class CallbackDecoratorTest extends TestCase
{
    #[DataProvider('provideRow')]
    public function testDecorate(Closure $callback, object $currentRow, object $expected): void
    {
        $decorator = new CallbackDecorator($callback);

        $this->assertEquals($expected, $decorator->decorate($currentRow));
    }

    public static function provideRow(): iterable
    {
        return [
            [
                function (SimpleXMLElement $currentRow): void {
                    $currentRow->addChild('type', (string) $currentRow->car->attributes()['type']);
                    foreach ($currentRow->xpath('car/@*') as $attribute) {
                        unset($attribute[0]);
                    }
                },
                simplexml_load_string('<root><car type="focus">ford</car></root>'),
                simplexml_load_string('<root><car>ford</car><type>focus</type></root>')
            ],
            [
                function (object $currentRow): void {
                    $currentRow->lorem = 'ipsum';
                },
                json_decode('{"foo": "bar"}'),
                (object) ['foo' => 'bar', 'lorem' => 'ipsum']
            ],
            [
                function (object $currentRow): void {
                    $currentRow['col2'] = 'value2';
                },
                new ArrayObject(['col1' => 'value1']),
                new ArrayObject(['col1' => 'value1', 'col2' => 'value2']),
            ],
        ];
    }
}
