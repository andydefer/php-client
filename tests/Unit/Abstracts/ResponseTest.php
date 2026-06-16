<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\Abstracts;

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HeaderType;
use AndyDefer\PhpClient\Enums\HttpStatusCode;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonListStruct;
use AndyDefer\PhpClient\Tests\Fixtures\Responses\TestErrorResponse;
use AndyDefer\PhpClient\Tests\Fixtures\Responses\TestResponse;
use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;

final class ResponseTest extends TestCase
{
    // ==================== HELPER ====================

    /**
     * Crée un ResponseBodyVO avec des données Pokémon valides.
     */
    private function createResponseBodyVO(): ResponseBodyVO
    {
        $json = '{"count":2,"next":null,"previous":null,"results":[{"name":"bulbasaur","url":"..."}]}';

        return new ResponseBodyVO($json, PokemonListStruct::class);
    }

    /**
     * Crée un ResponseBodyVO vide.
     */
    private function createEmptyResponseBodyVO(): ResponseBodyVO
    {
        return new ResponseBodyVO(
            'null',
            PokemonListStruct::class,
            ContentType::JSON
        );
    }

    /**
     * Crée un ResponseBodyVO Problem JSON.
     */
    private function createProblemJsonResponseBodyVO(): ResponseBodyVO
    {
        $problemJson = '{"type":"https://example.com/errors","title":"Invalid input","status":400}';

        return new ResponseBodyVO($problemJson, PokemonListStruct::class, ContentType::PROBLEM_JSON);
    }

    // ==================== CONSTRUCTION TESTS ====================

    /**
     * @test
     */
    public function test_response_can_be_created(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertInstanceOf(Response::class, $response);
        $this->assertInstanceOf(HttpStatusCode::class, $response->getStatusCode());
        $this->assertInstanceOf(ResponseBodyVO::class, $response->getBody());
        $this->assertInstanceOf(HeadersVO::class, $response->getHeaders());
    }

    /**
     * @test
     */
    public function test_response_with_ok_status(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::OK, $response->getStatusCode());
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isError());
    }

    /**
     * @test
     */
    public function test_response_with_created_status(): void
    {
        $response = new TestResponse(
            HttpStatusCode::CREATED,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::CREATED, $response->getStatusCode());
        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isError());
    }

    /**
     * @test
     */
    public function test_response_with_bad_request_status(): void
    {
        $response = new TestErrorResponse(
            HttpStatusCode::BAD_REQUEST,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::BAD_REQUEST, $response->getStatusCode());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
    }

    /**
     * @test
     */
    public function test_response_with_unauthorized_status(): void
    {
        $response = new TestErrorResponse(
            HttpStatusCode::UNAUTHORIZED,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::UNAUTHORIZED, $response->getStatusCode());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
    }

    /**
     * @test
     */
    public function test_response_with_not_found_status(): void
    {
        $response = new TestErrorResponse(
            HttpStatusCode::NOT_FOUND,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::NOT_FOUND, $response->getStatusCode());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
    }

    /**
     * @test
     */
    public function test_response_with_internal_server_error_status(): void
    {
        $response = new TestErrorResponse(
            HttpStatusCode::INTERNAL_SERVER_ERROR,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
    }

    // ==================== GETTER TESTS ====================

    /**
     * @test
     */
    public function test_response_get_status_code_returns_http_status_code(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertInstanceOf(HttpStatusCode::class, $response->getStatusCode());
        $this->assertSame(HttpStatusCode::OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function test_response_get_body_returns_response_body_vo(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertInstanceOf(ResponseBodyVO::class, $response->getBody());
        $this->assertInstanceOf(PokemonListStruct::class, $response->getBody()->getValue());
    }

    /**
     * @test
     */
    public function test_response_get_headers_returns_headers_vo(): void
    {
        $headers = new HeadersVO;
        $headers->setContentType(ContentType::JSON);

        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            $headers
        );

        $this->assertInstanceOf(HeadersVO::class, $response->getHeaders());
        $this->assertSame('application/json', $response->getHeaders()->get(HeaderType::CONTENT_TYPE));
    }

    // ==================== HEADERS TESTS ====================

    /**
     * @test
     */
    public function test_response_with_custom_headers(): void
    {
        $headers = new HeadersVO;
        $headers
            ->setContentType(ContentType::JSON)
            ->setAuthorization('token')
            ->setHost('api.example.com');

        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            $headers
        );

        $this->assertSame('application/json', $response->getHeaders()->get(HeaderType::CONTENT_TYPE));
        $this->assertSame('Bearer token', $response->getHeaders()->get(HeaderType::AUTHORIZATION));
        $this->assertSame('api.example.com', $response->getHeaders()->get(HeaderType::HOST));
    }

    /**
     * @test
     */
    public function test_response_with_empty_headers(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertEmpty($response->getHeaders()->toArray());
    }

    // ==================== RESPONSE BODY TESTS ====================

    /**
     * @test
     */
    public function test_response_body_can_be_accessed(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $body = $response->getBody();
        $this->assertInstanceOf(ResponseBodyVO::class, $body);
        $this->assertFalse($body->isEmpty());
        $this->assertTrue($body->isValidJson());
    }

    /**
     * @test
     */
    public function test_response_body_struct_can_be_accessed(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $struct = $response->getBody()->getValue();
        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertSame(2, $struct->count);
        $this->assertCount(1, $struct->results);
    }

    // ==================== CONCRETE RESPONSE TESTS ====================

    /**
     * @test
     */
    public function test_concrete_response_can_be_created(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::OK, $response->getStatusCode());
        $this->assertTrue($response->isSuccess());
        $this->assertNotEmpty($response->getCustomData());
    }

    /**
     * @test
     */
    public function test_concrete_response_with_error(): void
    {
        $response = new TestErrorResponse(
            HttpStatusCode::NOT_FOUND,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $this->assertSame(HttpStatusCode::NOT_FOUND, $response->getStatusCode());
        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertSame('Resource not found', $response->getErrorMessage());
    }

    // ==================== SUCCESS/ERROR TESTS ====================

    /**
     * @test
     */
    public function test_response_success_codes(): void
    {
        $successCodes = [
            HttpStatusCode::OK,
            HttpStatusCode::CREATED,
            HttpStatusCode::ACCEPTED,
            HttpStatusCode::NO_CONTENT,
        ];

        foreach ($successCodes as $code) {
            $response = new TestResponse(
                $code,
                $this->createResponseBodyVO(),
                new HeadersVO
            );
            $this->assertTrue($response->isSuccess(), "Code {$code->value} should be success");
            $this->assertFalse($response->isError(), "Code {$code->value} should not be error");
        }
    }

    /**
     * @test
     */
    public function test_response_error_codes(): void
    {
        $errorCodes = [
            HttpStatusCode::BAD_REQUEST,
            HttpStatusCode::UNAUTHORIZED,
            HttpStatusCode::FORBIDDEN,
            HttpStatusCode::NOT_FOUND,
            HttpStatusCode::INTERNAL_SERVER_ERROR,
            HttpStatusCode::SERVICE_UNAVAILABLE,
        ];

        foreach ($errorCodes as $code) {
            $response = new TestErrorResponse(
                $code,
                $this->createResponseBodyVO(),
                new HeadersVO
            );
            $this->assertFalse($response->isSuccess(), "Code {$code->value} should not be success");
            $this->assertTrue($response->isError(), "Code {$code->value} should be error");
        }
    }

    // ==================== IMMUTABILITY TESTS ====================

    /**
     * @test
     */
    public function test_response_is_immutable(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createResponseBodyVO(),
            new HeadersVO
        );

        $originalStatusCode = $response->getStatusCode();
        $originalBody = $response->getBody();
        $originalHeaders = $response->getHeaders();

        $this->assertSame($originalStatusCode, $response->getStatusCode());
        $this->assertSame($originalBody, $response->getBody());
        $this->assertSame($originalHeaders, $response->getHeaders());
    }

    // ==================== EDGE CASES TESTS ====================

    /**
     * @test
     */
    public function test_response_with_empty_body(): void
    {
        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createEmptyResponseBodyVO(),
            new HeadersVO
        );

        $this->assertTrue($response->getBody()->isEmpty());
        $this->assertNull($response->getBody()->getValue());
    }

    /**
     * @test
     */
    public function test_response_with_problem_json(): void
    {
        $response = new TestErrorResponse(
            HttpStatusCode::BAD_REQUEST,
            $this->createProblemJsonResponseBodyVO(),
            new HeadersVO
        );

        $this->assertTrue($response->getBody()->isProblemJson());
        $this->assertTrue($response->isError());
    }

    /**
     * @test
     */
    public function test_response_with_custom_headers_and_empty_body(): void
    {
        $headers = new HeadersVO;
        $headers->setContentType(ContentType::JSON);

        $response = new TestResponse(
            HttpStatusCode::OK,
            $this->createEmptyResponseBodyVO(),
            $headers
        );

        $this->assertTrue($response->getBody()->isEmpty());
        $this->assertSame('application/json', $response->getHeaders()->get(HeaderType::CONTENT_TYPE));
    }
}
