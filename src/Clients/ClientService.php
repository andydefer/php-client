<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Clients;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Contracts\ClientInterface;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\Enums\HttpStatusCode;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

final class ClientService implements ClientInterface
{
    private GuzzleClient $client;

    public function __construct(?GuzzleClient $client = null)
    {
        $this->client = $client ?? new GuzzleClient;
    }

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function get(string $uri, Request $request, string $responseClass): Response
    {
        return $this->send(HttpMethod::GET, $uri, $request, $responseClass);
    }

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function post(string $uri, Request $request, string $responseClass): Response
    {
        return $this->send(HttpMethod::POST, $uri, $request, $responseClass);
    }

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function put(string $uri, Request $request, string $responseClass): Response
    {
        return $this->send(HttpMethod::PUT, $uri, $request, $responseClass);
    }

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function patch(string $uri, Request $request, string $responseClass): Response
    {
        return $this->send(HttpMethod::PATCH, $uri, $request, $responseClass);
    }

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    public function delete(string $uri, Request $request, string $responseClass): Response
    {
        return $this->send(HttpMethod::DELETE, $uri, $request, $responseClass);
    }

    /**
     * @template TResponse of Response
     *
     * @param  class-string<TResponse>  $responseClass
     * @return TResponse
     */
    private function send(HttpMethod $method, string $uri, Request $request, string $responseClass): Response
    {
        $options = $this->buildOptions($request);

        try {
            $guzzleResponse = $this->client->request(
                $method->value,
                $uri,
                $options
            );

            $statusCode = HttpStatusCode::tryFrom($guzzleResponse->getStatusCode())
                ?? HttpStatusCode::INTERNAL_SERVER_ERROR;

            $body = new ResponseBodyVO(
                content: $guzzleResponse->getBody()->getContents(),
                contentType: $request->getBody()->getContentType(),
                structClass: $this->getStructClassFromResponse($responseClass)
            );

            $headers = $request->getHeaders();

            return new $responseClass($statusCode, $body, $headers);
        } catch (GuzzleException $e) {
            throw new \RuntimeException(
                sprintf('HTTP request failed: %s', $e->getMessage()),
                $e->getCode(),
                $e
            );
        }
    }

    private function buildOptions(Request $request): array
    {
        $options = [];

        // Headers
        $headers = $request->getHeaders()->toArray();
        if (! empty($headers)) {
            $options['headers'] = $headers;
        }

        // Body
        $body = $request->getBody();
        if (! $body->isEmpty()) {
            $options['body'] = $body->toString();
        }

        // Options
        $options = array_merge($options, $request->getOptions()->toArray());

        return $options;
    }

    /**
     * @param  class-string<Response>  $responseClass
     */
    private function getStructClassFromResponse(string $responseClass): ?string
    {
        if (method_exists($responseClass, 'getStructClass')) {
            return $responseClass::getStructClass();
        }

        return null;
    }
}
