<?php

namespace DMT\Import\Reader\Handlers\Sanitizers;

interface SanitizerInterface
{
    /**
     * Sanitize a row.
     *
     * This is executed before the handler hands the row to the reader to decorate.
     *
     * @param string|array $currentRow The row to sanitize.
     * @return string|array
     */
    public function sanitize($currentRow);
}
