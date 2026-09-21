<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Contracts;

use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;
use AndyDefer\PhpVo\Enums\HttpStatusCode;

interface ResponseInterface
{
    public function getStatusCode(): HttpStatusCode;

    public function getBody(): ResponseBodyVO;

    public function getHeaders(): HeadersVO;

    public function isSuccess(): bool;

    public function isError(): bool;
}
