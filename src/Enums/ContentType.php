<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Enums;

enum ContentType: string
{
    // JSON
    case JSON = 'application/json';
    case JSON_UTF8 = 'application/json; charset=utf-8';

    // Problem JSON (RFC 7807)
    case PROBLEM_JSON = 'application/problem+json';
    case PROBLEM_JSON_UTF8 = 'application/problem+json; charset=utf-8';

    // Formulaire
    case FORM = 'application/x-www-form-urlencoded';

    public function isJson(): bool
    {
        return in_array($this, [
            self::JSON,
            self::JSON_UTF8,
            self::PROBLEM_JSON,
            self::PROBLEM_JSON_UTF8,
        ]);
    }

    public function isForm(): bool
    {
        return $this === self::FORM;
    }

    public function withCharset(string $charset = 'utf-8'): self
    {
        return match ($this) {
            self::JSON => self::JSON_UTF8,
            self::PROBLEM_JSON => self::PROBLEM_JSON_UTF8,
            default => $this,
        };
    }

    public function withoutCharset(): self
    {
        return match ($this) {
            self::JSON_UTF8 => self::JSON,
            self::PROBLEM_JSON_UTF8 => self::PROBLEM_JSON,
            default => $this,
        };
    }

    public static function default(): self
    {
        return self::JSON;
    }

    public static function defaultAccept(): self
    {
        return self::JSON;
    }
}
