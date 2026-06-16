<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Enums;

use AndyDefer\DomainStructures\Traits\Enumable;

enum TestUserGrade: int
{
    use Enumable;

    case BRONZE = 1;
    case SILVER = 2;
    case GOLD = 3;
    case PLATINUM = 4;
}
