<?php

// tests/Fixtures/Collections/EnumCollection.php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Collections;

use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\DomainStructures\Tests\Fixtures\Enums\TestUserGrade;
use AndyDefer\DomainStructures\Tests\Fixtures\Enums\TestUserRole;
use AndyDefer\DomainStructures\Tests\Fixtures\Enums\TestUserStatus;

final class EnumCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(TestUserRole::class, TestUserStatus::class, TestUserGrade::class);
    }
}
