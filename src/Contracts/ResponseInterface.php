<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Contracts;

use AndyDefer\PhpClient\Enums\HttpStatusCode;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;

interface ResponseInterface
{
    public function getStatusCode(): HttpStatusCode;

    public function getBody(): ResponseBodyVO;

    public function getHeaders(): HeadersVO;
}
