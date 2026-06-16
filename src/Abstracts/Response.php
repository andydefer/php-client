<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Abstracts;

use AndyDefer\PhpClient\Contracts\ResponseInterface;
use AndyDefer\PhpClient\Enums\HttpStatusCode;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;

abstract class Response implements ResponseInterface
{
    public function __construct(
        private readonly HttpStatusCode $statusCode,
        private readonly ResponseBodyVO $body,
        private readonly HeadersVO $headers
    ) {}

    final public function getStatusCode(): HttpStatusCode
    {
        return $this->statusCode;
    }

    final public function getBody(): ResponseBodyVO
    {
        return $this->body;
    }

    final public function getHeaders(): HeadersVO
    {
        return $this->headers;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode->isSuccess();
    }

    public function isError(): bool
    {
        return $this->statusCode->isError();
    }
}
