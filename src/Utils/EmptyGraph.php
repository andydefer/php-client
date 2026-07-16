<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Utils;

use AndyDefer\PhpClient\Abstracts\Graph;

final class EmptyGraph extends Graph
{
    public function __construct() {}
}
