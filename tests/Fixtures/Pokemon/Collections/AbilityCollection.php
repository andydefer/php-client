<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\AbilityGraph;

/**
 * @extends AbstractTypedCollection<AbilityGraph>
 */
final class AbilityCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(AbilityGraph::class);
    }
}
