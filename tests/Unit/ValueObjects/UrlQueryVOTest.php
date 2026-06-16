<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\ValueObjects;

use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;
use stdClass;

final class UrlQueryVOTest extends TestCase
{
    // ==================== CONSTRUCTION TESTS ====================

    public function test_url_query_vo_can_be_created_with_query_string(): void
    {
        $query = new UrlQueryVO('page=1&limit=10&sort=asc');

        $this->assertInstanceOf(UrlQueryVO::class, $query);
        $this->assertSame('page=1&limit=10&sort=asc', $query->getValue());
        $this->assertSame('page=1&limit=10&sort=asc', $query->toString());
    }

    public function test_url_query_vo_can_be_created_empty(): void
    {
        $query = new UrlQueryVO;

        $this->assertInstanceOf(UrlQueryVO::class, $query);
        $this->assertSame('', $query->getValue());
        $this->assertSame('', $query->toString());
        $this->assertTrue($query->isEmpty());
    }

    public function test_url_query_vo_can_be_created_with_empty_string(): void
    {
        $query = new UrlQueryVO('');

        $this->assertInstanceOf(UrlQueryVO::class, $query);
        $this->assertSame('', $query->getValue());
        $this->assertTrue($query->isEmpty());
    }

    // ==================== GETTER TESTS ====================

    public function test_url_query_vo_get_parameters_returns_array(): void
    {
        $query = new UrlQueryVO('page=1&limit=10&sort=asc');
        $parameters = $query->getParameters();

        $this->assertIsArray($parameters);
        $this->assertArrayHasKey('page', $parameters);
        $this->assertArrayHasKey('limit', $parameters);
        $this->assertArrayHasKey('sort', $parameters);
        $this->assertSame('1', $parameters['page']);
        $this->assertSame('10', $parameters['limit']);
        $this->assertSame('asc', $parameters['sort']);
    }

    public function test_url_query_vo_get_returns_correct_value(): void
    {
        $query = new UrlQueryVO('page=1&limit=10&sort=asc');

        $this->assertSame('1', $query->get('page'));
        $this->assertSame('10', $query->get('limit'));
        $this->assertSame('asc', $query->get('sort'));
        $this->assertNull($query->get('non_existent'));
    }

    public function test_url_query_vo_has_returns_correct_value(): void
    {
        $query = new UrlQueryVO('page=1&limit=10');

        $this->assertTrue($query->has('page'));
        $this->assertTrue($query->has('limit'));
        $this->assertFalse($query->has('sort'));
        $this->assertFalse($query->has('non_existent'));
    }

    public function test_url_query_vo_is_empty_returns_correct_value(): void
    {
        $query1 = new UrlQueryVO('page=1');
        $this->assertFalse($query1->isEmpty());

        $query2 = new UrlQueryVO('');
        $this->assertTrue($query2->isEmpty());

        $query3 = new UrlQueryVO;
        $this->assertTrue($query3->isEmpty());
    }

    public function test_url_query_vo_to_string_returns_query_string(): void
    {
        $query = new UrlQueryVO('page=1&limit=10');
        $this->assertSame('page=1&limit=10', $query->toString());
        $this->assertSame('page=1&limit=10', (string) $query);
    }

    // ==================== WITH PARAMETER TESTS ====================

    public function test_url_query_vo_with_parameter_returns_new_instance(): void
    {
        $query = new UrlQueryVO('page=1');
        $newQuery = $query->withParameter('limit', '10');

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1', $query->toString());
        $this->assertSame('page=1&limit=10', $newQuery->toString());
        $this->assertSame('10', $newQuery->get('limit'));
    }

    public function test_url_query_vo_with_parameter_overwrites_existing(): void
    {
        $query = new UrlQueryVO('page=1&limit=5');
        $newQuery = $query->withParameter('limit', '10');

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1&limit=5', $query->toString());
        $this->assertSame('page=1&limit=10', $newQuery->toString());
        $this->assertSame('10', $newQuery->get('limit'));
    }

    public function test_url_query_vo_with_parameter_with_array_value(): void
    {
        $query = new UrlQueryVO('page=1');
        $newQuery = $query->withParameter('values', ['1', '2', '3']);

        $this->assertStringContainsString('values%5B0%5D=1', $newQuery->toString());
        $this->assertStringContainsString('values%5B1%5D=2', $newQuery->toString());
        $this->assertStringContainsString('values%5B2%5D=3', $newQuery->toString());
    }

    public function test_url_query_vo_with_parameter_with_null_value(): void
    {
        $query = new UrlQueryVO('page=1');
        $newQuery = $query->withParameter('limit', null);

        // http_build_query avec PHP_QUERY_RFC3986 convertit null en chaîne vide
        $this->assertSame('page=1&limit=', $newQuery->toString());
        $this->assertSame('', $newQuery->get('limit'));
    }

    // ==================== WITHOUT PARAMETER TESTS ====================

    public function test_url_query_vo_without_parameter_returns_new_instance(): void
    {
        $query = new UrlQueryVO('page=1&limit=10&sort=asc');
        $newQuery = $query->withoutParameter('limit');

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1&limit=10&sort=asc', $query->toString());
        $this->assertSame('page=1&sort=asc', $newQuery->toString());
        $this->assertFalse($newQuery->has('limit'));
    }

    public function test_url_query_vo_without_parameter_removes_last_parameter(): void
    {
        $query = new UrlQueryVO('page=1');
        $newQuery = $query->withoutParameter('page');

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1', $query->toString());
        $this->assertSame('', $newQuery->toString());
        $this->assertTrue($newQuery->isEmpty());
    }

    public function test_url_query_vo_without_parameter_ignores_non_existent(): void
    {
        $query = new UrlQueryVO('page=1');
        $newQuery = $query->withoutParameter('non_existent');

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1', $query->toString());
        $this->assertSame('page=1', $newQuery->toString());
    }

    // ==================== MERGE TESTS ====================

    public function test_url_query_vo_merge_returns_new_instance(): void
    {
        $query = new UrlQueryVO('page=1&limit=10');
        $newQuery = $query->merge(['sort' => 'asc', 'filter' => 'active']);

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1&limit=10', $query->toString());
        $this->assertStringContainsString('page=1', $newQuery->toString());
        $this->assertStringContainsString('limit=10', $newQuery->toString());
        $this->assertStringContainsString('sort=asc', $newQuery->toString());
        $this->assertStringContainsString('filter=active', $newQuery->toString());
    }

    public function test_url_query_vo_merge_overwrites_existing(): void
    {
        $query = new UrlQueryVO('page=1&limit=10');
        $newQuery = $query->merge(['page' => '2', 'sort' => 'asc']);

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1&limit=10', $query->toString());
        $this->assertStringContainsString('page=2', $newQuery->toString());
        $this->assertStringContainsString('limit=10', $newQuery->toString());
        $this->assertStringContainsString('sort=asc', $newQuery->toString());
        $this->assertSame('2', $newQuery->get('page'));
    }

    public function test_url_query_vo_merge_with_empty_array(): void
    {
        $query = new UrlQueryVO('page=1&limit=10');
        $newQuery = $query->merge([]);

        $this->assertNotSame($query, $newQuery);
        $this->assertSame('page=1&limit=10', $query->toString());
        $this->assertSame('page=1&limit=10', $newQuery->toString());
    }

    // ==================== IMMUTABILITY TESTS ====================

    public function test_url_query_vo_is_immutable(): void
    {
        $query = new UrlQueryVO('page=1&limit=10');
        $originalValue = $query->getValue();

        $query->withParameter('sort', 'asc');
        $query->withoutParameter('page');
        $query->merge(['filter' => 'active']);

        $this->assertSame($originalValue, $query->getValue());
        $this->assertSame('page=1&limit=10', $query->toString());
    }

    // ==================== EQUALS TESTS ====================

    public function test_url_query_vo_equals_returns_true_for_identical_queries(): void
    {
        $query1 = new UrlQueryVO('page=1&limit=10');
        $query2 = new UrlQueryVO('page=1&limit=10');

        $this->assertTrue($query1->equals($query2));
        $this->assertTrue($query2->equals($query1));
    }

    public function test_url_query_vo_equals_returns_false_for_different_queries(): void
    {
        $query1 = new UrlQueryVO('page=1&limit=10');
        $query2 = new UrlQueryVO('page=2&limit=10');

        $this->assertFalse($query1->equals($query2));
        $this->assertFalse($query2->equals($query1));
    }

    public function test_url_query_vo_equals_returns_true_for_same_parameters_different_order(): void
    {
        $query1 = new UrlQueryVO('page=1&limit=10');
        $query2 = new UrlQueryVO('limit=10&page=1');

        // parse_str() donne le même tableau, donc equals() retourne true
        $this->assertTrue($query1->equals($query2));
    }

    // ==================== EDGE CASES TESTS ====================

    public function test_url_query_vo_with_special_characters(): void
    {
        $test = new stdClass;
        $test->name = 'andy';

        $query = new UrlQueryVO('name=John%20Doe&city=Paris');
        $this->assertSame('John Doe', $query->get('name'));
        $this->assertSame('Paris', $query->get('city'));
    }

    public function test_url_query_vo_with_array_syntax(): void
    {
        $query = new UrlQueryVO('values[]=1&values[]=2&values[]=3');
        $values = $query->get('values');

        $this->assertIsArray($values);
        $this->assertSame(['1', '2', '3'], $values);
    }

    public function test_url_query_vo_with_empty_value(): void
    {
        $query = new UrlQueryVO('key1=&key2=value');
        $this->assertSame('', $query->get('key1'));
        $this->assertSame('value', $query->get('key2'));
    }

    public function test_url_query_vo_with_multiple_values_same_key_without_brackets(): void
    {
        // Sans [], la dernière valeur écrase les précédentes
        $query = new UrlQueryVO('color=red&color=blue&color=green');
        $color = $query->get('color');

        $this->assertIsString($color);
        $this->assertSame('green', $color);
    }

    public function test_url_query_vo_with_multiple_values_same_key_with_brackets(): void
    {
        // Avec [], on obtient un tableau
        $query = new UrlQueryVO('color[]=red&color[]=blue&color[]=green');
        $colors = $query->get('color');

        $this->assertIsArray($colors);
        $this->assertSame(['red', 'blue', 'green'], $colors);
    }

    public function test_url_query_vo_with_plus_sign(): void
    {
        $query = new UrlQueryVO('q=hello+world');
        $this->assertSame('hello world', $query->get('q'));
    }

    // ==================== CHAINING TESTS ====================

    public function test_url_query_vo_can_chain_modifications(): void
    {
        $query = (new UrlQueryVO('page=1'))
            ->withParameter('limit', '10')
            ->withParameter('sort', 'asc')
            ->withoutParameter('page');

        $this->assertSame('limit=10&sort=asc', $query->toString());
        $this->assertFalse($query->has('page'));
        $this->assertTrue($query->has('limit'));
        $this->assertTrue($query->has('sort'));
        $this->assertSame('10', $query->get('limit'));
        $this->assertSame('asc', $query->get('sort'));
    }

    public function test_url_query_vo_merge_after_modifications(): void
    {
        $query = (new UrlQueryVO('page=1'))
            ->withParameter('limit', '10')
            ->merge(['sort' => 'asc', 'filter' => 'active']);

        $this->assertStringContainsString('page=1', $query->toString());
        $this->assertStringContainsString('limit=10', $query->toString());
        $this->assertStringContainsString('sort=asc', $query->toString());
        $this->assertStringContainsString('filter=active', $query->toString());
    }
}
