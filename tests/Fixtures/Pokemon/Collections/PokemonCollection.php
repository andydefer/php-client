<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\DomainStructures\Collections\Utility\StringTypedCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonGraph;

/**
 * @extends AbstractTypedCollection<PokemonGraph>
 */
final class PokemonCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(PokemonGraph::class);
    }

    public function getNames(): StringTypedCollection
    {
        $names = new StringTypedCollection;
        foreach ($this->items as $pokemon) {
            $names->add($pokemon->name);
        }

        return $names;
    }
}
