<?php

namespace DMT\Test\Import\Reader\Decorators;

use DMT\Import\Reader\Decorators\DeserializeToObjectDecorator;
use DMT\Test\Import\Reader\Fixtures\Language;
use DMT\Test\Import\Reader\Fixtures\Program;
use JMS\Serializer\SerializerBuilder;
use PHPUnit\Framework\TestCase;

class DeserializeToObjectDecoratorTest extends TestCase
{
    /**
     * @dataProvider provideRow
     *
     * @param $currentRow
     */
    public function testDecorate($currentRow): void
    {
        $decorator = new DeserializeToObjectDecorator(SerializerBuilder::create()->build(), Program::class);
        $program = $decorator->apply($currentRow);

        $this->assertInstanceOf(Program::class, $program);
        $this->assertContainsOnlyInstancesOf(Language::class, $program->languages);
        $this->assertNotEmpty($program->languages[0]->name);
        $this->assertNotEmpty($program->languages[0]->since);
        $this->assertNotEmpty($program->languages[0]->author);
    }

    public function provideRow(): iterable
    {
        return [
            'xml' => [
                '<program>
                    <license>open source</license>
                    <languages>
                        <language><name>javascript</name><since>1995</since><by>Brendan Eich</by></language>
                        <language><name>php</name><since>1995</since><by>Rasmus Lerdorf</by></language>
                    </languages>
                </program>'
            ],
            'json' => [
                '{
                    "license": "closed source",
                    "languages": [
                        {
                            "name": "C#",
                            "since": 2000,
                            "by": "Anders Hejlsberg"
                        }
                    ] 
                }'
            ]
        ];
    }
}
