<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Clients;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Contracts\ClientInterface;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;
use AndyDefer\PhpVo\Enums\HttpStatusCode;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

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
                contentType: $this->resolveResponseContentType($guzzleResponse),
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

    /**
     * Resolve the ContentType from the response headers.
     *
     * Falls back to ContentType::default() when the header is missing
     * or does not map to a known ContentType value.
     */
    private function resolveResponseContentType(ResponseInterface $response): ContentType
    {
        $header = $response->getHeaderLine('Content-Type');

        if ($header === '') {
            return ContentType::default();
        }

        // Strip parameters such as "; charset=utf-8"
        $value = trim(explode(';', $header, 2)[0]);

        return ContentType::tryFrom($value) ?? ContentType::default();
    }

    private function buildOptions(Request $request): array
    {
        $options = [];

        // 1. Headers fournis explicitement par la requête
        $headers = $request->getHeaders()->toArray();

        // 2. Ajouter le Content-Type du body s'il n'est pas déjà défini
        $body = $request->getBody();

        if (! $body->isEmpty() && ! isset($headers['Content-Type'])) {
            $headers['Content-Type'] = $body->getContentType()->value;
        }

        if (! empty($headers)) {
            $options['headers'] = $headers;
        }

        // 3. Body
        if (! $body->isEmpty()) {
            $options['body'] = $body->toString();
        }

        // 4. Options additionnelles
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
