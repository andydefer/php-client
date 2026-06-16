<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Enums;

enum ConnectionType: string
{
    case KEEP_ALIVE = 'keep-alive';
    case CLOSE = 'close';
    case UPGRADE = 'upgrade';

    public static function default(): self
    {
        return self::KEEP_ALIVE;
    }
}
