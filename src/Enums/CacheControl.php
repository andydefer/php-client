<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Enums;

enum CacheControl: string
{
    case NO_CACHE = 'no-cache';
    case NO_STORE = 'no-store';
    case MAX_AGE = 'max-age';
    case MUST_REVALIDATE = 'must-revalidate';
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case ONLY_IF_CACHED = 'only-if-cached';

    public function withMaxAge(int $seconds): string
    {
        return $this->value.', max-age='.$seconds;
    }

    public static function default(): self
    {
        return self::NO_CACHE;
    }
}
