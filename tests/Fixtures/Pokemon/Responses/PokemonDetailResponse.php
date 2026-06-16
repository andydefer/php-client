<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\TypeCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonDetailGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonDetailStruct;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonHeight;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonId;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonName;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonWeight;

final class PokemonDetailResponse extends Response
{
    public function getPokemon(): PokemonDetailGraph
    {
        $struct = $this->getBody()->getValue();
        if (! $struct instanceof PokemonDetailStruct) {
            throw new \RuntimeException('Invalid structure type');
        }

        return $struct->data;
    }

    public function getId(): PokemonId
    {
        return $this->getPokemon()->id;
    }

    public function getName(): PokemonName
    {
        return $this->getPokemon()->name;
    }

    public function getHeight(): PokemonHeight
    {
        return $this->getPokemon()->height;
    }

    public function getWeight(): PokemonWeight
    {
        return $this->getPokemon()->weight;
    }

    public function getTypes(): TypeCollection
    {
        return $this->getPokemon()->types;
    }

    public static function getStructClass(): string
    {
        return PokemonDetailStruct::class;
    }
}
