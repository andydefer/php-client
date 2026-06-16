<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Abstracts;

use AndyDefer\DomainStructures\Interfaces\Transformable;
use AndyDefer\DomainStructures\Normalizers\NormalizerChain;
use AndyDefer\DomainStructures\Traits\Hydratable;

abstract class Graph implements Transformable
{
    use Hydratable;

    public function toArray(): array
    {
        return NormalizerChain::get()->normalize($this);

    }
}
