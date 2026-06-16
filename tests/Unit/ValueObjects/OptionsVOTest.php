<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\ValueObjects;

use AndyDefer\DomainStructures\Utils\StrictDataObject;
use AndyDefer\PhpClient\Enums\OptionType;
use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\OptionsVO;

final class OptionsVOTest extends TestCase
{
    // ==================== CONSTRUCTION TESTS ====================

    public function test_options_vo_can_be_created(): void
    {
        $options = new OptionsVO;

        $this->assertInstanceOf(OptionsVO::class, $options);
        $this->assertEmpty($options->toArray());
    }

    // ==================== SETTER TESTS ====================

    public function test_options_vo_set_timeout(): void
    {
        $options = new OptionsVO;
        $options->setTimeout(30);

        $this->assertTrue($options->has(OptionType::TIMEOUT));
        $this->assertSame(30, $options->get(OptionType::TIMEOUT));
        $this->assertSame(['timeout' => 30], $options->toArray());
    }

    public function test_options_vo_set_connect_timeout(): void
    {
        $options = new OptionsVO;
        $options->setConnectTimeout(10);

        $this->assertTrue($options->has(OptionType::CONNECT_TIMEOUT));
        $this->assertSame(10, $options->get(OptionType::CONNECT_TIMEOUT));
        $this->assertSame(['connect_timeout' => 10], $options->toArray());
    }

    public function test_options_vo_set_verify_with_bool(): void
    {
        $options = new OptionsVO;
        $options->setVerify(true);

        $this->assertTrue($options->has(OptionType::VERIFY));
        $this->assertTrue($options->get(OptionType::VERIFY));
        $this->assertSame(['verify' => true], $options->toArray());
    }

    public function test_options_vo_set_verify_with_string(): void
    {
        $options = new OptionsVO;
        $options->setVerify('/path/to/cert.pem');

        $this->assertTrue($options->has(OptionType::VERIFY));
        $this->assertSame('/path/to/cert.pem', $options->get(OptionType::VERIFY));
        $this->assertSame(['verify' => '/path/to/cert.pem'], $options->toArray());
    }

    public function test_options_vo_set_debug(): void
    {
        $options = new OptionsVO;
        $options->setDebug(true);

        $this->assertTrue($options->has(OptionType::DEBUG));
        $this->assertTrue($options->get(OptionType::DEBUG));
        $this->assertSame(['debug' => true], $options->toArray());
    }

    public function test_options_vo_set_http_errors(): void
    {
        $options = new OptionsVO;
        $options->setHttpErrors(false);

        $this->assertTrue($options->has(OptionType::HTTP_ERRORS));
        $this->assertFalse($options->get(OptionType::HTTP_ERRORS));
        $this->assertSame(['http_errors' => false], $options->toArray());
    }

    public function test_options_vo_set_allow_redirects_with_bool(): void
    {
        $options = new OptionsVO;
        $options->setAllowRedirects(false);

        $this->assertTrue($options->has(OptionType::ALLOW_REDIRECTS));
        $this->assertFalse($options->get(OptionType::ALLOW_REDIRECTS));
        $this->assertSame(['allow_redirects' => false], $options->toArray());
    }

    public function test_options_vo_set_allow_redirects_with_array(): void
    {
        $options = new OptionsVO;
        $options->setAllowRedirects(['max' => 5, 'strict' => true]);

        $this->assertTrue($options->has(OptionType::ALLOW_REDIRECTS));
        $this->assertSame(['max' => 5, 'strict' => true], $options->get(OptionType::ALLOW_REDIRECTS));
        $this->assertSame(['allow_redirects' => ['max' => 5, 'strict' => true]], $options->toArray());
    }

    public function test_options_vo_set_max_redirects(): void
    {
        $options = new OptionsVO;
        $options->setMaxRedirects(5);

        $this->assertTrue($options->has(OptionType::MAX_REDIRECTS));
        $this->assertSame(5, $options->get(OptionType::MAX_REDIRECTS));
        $this->assertSame(['max_redirects' => 5], $options->toArray());
    }

    public function test_options_vo_set_cookies_with_bool(): void
    {
        $options = new OptionsVO;
        $options->setCookies(true);

        $this->assertTrue($options->has(OptionType::COOKIES));
        $this->assertTrue($options->get(OptionType::COOKIES));
        $this->assertSame(['cookies' => true], $options->toArray());
    }

    public function test_options_vo_set_cookies_with_array(): void
    {
        $options = new OptionsVO;
        $options->setCookies(['session' => 'abc123']);

        $this->assertTrue($options->has(OptionType::COOKIES));
        $this->assertSame(['session' => 'abc123'], $options->get(OptionType::COOKIES));
        $this->assertSame(['cookies' => ['session' => 'abc123']], $options->toArray());
    }

    public function test_options_vo_set_idn_conversion(): void
    {
        $options = new OptionsVO;
        $options->setIdnConversion(true);

        $this->assertTrue($options->has(OptionType::IDN_CONVERSION));
        $this->assertTrue($options->get(OptionType::IDN_CONVERSION));
        $this->assertSame(['idn_conversion' => true], $options->toArray());
    }

    // ==================== TRANSFER OPTIONS TESTS ====================

    public function test_options_vo_set_body(): void
    {
        $options = new OptionsVO;
        $options->setBody('Hello World');

        $this->assertTrue($options->has(OptionType::BODY));
        $this->assertSame('Hello World', $options->get(OptionType::BODY));
        $this->assertSame(['body' => 'Hello World'], $options->toArray());
    }

    public function test_options_vo_set_json(): void
    {
        $options = new OptionsVO;
        $options->setJson(['key' => 'value']);

        $this->assertTrue($options->has(OptionType::JSON));
        $this->assertSame(['key' => 'value'], $options->get(OptionType::JSON));
        $this->assertSame(['json' => ['key' => 'value']], $options->toArray());
    }

    public function test_options_vo_set_multipart(): void
    {
        $options = new OptionsVO;
        $multipart = [
            ['name' => 'field', 'contents' => 'value'],
            ['name' => 'file', 'contents' => 'binary_data'],
        ];
        $options->setMultipart($multipart);

        $this->assertTrue($options->has(OptionType::MULTIPART));
        $this->assertSame($multipart, $options->get(OptionType::MULTIPART));
        $this->assertSame(['multipart' => $multipart], $options->toArray());
    }

    public function test_options_vo_set_form_params(): void
    {
        $options = new OptionsVO;
        $options->setFormParams(['name' => 'John', 'age' => '30']);

        $this->assertTrue($options->has(OptionType::FORM_PARAMS));
        $this->assertSame(['name' => 'John', 'age' => '30'], $options->get(OptionType::FORM_PARAMS));
        $this->assertSame(['form_params' => ['name' => 'John', 'age' => '30']], $options->toArray());
    }

    public function test_options_vo_set_stream(): void
    {
        $options = new OptionsVO;
        $options->setStream(true);

        $this->assertTrue($options->has(OptionType::STREAM));
        $this->assertTrue($options->get(OptionType::STREAM));
        $this->assertSame(['stream' => true], $options->toArray());
    }

    public function test_options_vo_set_sink(): void
    {
        $options = new OptionsVO;
        $options->setSink('/path/to/file');

        $this->assertTrue($options->has(OptionType::SINK));
        $this->assertSame('/path/to/file', $options->get(OptionType::SINK));
        $this->assertSame(['sink' => '/path/to/file'], $options->toArray());
    }

    public function test_options_vo_set_read_timeout(): void
    {
        $options = new OptionsVO;
        $options->setReadTimeout(60);

        $this->assertTrue($options->has(OptionType::READ_TIMEOUT));
        $this->assertSame(60, $options->get(OptionType::READ_TIMEOUT));
        $this->assertSame(['read_timeout' => 60], $options->toArray());
    }

    // ==================== PROXY OPTIONS TESTS ====================

    public function test_options_vo_set_proxy_with_string(): void
    {
        $options = new OptionsVO;
        $options->setProxy('tcp://localhost:8080');

        $this->assertTrue($options->has(OptionType::PROXY));
        $this->assertSame('tcp://localhost:8080', $options->get(OptionType::PROXY));
        $this->assertSame(['proxy' => 'tcp://localhost:8080'], $options->toArray());
    }

    public function test_options_vo_set_proxy_with_array(): void
    {
        $options = new OptionsVO;
        $options->setProxy(['http' => 'tcp://localhost:8080', 'https' => 'tcp://localhost:8443']);

        $this->assertTrue($options->has(OptionType::PROXY));
        $this->assertSame(['http' => 'tcp://localhost:8080', 'https' => 'tcp://localhost:8443'], $options->get(OptionType::PROXY));
        $this->assertSame(['proxy' => ['http' => 'tcp://localhost:8080', 'https' => 'tcp://localhost:8443']], $options->toArray());
    }

    public function test_options_vo_set_no_proxy(): void
    {
        $options = new OptionsVO;
        $options->setNoProxy(['.example.com', '.test.com']);

        $this->assertTrue($options->has(OptionType::NO_PROXY));
        $this->assertSame(['.example.com', '.test.com'], $options->get(OptionType::NO_PROXY));
        $this->assertSame(['no_proxy' => ['.example.com', '.test.com']], $options->toArray());
    }

    // ==================== AUTH OPTIONS TESTS ====================

    public function test_options_vo_set_auth(): void
    {
        $options = new OptionsVO;
        $options->setAuth(['username', 'password']);

        $this->assertTrue($options->has(OptionType::AUTH));
        $this->assertSame(['username', 'password'], $options->get(OptionType::AUTH));
        $this->assertSame(['auth' => ['username', 'password']], $options->toArray());
    }

    public function test_options_vo_set_auth_with_digest(): void
    {
        $options = new OptionsVO;
        $options->setAuth(['username', 'password', 'digest']);

        $this->assertTrue($options->has(OptionType::AUTH));
        $this->assertSame(['username', 'password', 'digest'], $options->get(OptionType::AUTH));
        $this->assertSame(['auth' => ['username', 'password', 'digest']], $options->toArray());
    }

    public function test_options_vo_set_cert_with_string(): void
    {
        $options = new OptionsVO;
        $options->setCert('/path/to/cert.pem');

        $this->assertTrue($options->has(OptionType::CERT));
        $this->assertSame('/path/to/cert.pem', $options->get(OptionType::CERT));
        $this->assertSame(['cert' => '/path/to/cert.pem'], $options->toArray());
    }

    public function test_options_vo_set_cert_with_array(): void
    {
        $options = new OptionsVO;
        $options->setCert(['/path/to/cert.pem', 'password']);

        $this->assertTrue($options->has(OptionType::CERT));
        $this->assertSame(['/path/to/cert.pem', 'password'], $options->get(OptionType::CERT));
        $this->assertSame(['cert' => ['/path/to/cert.pem', 'password']], $options->toArray());
    }

    public function test_options_vo_set_ssl_key_with_string(): void
    {
        $options = new OptionsVO;
        $options->setSslKey('/path/to/key.pem');

        $this->assertTrue($options->has(OptionType::SSL_KEY));
        $this->assertSame('/path/to/key.pem', $options->get(OptionType::SSL_KEY));
        $this->assertSame(['ssl_key' => '/path/to/key.pem'], $options->toArray());
    }

    public function test_options_vo_set_ssl_key_with_array(): void
    {
        $options = new OptionsVO;
        $options->setSslKey(['/path/to/key.pem', 'password']);

        $this->assertTrue($options->has(OptionType::SSL_KEY));
        $this->assertSame(['/path/to/key.pem', 'password'], $options->get(OptionType::SSL_KEY));
        $this->assertSame(['ssl_key' => ['/path/to/key.pem', 'password']], $options->toArray());
    }

    // ==================== VERSION OPTIONS TESTS ====================

    public function test_options_vo_set_version(): void
    {
        $options = new OptionsVO;
        $options->setVersion('2.0');

        $this->assertTrue($options->has(OptionType::VERSION));
        $this->assertSame('2.0', $options->get(OptionType::VERSION));
        $this->assertSame(['version' => '2.0'], $options->toArray());
    }

    // ==================== ENVIRONMENT OPTIONS TESTS ====================

    public function test_options_vo_set_base_uri(): void
    {
        $options = new OptionsVO;
        $options->setBaseUri('https://api.example.com');

        $this->assertTrue($options->has(OptionType::BASE_URI));
        $this->assertSame('https://api.example.com', $options->get(OptionType::BASE_URI));
        $this->assertSame(['base_uri' => 'https://api.example.com'], $options->toArray());
    }

    public function test_options_vo_set_headers(): void
    {
        $options = new OptionsVO;
        $options->setHeaders(['Content-Type' => 'application/json', 'Authorization' => 'Bearer token']);

        $this->assertTrue($options->has(OptionType::HEADERS));
        $this->assertSame(['Content-Type' => 'application/json', 'Authorization' => 'Bearer token'], $options->get(OptionType::HEADERS));
        $this->assertSame(['headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer token']], $options->toArray());
    }

    public function test_options_vo_set_query(): void
    {
        $options = new OptionsVO;
        $options->setQuery(['page' => 1, 'limit' => 10]);

        $this->assertTrue($options->has(OptionType::QUERY));
        $this->assertSame(['page' => 1, 'limit' => 10], $options->get(OptionType::QUERY));
        $this->assertSame(['query' => ['page' => 1, 'limit' => 10]], $options->toArray());
    }

    public function test_options_vo_set_decode_content(): void
    {
        $options = new OptionsVO;
        $options->setDecodeContent(true);

        $this->assertTrue($options->has(OptionType::DECODE_CONTENT));
        $this->assertTrue($options->get(OptionType::DECODE_CONTENT));
        $this->assertSame(['decode_content' => true], $options->toArray());
    }

    public function test_options_vo_set_force_ip_resolve(): void
    {
        $options = new OptionsVO;
        $options->setForceIpResolve('v4');

        $this->assertTrue($options->has(OptionType::FORCE_IP_RESOLVE));
        $this->assertSame('v4', $options->get(OptionType::FORCE_IP_RESOLVE));
        $this->assertSame(['force_ip_resolve' => 'v4'], $options->toArray());
    }

    // ==================== LOGGING OPTIONS TESTS ====================

    public function test_options_vo_set_on_stats(): void
    {
        $options = new OptionsVO;
        $callback = function () {};
        $options->setOnStats($callback);

        $this->assertTrue($options->has(OptionType::ON_STATS));
        $this->assertSame($callback, $options->get(OptionType::ON_STATS));
    }

    // ==================== CUSTOM OPTIONS TESTS ====================

    public function test_options_vo_set_custom(): void
    {
        $options = new OptionsVO;
        $options->setCustom('custom_key', 'custom_value');

        // Vérifier que la clé personnalisée existe
        $this->assertArrayHasKey('custom_key', $options->toArray());
        $this->assertSame('custom_value', $options->toArray()['custom_key']);
    }

    // ==================== GET VALUE TESTS ====================

    public function test_options_vo_get_value_returns_strict_data_object(): void
    {
        $options = new OptionsVO;
        $options->setTimeout(30);
        $options->setConnectTimeout(10);

        $value = $options->getValue();

        $this->assertInstanceOf(StrictDataObject::class, $value);
        $this->assertSame(30, $value->get('timeout'));
        $this->assertSame(10, $value->get('connect_timeout'));
    }

    // ==================== IMMUTABILITY TESTS ====================

    public function test_options_vo_is_mutable(): void
    {
        $options = new OptionsVO;
        $options->setTimeout(30);

        $this->assertSame(['timeout' => 30], $options->toArray());

        // Les setters modifient l'instance (ils retournent $this)
        $options->setConnectTimeout(10);

        $this->assertSame(['timeout' => 30, 'connect_timeout' => 10], $options->toArray());
    }

    // ==================== CHAINING TESTS ====================

    public function test_options_vo_can_chain_setters(): void
    {
        $options = new OptionsVO;

        $result = $options
            ->setTimeout(30)
            ->setConnectTimeout(10)
            ->setHttpErrors(true)
            ->setVersion('1.1');

        $this->assertSame($options, $result);
        $this->assertSame([
            'timeout' => 30,
            'connect_timeout' => 10,
            'http_errors' => true,
            'version' => '1.1',
        ], $options->toArray());
    }

    // ==================== GETTER TESTS ====================

    public function test_options_vo_get_returns_null_for_non_existent_option(): void
    {
        $options = new OptionsVO;
        $this->assertNull($options->get(OptionType::TIMEOUT));
    }

    public function test_options_vo_has_returns_false_for_non_existent_option(): void
    {
        $options = new OptionsVO;
        $this->assertFalse($options->has(OptionType::TIMEOUT));
    }
}
