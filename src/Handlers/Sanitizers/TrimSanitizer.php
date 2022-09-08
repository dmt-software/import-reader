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
     * @param string $chars
     * @param int $direction
     */
    public function __construct(string $chars = " \t\n\r\0\x0B", int $direction = self::TRIM_LEFT | self::TRIM_RIGHT)
    {
        $this->chars = $chars;
        $this->direction = $direction;
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
            $currentRow = array_map($method, $currentRow, [$this->chars]);
        }

        return $currentRow;
    }
}
