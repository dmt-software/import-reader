<?php

declare(strict_types=1);

namespace DMT\Test\Import\Reader\Handlers\Sanitizers;

use DMT\Import\Reader\Handlers\Sanitizers\TrimSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TrimSanitizerTest extends TestCase
{
    #[DataProvider('provideValue')]
    public function testSanitize(?string $chars, ?int $direction, string $expected): void
    {
        $sanitizer = new TrimSanitizer($chars, $direction);
        $this->assertSame($expected, $sanitizer->sanitize(' value. '));
    }

    public static function provideValue(): iterable
    {
        return [
            [null, null, 'value.'],
            [null, TrimSanitizer::TRIM_LEFT, 'value. '],
            [null, TrimSanitizer::TRIM_RIGHT, ' value.'],
            [' v.', null, 'alue'],
            [' v.', TrimSanitizer::TRIM_LEFT, 'alue. '],
            [' v.', TrimSanitizer::TRIM_RIGHT, ' value'],

        ];
    }
}
