<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Contracts;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Response;

interface ClientInterface
{
    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function get(string $uri, Request $request, string $responseClass): Response;

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function post(string $uri, Request $request, string $responseClass): Response;

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function put(string $uri, Request $request, string $responseClass): Response;

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function patch(string $uri, Request $request, string $responseClass): Response;

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function delete(string $uri, Request $request, string $responseClass): Response;
}
