<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\Abstracts;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HeaderType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\Enums\OptionType;
use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\OptionsVO;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

final class RequestTest extends TestCase
{
    // ==================== HELPER ====================

    private function createTestRequest(): Request
    {
        return new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::POST;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://api.example.com/v2/test');
            }

            protected function setBody(): RequestBodyVO
            {
                $struct = new class extends Struct
                {
                    public function __construct(
                        public readonly string $name = 'test',
                        public readonly int $value = 123,
                    ) {}
                };

                return new RequestBodyVO(new $struct, ContentType::JSON);
            }
        };
    }

    // ==================== CONSTRUCTION TESTS ====================

    public function test_request_can_be_created(): void
    {
        $request = $this->createTestRequest();

        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(HeadersVO::class, $request->getHeaders());
        $this->assertInstanceOf(OptionsVO::class, $request->getOptions());
    }

    public function test_request_has_default_headers_and_options(): void
    {
        $request = $this->createTestRequest();

        $headers = $request->getHeaders();
        $options = $request->getOptions();

        $this->assertEmpty($headers->toArray());
        $this->assertEmpty($options->toArray());
    }

    // ==================== GETTER TESTS ====================

    public function test_request_get_method_returns_http_method(): void
    {
        $request = $this->createTestRequest();

        $this->assertInstanceOf(HttpMethod::class, $request->getMethod());
        $this->assertSame(HttpMethod::POST, $request->getMethod());
    }

    public function test_request_get_url_returns_url_vo(): void
    {
        $request = $this->createTestRequest();

        $this->assertInstanceOf(UrlVO::class, $request->getUrl());
        $this->assertSame('https://api.example.com/v2/test', $request->getUrl()->getValue());
    }

    public function test_request_get_body_returns_request_body_vo(): void
    {
        $request = $this->createTestRequest();

        $this->assertInstanceOf(RequestBodyVO::class, $request->getBody());
        $this->assertTrue($request->getBody()->isJson());
        $this->assertStringContainsString('test', $request->getBody()->toString());
    }

    public function test_request_get_headers_returns_headers_vo(): void
    {
        $request = $this->createTestRequest();

        $this->assertInstanceOf(HeadersVO::class, $request->getHeaders());
    }

    public function test_request_get_options_returns_options_vo(): void
    {
        $request = $this->createTestRequest();

        $this->assertInstanceOf(OptionsVO::class, $request->getOptions());
    }

    // ==================== HEADERS MODIFICATION TESTS ====================

    public function test_request_headers_can_be_modified(): void
    {
        $request = $this->createTestRequest();

        $request->getHeaders()
            ->setHost('api.example.com')
            ->setContentType(ContentType::JSON)
            ->setAuthorization('token');

        $this->assertSame('api.example.com', $request->getHeaders()->get(HeaderType::HOST));
        $this->assertSame('application/json', $request->getHeaders()->get(HeaderType::CONTENT_TYPE));
        $this->assertSame('Bearer token', $request->getHeaders()->get(HeaderType::AUTHORIZATION));
    }

    public function test_request_headers_modifications_are_mutable(): void
    {
        $request = $this->createTestRequest();

        $headers = $request->getHeaders();
        $headers->setHost('api.example.com');

        $this->assertSame('api.example.com', $request->getHeaders()->get(HeaderType::HOST));
        $this->assertSame($headers, $request->getHeaders());
    }

    // ==================== OPTIONS MODIFICATION TESTS ====================

    public function test_request_options_can_be_modified(): void
    {
        $request = $this->createTestRequest();

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(true);

        $this->assertTrue($request->getOptions()->has(OptionType::TIMEOUT));
        $this->assertSame(30, $request->getOptions()->get(OptionType::TIMEOUT));
        $this->assertSame(10, $request->getOptions()->get(OptionType::CONNECT_TIMEOUT));
        $this->assertTrue($request->getOptions()->get(OptionType::HTTP_ERRORS));
    }

    public function test_request_options_modifications_are_mutable(): void
    {
        $request = $this->createTestRequest();

        $options = $request->getOptions();
        $options->setTimeout(30);

        $this->assertSame(30, $request->getOptions()->get(OptionType::TIMEOUT));
        $this->assertSame($options, $request->getOptions());
    }

    // ==================== CHAINING TESTS ====================

    public function test_request_headers_and_options_can_be_chained(): void
    {
        $request = $this->createTestRequest();

        $request->getHeaders()
            ->setHost('api.example.com')
            ->setContentType(ContentType::JSON);

        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10);

        $headers = $request->getHeaders()->toArray();
        $options = $request->getOptions()->toArray();

        $this->assertArrayHasKey('Host', $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('timeout', $options);
        $this->assertArrayHasKey('connect_timeout', $options);
    }

    // ==================== CONCRETE REQUEST TESTS ====================

    public function test_concrete_request_can_be_created(): void
    {
        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::GET;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://api.example.com/v2/users');
            }

            protected function setBody(): RequestBodyVO
            {
                $struct = new class extends Struct
                {
                    public function __construct() {}
                };

                return new RequestBodyVO(new $struct, ContentType::JSON);
            }
        };

        $this->assertSame(HttpMethod::GET, $request->getMethod());
        $this->assertSame('https://api.example.com/v2/users', $request->getUrl()->getValue());
        $this->assertInstanceOf(RequestBodyVO::class, $request->getBody());
        $this->assertTrue($request->getBody()->isEmpty());
    }

    public function test_concrete_request_with_form_body(): void
    {
        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::POST;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://api.example.com/v2/login');
            }

            protected function setBody(): RequestBodyVO
            {
                $struct = new class extends Struct
                {
                    public function __construct(
                        public readonly string $username = 'john',
                        public readonly string $password = 'secret',
                    ) {}
                };

                return new RequestBodyVO(new $struct, ContentType::FORM);
            }
        };

        $this->assertTrue($request->getBody()->isForm());
        $this->assertStringContainsString('username=john&password=secret', $request->getBody()->toString());
    }

    // ==================== EDGE CASES TESTS ====================

    public function test_request_with_empty_body(): void
    {
        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::GET;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://api.example.com/v2/users');
            }

            protected function setBody(): RequestBodyVO
            {
                $struct = new class extends Struct
                {
                    public function __construct() {}
                };

                return new RequestBodyVO(new $struct, ContentType::JSON);
            }
        };

        $this->assertTrue($request->getBody()->isEmpty());
        $this->assertSame('[]', $request->getBody()->toString());
    }

    public function test_request_with_custom_headers_via_headers_vo(): void
    {
        $request = $this->createTestRequest();

        $request->getHeaders()->setCustom('X-Custom-Header', 'custom-value');

        $headers = $request->getHeaders()->toArray();
        $this->assertArrayHasKey('X-Custom-Header', $headers);
        $this->assertSame('custom-value', $headers['X-Custom-Header']);
    }

    public function test_request_with_custom_options_via_options_vo(): void
    {
        $request = $this->createTestRequest();

        $request->getOptions()->setCustom('custom_option', 'custom_value');

        $options = $request->getOptions()->toArray();
        $this->assertArrayHasKey('custom_option', $options);
        $this->assertSame('custom_value', $options['custom_option']);
    }
}
