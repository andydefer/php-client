<?php

declare(strict_types=1);

namespace AndyDefer\PhpHttpClient\Enums;

enum ContentEncoding: string
{
    case GZIP = 'gzip';
    case DEFLATE = 'deflate';
    case BR = 'br'; // Brotli
    case ZSTD = 'zstd';
    case IDENTITY = 'identity'; // Pas de compression

    public function isCompressed(): bool
    {
        return $this !== self::IDENTITY;
    }

    public static function default(): self
    {
        return self::IDENTITY;
    }

    public static function fromString(string $encoding): ?self
    {
        return match (strtolower($encoding)) {
            'gzip' => self::GZIP,
            'deflate' => self::DEFLATE,
            'br' => self::BR,
            'zstd' => self::ZSTD,
            'identity' => self::IDENTITY,
            default => null,
        };
    }
}
