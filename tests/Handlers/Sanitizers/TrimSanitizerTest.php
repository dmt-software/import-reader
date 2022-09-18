<?php

namespace DMT\Test\Import\Reader\Handlers\Sanitizers;

use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use PHPUnit\Framework\TestCase;

class TrimSanitizerTest extends TestCase
{
    /**
     * @dataProvider provideValue
     *
     * @param string|null $chars
     * @param int|null $direction
     * @param string $expected
     */
    public function testSanitize(?string $chars, ?int $direction, string $expected): void
    {
        $sanitizer = new TrimSanitizer($chars, $direction);
        $this->assertSame($expected, $sanitizer->sanitize(' value. '));
    }

    public function provideValue(): iterable
    {
        return [
            'trim white space on both sides' => [null, null, 'value.'],
            'trim white space left side' => [null, TrimSanitizer::TRIM_LEFT, 'value. '],
            'trim white space right side' => [null, TrimSanitizer::TRIM_RIGHT, ' value.'],
            'trim custom on both sides' => [' v.', null, 'alue'],
            'trim custom on left side' => [' v.', TrimSanitizer::TRIM_LEFT, 'alue. '],
            'trim custom on right side' => [' v.', TrimSanitizer::TRIM_RIGHT, ' value'],

        ];
    }
}
