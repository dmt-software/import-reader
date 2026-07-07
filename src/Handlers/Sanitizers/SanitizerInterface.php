<?php

declare(strict_types=1);

namespace DMT\Import\Reader\Handlers\Sanitizers;

interface SanitizerInterface
{
    /**
     * Sanitize a row.
     *
     * This is executed before the handler hands the row to the reader to decorate.
     *
     * @param string|array $currentRow The row to sanitize.
     */
    public function sanitize(string|array $currentRow): string|array;
}
