<?php

namespace DMT\Import\Reader\Handlers\Sanitizers;

/**
 * Encoding sanitizer.
 *
 * This fixes encoding problems by using iconv to transliterate between character sets.
 */
final class EncodingSanitizer implements SanitizerInterface
{
    private string $from;
    private string $to;

    /**
     * @param string $from The encoding of the file.
     * @param string $to The encoding to transform into.
     */
    public function __construct(string $from, string $to = 'UTF-8//TRANSLIT')
    {
        $this->from = $from;
        $this->to = $to;
    }

    /** @inheritDoc */
    public function sanitize($currentRow)
    {
        if (is_string($currentRow)) {
            $currentRow = iconv($this->from, $this->to, $currentRow);
        } elseif (is_array($currentRow)) {
            $currentRow = array_map(fn($col) => iconv($this->from, $this->to, $col), $currentRow);
        }

        return $currentRow;
    }
}
