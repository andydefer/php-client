<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Utils;

use AndyDefer\PhpClient\Abstracts\Struct;

final class EmptyStruct extends Struct
{
    public function __construct() {}
}
