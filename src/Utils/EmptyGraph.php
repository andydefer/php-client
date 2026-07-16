<?php

declare(strict_types=1);

namespace AndyDefer\PhpPawapay\Structures;

use AndyDefer\PhpClient\Abstracts\Graph;

final class EmptyGraph extends Graph
{
    public function __construct() {}
}
