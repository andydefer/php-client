<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Enums;

enum PokemonType: string
{
    case GRASS = 'grass';
    case POISON = 'poison';
    case FIRE = 'fire';
    case WATER = 'water';
    case ELECTRIC = 'electric';
    case GROUND = 'ground';
    case FLYING = 'flying';
    case PSYCHIC = 'psychic';
    case BUG = 'bug';
    case ROCK = 'rock';
    case GHOST = 'ghost';
    case DARK = 'dark';
    case STEEL = 'steel';
    case ICE = 'ice';
    case DRAGON = 'dragon';
    case FAIRY = 'fairy';
    case NORMAL = 'normal';
    case FIGHTING = 'fighting';
}

enum PokemonStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case LEGENDARY = 'legendary';
    case MYTHICAL = 'mythical';
}
