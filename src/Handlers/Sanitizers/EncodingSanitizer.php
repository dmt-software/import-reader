<?php

namespace DMT\Import\Reader\Handlers\Sanitizers;

/**
 * Encoding sanitizer.
 *
 * This fixes encoding problems by using iconv to transliterate between character sets.
 */
final readonly class EncodingSanitizer implements SanitizerInterface
{
    /**
     * @param string $from The encoding of the file.
     * @param string $to The encoding to transform into.
     */
    public function __construct(private string $from, private string $to = 'UTF-8//TRANSLIT')
    {
    }

    /**
     * @inheritDoc
     */
    public function sanitize(string|array $currentRow): string|array
    {
        if (is_string($currentRow)) {
            $currentRow = iconv($this->from, $this->to, $currentRow);
        } elseif (is_array($currentRow)) {
            $currentRow = array_map(fn($col) => iconv($this->from, $this->to, (string) $col), $currentRow);
        }

        return $currentRow;
    }
}
