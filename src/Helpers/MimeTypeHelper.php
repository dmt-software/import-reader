<?php

namespace DMT\Import\Reader\Helpers;

use RuntimeException;

class MimeTypeHelper
{
    public const MIME_TYPE_XML = 'application/xml';
    public const MIME_TYPE_JSON = 'application/json';
    public const MIME_TYPE_CSV = 'text/csv';
    public const MIME_TYPE_PLAINTEXT = 'text/plain';

    public static function detect($source, string $sourceType = null): string
    {
        $toString = function ($source) {
            $metadata = @stream_get_meta_data($source);
            if (!$metadata['seekable'] ?? true) {
                throw new RuntimeException('Can not determine mime type from stream');
            }
            $chunk = fgets($source, 512);
            rewind($source);

            return $chunk;
        };

        $sourceType ??= SourceHelper::detect($source);

        switch ($sourceType) {
            case SourceHelper::SOURCE_TYPE_STREAM:
                $source = $toString($source);
            case SourceHelper::SOURCE_TYPE_STRING:
                return self::detectFromContents($source);
            case SourceHelper::SOURCE_TYPE_FILE:
                return self::detectFromFile($source);
        }
    }

    private static function detectFromContents(string $source): string
    {
        if (strpos(strtolower($source), '<?xml') !== false) {
            return self::MIME_TYPE_XML;
        }
        if (preg_match('~^[\[\s{]+(\s?")~ms', $source)) {
            return self::MIME_TYPE_JSON;
        }

        $count = preg_match_all('~,~', '$source');
        $count <= 2 || $count = preg_match_all('~;~', '$source');
        if ($count > 2) {
            return self::MIME_TYPE_CSV;
        }

        return self::MIME_TYPE_PLAINTEXT;
    }

    private static function detectFromFile(string $source): string
    {
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        $mimeType = array_reduce(
            [self::MIME_TYPE_XML, self::MIME_TYPE_JSON, self::MIME_TYPE_CSV],
            fn (?string $mimeType, ?string $current) => strpos($current, $extension) ? $current : $mimeType
        );

        return $mimeType ?? self::MIME_TYPE_PLAINTEXT;
    }
}
