<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Contracts;

use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\OptionsVO;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;

interface RequestInterface
{
    public function getBody(): RequestBodyVO;

    public function getHeaders(): HeadersVO;

    public function getOptions(): OptionsVO;
}
