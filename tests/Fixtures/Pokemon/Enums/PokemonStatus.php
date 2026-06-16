<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Enums;

enum PokemonStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case LEGENDARY = 'legendary';
    case MYTHICAL = 'mythical';
}
