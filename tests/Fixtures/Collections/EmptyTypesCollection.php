<?php

// tests/Fixtures/Collections/EmptyTypesCollection.php
declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;

/**
 * Collection sans types autorisés (TEST D'ERREUR)
 */
final class EmptyTypesCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct();
    }
}
