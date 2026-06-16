<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\ValueObjects;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpClient\Enums\AcceptLanguage;
use AndyDefer\PhpClient\Enums\CacheControl;
use AndyDefer\PhpClient\Enums\ConnectionType;
use AndyDefer\PhpClient\Enums\ContentEncoding;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HeaderType;
use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;

final class HeadersVOTest extends TestCase
{
    // ==================== GENERAL HEADERS TESTS ====================

    public function test_headers_vo_set_host(): void
    {
        $headers = new HeadersVO;
        $headers->setHost('api.example.com');

        $this->assertTrue($headers->has(HeaderType::HOST));
        $this->assertSame('api.example.com', $headers->get(HeaderType::HOST));
        $this->assertSame(['Host' => 'api.example.com'], $headers->toArray());
    }

    public function test_headers_vo_set_user_agent(): void
    {
        $headers = new HeadersVO;
        $headers->setUserAgent('PHP/8.2');

        $this->assertTrue($headers->has(HeaderType::USER_AGENT));
        $this->assertSame('PHP/8.2', $headers->get(HeaderType::USER_AGENT));
        $this->assertSame(['User-Agent' => 'PHP/8.2'], $headers->toArray());
    }

    public function test_headers_vo_set_accept(): void
    {
        $headers = new HeadersVO;
        $headers->setAccept(ContentType::JSON);

        $this->assertTrue($headers->has(HeaderType::ACCEPT));
        $this->assertSame('application/json', $headers->get(HeaderType::ACCEPT));
        $this->assertSame(['Accept' => 'application/json'], $headers->toArray());
    }

    public function test_headers_vo_set_accept_language(): void
    {
        $headers = new HeadersVO;
        $headers->setAcceptLanguage(AcceptLanguage::FR_FR->value);

        $this->assertTrue($headers->has(HeaderType::ACCEPT_LANGUAGE));
        $this->assertSame('fr-FR', $headers->get(HeaderType::ACCEPT_LANGUAGE));
        $this->assertSame(['Accept-Language' => 'fr-FR'], $headers->toArray());
    }

    public function test_headers_vo_set_accept_encoding(): void
    {
        $headers = new HeadersVO;
        $headers->setAcceptEncoding(ContentEncoding::GZIP);

        $this->assertTrue($headers->has(HeaderType::ACCEPT_ENCODING));
        $this->assertSame('gzip', $headers->get(HeaderType::ACCEPT_ENCODING));
        $this->assertSame(['Accept-Encoding' => 'gzip'], $headers->toArray());
    }

    public function test_headers_vo_set_connection(): void
    {
        $headers = new HeadersVO;
        $headers->setConnection(ConnectionType::KEEP_ALIVE);

        $this->assertTrue($headers->has(HeaderType::CONNECTION));
        $this->assertSame('keep-alive', $headers->get(HeaderType::CONNECTION));
        $this->assertSame(['Connection' => 'keep-alive'], $headers->toArray());
    }

    // ==================== AUTHENTICATION HEADERS TESTS ====================

    public function test_headers_vo_set_authorization(): void
    {
        $headers = new HeadersVO;
        $headers->setAuthorization('eyJhbGciOiJIUzI1NiIs...');

        $this->assertTrue($headers->has(HeaderType::AUTHORIZATION));
        $this->assertSame('Bearer eyJhbGciOiJIUzI1NiIs...', $headers->get(HeaderType::AUTHORIZATION));
        $this->assertSame(['Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIs...'], $headers->toArray());
    }

    public function test_headers_vo_set_basic_auth(): void
    {
        $headers = new HeadersVO;
        $headers->setBasicAuth('username', 'password');

        $this->assertTrue($headers->has(HeaderType::AUTHORIZATION));
        $this->assertSame('Basic '.base64_encode('username:password'), $headers->get(HeaderType::AUTHORIZATION));
        $this->assertSame(['Authorization' => 'Basic '.base64_encode('username:password')], $headers->toArray());
    }

    public function test_headers_vo_set_api_key(): void
    {
        $headers = new HeadersVO;
        $headers->setApiKey('abc123');

        $this->assertTrue($headers->has(HeaderType::X_API_KEY));
        $this->assertSame('abc123', $headers->get(HeaderType::X_API_KEY));
        $this->assertSame(['X-API-Key' => 'abc123'], $headers->toArray());
    }

    public function test_headers_vo_set_cookie(): void
    {
        $headers = new HeadersVO;
        $headers->setCookie('sessionId=xyz123');

        $this->assertTrue($headers->has(HeaderType::COOKIE));
        $this->assertSame('sessionId=xyz123', $headers->get(HeaderType::COOKIE));
        $this->assertSame(['Cookie' => 'sessionId=xyz123'], $headers->toArray());
    }

    // ==================== CONTENT HEADERS TESTS ====================

    public function test_headers_vo_set_content_type(): void
    {
        $headers = new HeadersVO;
        $headers->setContentType(ContentType::JSON);

        $this->assertTrue($headers->has(HeaderType::CONTENT_TYPE));
        $this->assertSame('application/json', $headers->get(HeaderType::CONTENT_TYPE));
        $this->assertSame(['Content-Type' => 'application/json'], $headers->toArray());
    }

    public function test_headers_vo_set_content_type_with_charset(): void
    {
        $headers = new HeadersVO;
        $headers->setContentType(ContentType::JSON_UTF8);

        $this->assertTrue($headers->has(HeaderType::CONTENT_TYPE));
        $this->assertSame('application/json; charset=utf-8', $headers->get(HeaderType::CONTENT_TYPE));
        $this->assertSame(['Content-Type' => 'application/json; charset=utf-8'], $headers->toArray());
    }

    public function test_headers_vo_set_content_length(): void
    {
        $headers = new HeadersVO;
        $headers->setContentLength(1024);

        $this->assertTrue($headers->has(HeaderType::CONTENT_LENGTH));
        $this->assertSame('1024', $headers->get(HeaderType::CONTENT_LENGTH));
        $this->assertSame(['Content-Length' => '1024'], $headers->toArray());
    }

    public function test_headers_vo_set_content_encoding(): void
    {
        $headers = new HeadersVO;
        $headers->setContentEncoding(ContentEncoding::GZIP);

        $this->assertTrue($headers->has(HeaderType::CONTENT_ENCODING));
        $this->assertSame('gzip', $headers->get(HeaderType::CONTENT_ENCODING));
        $this->assertSame(['Content-Encoding' => 'gzip'], $headers->toArray());
    }

    public function test_headers_vo_set_content_language(): void
    {
        $headers = new HeadersVO;
        $headers->setContentLanguage('fr-FR');

        $this->assertTrue($headers->has(HeaderType::CONTENT_LANGUAGE));
        $this->assertSame('fr-FR', $headers->get(HeaderType::CONTENT_LANGUAGE));
        $this->assertSame(['Content-Language' => 'fr-FR'], $headers->toArray());
    }

    // ==================== CACHE HEADERS TESTS ====================

    public function test_headers_vo_set_cache_control(): void
    {
        $headers = new HeadersVO;
        $headers->setCacheControl(CacheControl::NO_CACHE);

        $this->assertTrue($headers->has(HeaderType::CACHE_CONTROL));
        $this->assertSame('no-cache', $headers->get(HeaderType::CACHE_CONTROL));
        $this->assertSame(['Cache-Control' => 'no-cache'], $headers->toArray());
    }

    public function test_headers_vo_set_if_modified_since(): void
    {
        $headers = new HeadersVO;
        $headers->setIfModifiedSince('Mon, 15 Jun 2025 12:00:00 GMT');

        $this->assertTrue($headers->has(HeaderType::IF_MODIFIED_SINCE));
        $this->assertSame('Mon, 15 Jun 2025 12:00:00 GMT', $headers->get(HeaderType::IF_MODIFIED_SINCE));
        $this->assertSame(['If-Modified-Since' => 'Mon, 15 Jun 2025 12:00:00 GMT'], $headers->toArray());
    }

    public function test_headers_vo_set_if_none_match(): void
    {
        $headers = new HeadersVO;
        $headers->setIfNoneMatch('"abc123"');

        $this->assertTrue($headers->has(HeaderType::IF_NONE_MATCH));
        $this->assertSame('"abc123"', $headers->get(HeaderType::IF_NONE_MATCH));
        $this->assertSame(['If-None-Match' => '"abc123"'], $headers->toArray());
    }

    // ==================== REQUEST HEADERS TESTS ====================

    public function test_headers_vo_set_referer(): void
    {
        $headers = new HeadersVO;
        $headers->setReferer('https://example.com');

        $this->assertTrue($headers->has(HeaderType::REFERER));
        $this->assertSame('https://example.com', $headers->get(HeaderType::REFERER));
        $this->assertSame(['Referer' => 'https://example.com'], $headers->toArray());
    }

    public function test_headers_vo_set_origin(): void
    {
        $headers = new HeadersVO;
        $headers->setOrigin('https://example.com');

        $this->assertTrue($headers->has(HeaderType::ORIGIN));
        $this->assertSame('https://example.com', $headers->get(HeaderType::ORIGIN));
        $this->assertSame(['Origin' => 'https://example.com'], $headers->toArray());
    }

    public function test_headers_vo_set_x_requested_with(): void
    {
        $headers = new HeadersVO;
        $headers->setXRequestedWith('XMLHttpRequest');

        $this->assertTrue($headers->has(HeaderType::X_REQUESTED_WITH));
        $this->assertSame('XMLHttpRequest', $headers->get(HeaderType::X_REQUESTED_WITH));
        $this->assertSame(['X-Requested-With' => 'XMLHttpRequest'], $headers->toArray());
    }

    public function test_headers_vo_set_x_forwarded_for(): void
    {
        $headers = new HeadersVO;
        $headers->setXForwardedFor('192.168.1.1');

        $this->assertTrue($headers->has(HeaderType::X_FORWARDED_FOR));
        $this->assertSame('192.168.1.1', $headers->get(HeaderType::X_FORWARDED_FOR));
        $this->assertSame(['X-Forwarded-For' => '192.168.1.1'], $headers->toArray());
    }

    // ==================== CUSTOM HEADERS TESTS ====================

    public function test_headers_vo_set_x_request_id(): void
    {
        $headers = new HeadersVO;
        $headers->setXRequestId('req-123-456');

        $this->assertTrue($headers->has(HeaderType::X_REQUEST_ID));
        $this->assertSame('req-123-456', $headers->get(HeaderType::X_REQUEST_ID));
        $this->assertSame(['X-Request-Id' => 'req-123-456'], $headers->toArray());
    }

    public function test_headers_vo_set_x_correlation_id(): void
    {
        $headers = new HeadersVO;
        $headers->setXCorrelationId('corr-123-456');

        $this->assertTrue($headers->has(HeaderType::X_CORRELATION_ID));
        $this->assertSame('corr-123-456', $headers->get(HeaderType::X_CORRELATION_ID));
        $this->assertSame(['X-Correlation-Id' => 'corr-123-456'], $headers->toArray());
    }

    // ==================== SECURITY HEADERS TESTS ====================

    public function test_headers_vo_set_xsrf_token(): void
    {
        $headers = new HeadersVO;
        $headers->setXsrfToken('token-xyz');

        $this->assertTrue($headers->has(HeaderType::X_CSRF_TOKEN));
        $this->assertSame('token-xyz', $headers->get(HeaderType::X_CSRF_TOKEN));
        $this->assertSame(['X-CSRF-Token' => 'token-xyz'], $headers->toArray());
    }

    public function test_headers_vo_set_strict_transport_security(): void
    {
        $headers = new HeadersVO;
        $headers->setStrictTransportSecurity('max-age=31536000');

        $this->assertTrue($headers->has(HeaderType::STRICT_TRANSPORT_SECURITY));
        $this->assertSame('max-age=31536000', $headers->get(HeaderType::STRICT_TRANSPORT_SECURITY));
        $this->assertSame(['Strict-Transport-Security' => 'max-age=31536000'], $headers->toArray());
    }

    // ==================== CUSTOM GENERIC HEADER TEST ====================

    public function test_headers_vo_set_custom(): void
    {
        $headers = new HeadersVO;
        $headers->setCustom('X-Custom-Header', 'custom-value');

        $this->assertArrayHasKey('X-Custom-Header', $headers->toArray());
        $this->assertSame('custom-value', $headers->toArray()['X-Custom-Header']);
    }

    // ==================== GET VALUE TESTS ====================

    public function test_headers_vo_get_value_returns_strict_data_object(): void
    {
        $headers = new HeadersVO;
        $headers->setHost('api.example.com');
        $headers->setContentType(ContentType::JSON);

        $value = $headers->getValue();

        $this->assertInstanceOf(StrictDataObject::class, $value);
        $this->assertSame('api.example.com', $value->get('Host'));
        $this->assertSame('application/json', $value->get('Content-Type'));
    }

    // ==================== CHAINING TESTS ====================

    public function test_headers_vo_can_chain_setters(): void
    {
        $headers = new HeadersVO;

        $result = $headers
            ->setHost('api.example.com')
            ->setContentType(ContentType::JSON)
            ->setAuthorization('token')
            ->setUserAgent('PHP/8.2');

        $this->assertSame($headers, $result);
        $this->assertSame([
            'Host' => 'api.example.com',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer token',
            'User-Agent' => 'PHP/8.2',
        ], $headers->toArray());
    }

    // ==================== GETTER TESTS ====================

    public function test_headers_vo_get_returns_null_for_non_existent_header(): void
    {
        $headers = new HeadersVO;
        $this->assertNull($headers->get(HeaderType::HOST));
    }

    public function test_headers_vo_has_returns_false_for_non_existent_header(): void
    {
        $headers = new HeadersVO;
        $this->assertFalse($headers->has(HeaderType::HOST));
    }

    // ==================== MULTIPLE HEADERS TESTS ====================

    public function test_headers_vo_can_store_multiple_headers(): void
    {
        $headers = new HeadersVO;
        $headers
            ->setHost('api.example.com')
            ->setUserAgent('PHP/8.2')
            ->setAccept(ContentType::JSON)
            ->setAuthorization('token')
            ->setContentType(ContentType::JSON);

        $this->assertSame([
            'Host' => 'api.example.com',
            'User-Agent' => 'PHP/8.2',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer token',
            'Content-Type' => 'application/json',
        ], $headers->toArray());
    }

    // ==================== OVERWRITE HEADERS TESTS ====================

    public function test_headers_vo_overwrites_existing_headers(): void
    {
        $headers = new HeadersVO;
        // Utiliser FORM au lieu de TEXT car TEXT n'existe plus
        $headers->setContentType(ContentType::FORM);
        $this->assertSame('application/x-www-form-urlencoded', $headers->get(HeaderType::CONTENT_TYPE));

        $headers->setContentType(ContentType::JSON);
        $this->assertSame('application/json', $headers->get(HeaderType::CONTENT_TYPE));
        $this->assertCount(1, $headers->toArray());
    }
    // ==================== ACCEPT LANGUAGE WITH QUALITY TEST ====================

    public function test_headers_vo_accept_language_with_quality(): void
    {
        $headers = new HeadersVO;
        $headers->setAcceptLanguage(AcceptLanguage::FR_FR->withQuality(0.9));

        $this->assertTrue($headers->has(HeaderType::ACCEPT_LANGUAGE));
        $this->assertSame('fr-FR;q=0.9', $headers->get(HeaderType::ACCEPT_LANGUAGE));
    }
}
