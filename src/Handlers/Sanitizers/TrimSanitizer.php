<?php

namespace DMT\Import\Reader\Handlers\Sanitizers;

/**
 * Trim sanitizer.
 *
 * This strips of any unwanted characters on the left and/or right side of a row.
 */
final class TrimSanitizer implements SanitizerInterface
{
    public const TRIM_LEFT = 1;
    public const TRIM_RIGHT = 2;

    private string $chars;
    private int $direction;

    /**
     * @param string|null $chars
     * @param int|null $direction
     */
    public function __construct(string $chars = null, int $direction = null)
    {
        $this->chars = $chars ?? " \t\n\r\0\x0B";
        $this->direction = $direction ?? self::TRIM_LEFT | self::TRIM_RIGHT;
    }

    public function sanitize($currentRow)
    {
        $method = null;
        if ($this->direction & self::TRIM_LEFT) {
            $method = 'ltrim';
        }
        if ($this->direction & self::TRIM_RIGHT) {
            $method = $method ? 'trim' : 'rtrim';
        }

        if (is_string($currentRow)) {
            $currentRow = call_user_func($method, $currentRow, $this->chars);
        } elseif (is_array($currentRow)) {
            $currentRow = array_map(fn($column) => call_user_func($method, $column, $this->chars), $currentRow);
        }

        return $currentRow;
    }
}
