<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\DomainStructures\Collections\Utility\StringTypedCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\TypeGraph;

/**
 * @extends AbstractTypedCollection<TypeGraph>
 */
final class TypeCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(TypeGraph::class);
    }

    public function getTypeNames(): StringTypedCollection
    {
        $names = new StringTypedCollection;
        foreach ($this->items as $type) {
            $names->add($type->name);
        }

        return $names;
    }
}
