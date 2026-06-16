<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Enums;

enum TestStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
