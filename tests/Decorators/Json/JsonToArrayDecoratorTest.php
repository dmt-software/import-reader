<?php

namespace DMT\Test\Import\Reader\Decorators\Json;

use ArrayObject;
use DMT\Import\Reader\Decorators\Json\JsonToArrayDecorator;
use PHPUnit\Framework\TestCase;
use stdClass;

class JsonToArrayDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param stdClass $row
     * @param ArrayObject $expected
     */
    public function testDecorate(stdClass $row, ArrayObject $expected): void
    {
        $decorator = new JsonToArrayDecorator();

        $this->assertEquals($expected, $decorator->decorate($row));
    }

    public function provideRow(): iterable
    {
        return [
            'json object' => [
                json_decode('{"type": "json"}'),
                new ArrayObject(['type' => 'json'])
            ],
            'nested objects' => [
                json_decode('{"type": {"name": "xml"}}'),
                new ArrayObject(['type' => ['name' =>'xml']])
            ],
        ];
    }

    public function testDecorateWithMapping(): void
    {
        $row = json_decode('{
            "employee": "John Do",
            "role": "developer",
            "experience": {
                "years": 5
            },
            "languages": ["php", "javascript"]
        }');

        $decorator = new JsonToArrayDecorator([
            'employee' => 'name',
            'experience.years' => 'experience',
            'languages' => 'languages'
        ]);

        $this->assertEquals(
            new ArrayObject([
                'name' => 'John Do',
                'experience' => '5',
                'languages' => ['php', 'javascript']
            ]),
            $decorator->decorate($row)
        );
    }
}
