<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\PokemonCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonListStruct;

final class PokemonListResponse extends Response
{
    private function getStruct(): PokemonListStruct
    {
        return $this->getBody()->getValue();
    }

    public function getPokemons(): PokemonCollection
    {
        return $this->getStruct()->results;
    }

    public function getCount(): int
    {
        return $this->getStruct()->count ?? 0;
    }

    public function getNext(): ?string
    {
        return $this->getStruct()->next;
    }

    public function getPrevious(): ?string
    {
        return $this->getStruct()->previous;
    }

    public static function getStructClass(): string
    {
        return PokemonListStruct::class;
    }
}
