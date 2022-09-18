<?php

namespace DMT\Test\Import\Reader\Handlers\Sanitizers;

use DMT\Import\Reader\Handlers\Sanitizers\EncodingSanitizer;
use PHPUnit\Framework\TestCase;

class EncodingSanitizerTest extends TestCase
{
    public function testSanitize(): void
    {
        $sanitizer = new EncodingSanitizer('UTF-8', 'ASCII//TRANSLIT');
        $this->assertSame('iou', $sanitizer->sanitize('ìöú'));

        $sanitizer = new EncodingSanitizer('UTF-8', 'ASCII//IGNORE');
        $this->assertSame(['steering wheel', '30cm '], $sanitizer->sanitize(['steering wheel', '30cm ø']));
    }
}
