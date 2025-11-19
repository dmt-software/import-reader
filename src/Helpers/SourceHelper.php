<?php

namespace DMT\Import\Reader\Helpers;

use RuntimeException;

class SourceHelper
{
    public const string SOURCE_TYPE_FILE = 'file';
    public const string SOURCE_TYPE_STREAM = 'stream';
    public const string SOURCE_TYPE_STRING = 'contents';

    public static function detect(mixed $source): string
    {
        if (is_resource($source)) {
            return self::SOURCE_TYPE_STREAM;
        }

        if (!is_string($source)) {
            throw new RuntimeException('unsupported source type');
        }

        $m = [];
        if (preg_match('~^(\S+)://~', $source, $m) && in_array($m[1], stream_get_wrappers()) || is_file($source)) {
            return self::SOURCE_TYPE_FILE;
        }

        return self::SOURCE_TYPE_STRING;
    }
}
