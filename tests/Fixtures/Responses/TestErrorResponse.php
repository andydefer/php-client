<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Responses;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Enums\HttpStatusCode;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;

final class TestErrorResponse extends Response
{
    public function __construct(
        HttpStatusCode $statusCode,
        ResponseBodyVO $body,
        HeadersVO $headers
    ) {
        parent::__construct($statusCode, $body, $headers);
    }

    /**
     * Retourne un message d'erreur.
     */
    public function getErrorMessage(): string
    {
        return 'Resource not found';
    }
}
