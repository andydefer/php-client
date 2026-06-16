<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\ValueObjects;

use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use InvalidArgumentException;

final class UrlVOTest extends TestCase
{
    // ==================== CONSTRUCTION TESTS ====================

    public function test_url_vo_can_be_created_with_full_url(): void
    {
        $url = new UrlVO('https://api.example.com:443/v2/deposits?page=1&limit=10#section');

        $this->assertInstanceOf(UrlVO::class, $url);
        $this->assertSame('https://api.example.com:443/v2/deposits?page=1&limit=10#section', $url->getValue());
    }

    public function test_url_vo_can_be_created_without_port(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');

        $this->assertSame('https', $url->getScheme());
        $this->assertSame('api.example.com', $url->getHost());
        $this->assertNull($url->getPort());
        $this->assertSame('/v2/deposits', $url->getPath());
    }

    public function test_url_vo_can_be_created_without_path(): void
    {
        $url = new UrlVO('https://api.example.com');

        $this->assertSame('/', $url->getPath());
    }

    public function test_url_vo_can_be_created_without_scheme(): void
    {
        $url = new UrlVO('api.example.com/v2/deposits');

        $this->assertSame('https', $url->getScheme());
        $this->assertSame('api.example.com', $url->getHost());
        $this->assertSame('/v2/deposits', $url->getPath());
    }

    public function test_url_vo_throws_exception_for_invalid_url(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URL: invalid url with spaces');

        new UrlVO('invalid url with spaces');
    }
    // ==================== GETTER TESTS ====================

    public function test_url_vo_get_scheme_returns_correct_value(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertSame('https', $url->getScheme());

        $url2 = new UrlVO('http://api.example.com/v2/deposits');
        $this->assertSame('http', $url2->getScheme());
    }

    public function test_url_vo_get_host_returns_correct_value(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertSame('api.example.com', $url->getHost());
    }

    public function test_url_vo_get_port_returns_correct_value(): void
    {
        $url = new UrlVO('https://api.example.com:8080/v2/deposits');
        $this->assertSame(8080, $url->getPort());

        $url2 = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertNull($url2->getPort());
    }

    public function test_url_vo_get_path_returns_correct_value(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertSame('/v2/deposits', $url->getPath());

        $url2 = new UrlVO('https://api.example.com');
        $this->assertSame('/', $url2->getPath());
    }

    public function test_url_vo_get_query_returns_url_query_vo(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits?page=1&limit=10');
        $query = $url->getQuery();

        $this->assertInstanceOf(UrlQueryVO::class, $query);
        $this->assertSame('page=1&limit=10', $query->toString());
        $this->assertSame('1', $query->get('page'));
        $this->assertSame('10', $query->get('limit'));
    }

    public function test_url_vo_get_query_returns_empty_url_query_vo_when_no_query(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $query = $url->getQuery();

        $this->assertInstanceOf(UrlQueryVO::class, $query);
        $this->assertTrue($query->isEmpty());
        $this->assertSame('', $query->toString());
    }

    public function test_url_vo_get_fragment_returns_correct_value(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits#section');
        $this->assertSame('section', $url->getFragment());

        $url2 = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertNull($url2->getFragment());
    }

    // ==================== GET FULL PATH TESTS ====================

    public function test_url_vo_get_full_path_returns_correct_value(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits?page=1&limit=10#section');
        $this->assertSame('/v2/deposits?page=1&limit=10#section', $url->getFullPath());
    }

    public function test_url_vo_get_full_path_without_query(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits#section');
        $this->assertSame('/v2/deposits#section', $url->getFullPath());
    }

    public function test_url_vo_get_full_path_without_fragment(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits?page=1');
        $this->assertSame('/v2/deposits?page=1', $url->getFullPath());
    }

    public function test_url_vo_get_full_path_without_query_and_fragment(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertSame('/v2/deposits', $url->getFullPath());
    }

    // ==================== GET BASE URL TESTS ====================

    public function test_url_vo_get_base_url_returns_correct_value(): void
    {
        $url = new UrlVO('https://api.example.com:8080/v2/deposits?page=1');
        $this->assertSame('https://api.example.com:8080', $url->getBaseUrl());
    }

    public function test_url_vo_get_base_url_without_port(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertSame('https://api.example.com', $url->getBaseUrl());
    }

    // ==================== WITH PATH TESTS ====================

    public function test_url_vo_with_path_returns_new_instance(): void
    {
        $url = new UrlVO('https://api.example.com');
        $newUrl = $url->withPath('/v2/deposits');

        $this->assertNotSame($url, $newUrl);
        $this->assertSame('/v2/deposits', $newUrl->getPath());
        $this->assertSame('https://api.example.com/v2/deposits', $newUrl->getValue());
    }

    public function test_url_vo_with_path_preserves_query_and_fragment(): void
    {
        $url = new UrlVO('https://api.example.com?page=1#section');
        $newUrl = $url->withPath('/v2/deposits');

        $this->assertSame('https://api.example.com/v2/deposits?page=1#section', $newUrl->getValue());
    }

    // ==================== WITH QUERY TESTS ====================

    public function test_url_vo_with_query_returns_new_instance(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $query = new UrlQueryVO('page=1&limit=10');
        $newUrl = $url->withQuery($query);

        $this->assertNotSame($url, $newUrl);
        $this->assertSame('page=1&limit=10', $newUrl->getQuery()->toString());
        $this->assertSame('https://api.example.com/v2/deposits?page=1&limit=10', $newUrl->getValue());
    }

    public function test_url_vo_with_query_overwrites_existing_query(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits?old=value');
        $query = new UrlQueryVO('page=1&limit=10');
        $newUrl = $url->withQuery($query);

        $this->assertSame('page=1&limit=10', $newUrl->getQuery()->toString());
        $this->assertSame('https://api.example.com/v2/deposits?page=1&limit=10', $newUrl->getValue());
    }

    public function test_url_vo_with_query_empty_removes_query(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits?page=1');
        $query = new UrlQueryVO('');
        $newUrl = $url->withQuery($query);

        $this->assertSame('https://api.example.com/v2/deposits', $newUrl->getValue());
        $this->assertTrue($newUrl->getQuery()->isEmpty());
    }

    // ==================== WITH FRAGMENT TESTS ====================

    public function test_url_vo_with_fragment_returns_new_instance(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $newUrl = $url->withFragment('section');

        $this->assertNotSame($url, $newUrl);
        $this->assertSame('section', $newUrl->getFragment());
        $this->assertSame('https://api.example.com/v2/deposits#section', $newUrl->getValue());
    }

    public function test_url_vo_with_fragment_overwrites_existing_fragment(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits#old');
        $newUrl = $url->withFragment('new');

        $this->assertSame('new', $newUrl->getFragment());
        $this->assertSame('https://api.example.com/v2/deposits#new', $newUrl->getValue());
    }

    public function test_url_vo_with_fragment_null_removes_fragment(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits#section');
        $newUrl = $url->withFragment(null);

        $this->assertNull($newUrl->getFragment());
        $this->assertSame('https://api.example.com/v2/deposits', $newUrl->getValue());
    }

    // ==================== IMMUTABILITY TESTS ====================

    public function test_url_vo_is_immutable(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $originalValue = $url->getValue();

        $url->withPath('/v3/');
        $url->withQuery(new UrlQueryVO('page=1'));
        $url->withFragment('section');

        $this->assertSame($originalValue, $url->getValue());
    }

    // ==================== STRING REPRESENTATION TESTS ====================

    public function test_url_vo_to_string_returns_url_string(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits');
        $this->assertSame('https://api.example.com/v2/deposits', (string) $url);
    }

    // ==================== EQUALS METHOD TESTS ====================

    public function test_url_vo_equals_returns_true_for_identical_urls(): void
    {
        $url1 = new UrlVO('https://api.example.com/v2/deposits');
        $url2 = new UrlVO('https://api.example.com/v2/deposits');

        $this->assertTrue($url1->equals($url2));
        $this->assertTrue($url2->equals($url1));
    }

    public function test_url_vo_equals_returns_false_for_different_urls(): void
    {
        $url1 = new UrlVO('https://api.example.com/v2/deposits');
        $url2 = new UrlVO('https://api.example.com/v3/deposits');

        $this->assertFalse($url1->equals($url2));
        $this->assertFalse($url2->equals($url1));
    }

    // ==================== EDGE CASES TESTS ====================

    public function test_url_vo_with_encoded_unicode_characters(): void
    {
        // Avec des caractères encodés, c'est valide
        $url = new UrlVO('https://api.example.com/v2/d%C3%A9p%C3%B4ts');
        $this->assertSame('https://api.example.com/v2/d%C3%A9p%C3%B4ts', $url->getValue());
    }

    public function test_url_vo_with_special_characters_in_query(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits?name=John%20Doe&city=Paris');
        $query = $url->getQuery();

        $this->assertSame('John Doe', $query->get('name'));
        $this->assertSame('Paris', $query->get('city'));
    }

    public function test_url_vo_with_array_in_query(): void
    {
        $url = new UrlVO('https://api.example.com/v2/deposits?values[]=1&values[]=2&values[]=3');
        $query = $url->getQuery();

        $values = $query->get('values');
        $this->assertIsArray($values);
        $this->assertSame(['1', '2', '3'], $values);
    }

    // ==================== CHAINING TESTS ====================

    public function test_url_vo_can_chain_modifications(): void
    {
        $url = (new UrlVO('https://api.example.com'))
            ->withPath('/v2/deposits')
            ->withQuery(new UrlQueryVO('page=1&limit=10'))
            ->withFragment('section');

        $this->assertSame('https://api.example.com/v2/deposits?page=1&limit=10#section', $url->getValue());
    }
}
