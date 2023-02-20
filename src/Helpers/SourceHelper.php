<?php

namespace DMT\Import\Reader\Helpers;

use RuntimeException;

class SourceHelper
{
    public const SOURCE_TYPE_FILE = 'file';
    public const SOURCE_TYPE_STREAM = 'stream';
    public const SOURCE_TYPE_STRING = 'contents';

    /**
     * @param resource|string $source
     * @return string
     */
    public static function detect($source): string
    {
        if (is_resource($source)) {
            return self::SOURCE_TYPE_STREAM;
        }

        if (!is_string($source)) {
            throw new RuntimeException('unsupported source type');
        }

        $m = [];
        if (is_file($source) || preg_match('~^(\S+)://~', $source, $m) && in_array($m[1], stream_get_wrappers())) {
            return self::SOURCE_TYPE_FILE;
        }

        return self::SOURCE_TYPE_STRING;
    }
}
