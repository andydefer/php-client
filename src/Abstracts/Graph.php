<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Abstracts;

abstract class Graph extends HydratableStructure
{
    /**
     * Les Graph conservent la casse originale des clés
     * car ils représentent des données d'API.
     */
    protected function normalizeKey(string $key): string
    {
        return $key;
    }
}
