<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\ValueObjects;

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use InvalidArgumentException;

final class RequestBodyVOTest extends TestCase
{
    // ==================== HELPER ====================

    private function createTestStruct(): Struct
    {
        return new class extends Struct
        {
            public function __construct(
                public readonly string $name = 'John',
                public readonly int $age = 30,
                public readonly array $tags = ['php', 'test'],
            ) {}
        };
    }

    // ==================== CONSTRUCTION TESTS ====================

    public function test_request_body_vo_can_be_created_with_default_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct);

        $this->assertInstanceOf(RequestBodyVO::class, $body);
        $this->assertSame($struct, $body->getStruct());
        $this->assertSame(ContentType::JSON, $body->getContentType());
        $this->assertTrue($body->isJson());
        $this->assertFalse($body->isForm());
    }

    public function test_request_body_vo_can_be_created_with_json_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::JSON);

        $this->assertInstanceOf(RequestBodyVO::class, $body);
        $this->assertSame(ContentType::JSON, $body->getContentType());
        $this->assertTrue($body->isJson());
        $this->assertFalse($body->isForm());
    }

    public function test_request_body_vo_can_be_created_with_form_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::FORM);

        $this->assertInstanceOf(RequestBodyVO::class, $body);
        $this->assertSame(ContentType::FORM, $body->getContentType());
        $this->assertFalse($body->isJson());
        $this->assertTrue($body->isForm());
    }

    public function test_request_body_vo_can_be_created_with_problem_json_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::PROBLEM_JSON);

        $this->assertInstanceOf(RequestBodyVO::class, $body);
        $this->assertSame(ContentType::PROBLEM_JSON, $body->getContentType());
        $this->assertTrue($body->isJson());
        $this->assertFalse($body->isForm());
    }

    // ==================== GETTER TESTS ====================

    public function test_request_body_vo_get_struct_returns_struct(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct);

        $this->assertSame($struct, $body->getStruct());
    }

    public function test_request_body_vo_get_content_type_returns_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::FORM);

        $this->assertSame(ContentType::FORM, $body->getContentType());
    }

    // ==================== TO STRING TESTS ====================

    public function test_request_body_vo_to_string_returns_json_for_json_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::JSON);

        $expected = '{"name":"John","age":30,"tags":["php","test"]}';
        $this->assertSame($expected, $body->toString());
        $this->assertSame($expected, (string) $body);
    }

    public function test_request_body_vo_to_string_returns_form_for_form_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::FORM);

        $expected = 'name=John&age=30&tags%5B0%5D=php&tags%5B1%5D=test';
        $this->assertSame($expected, $body->toString());
        $this->assertSame($expected, (string) $body);
    }

    public function test_request_body_vo_to_string_returns_json_for_problem_json_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::PROBLEM_JSON);

        $expected = '{"name":"John","age":30,"tags":["php","test"]}';
        $this->assertSame($expected, $body->toString());
    }

    // ==================== TO ARRAY TESTS ====================

    public function test_request_body_vo_to_array_returns_struct_array(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct);

        $expected = ['name' => 'John', 'age' => 30, 'tags' => ['php', 'test']];
        $this->assertSame($expected, $body->toArray());
    }

    // ==================== TO JSON TESTS ====================

    public function test_request_body_vo_to_json_returns_json_for_json_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::JSON);

        $expected = '{"name":"John","age":30,"tags":["php","test"]}';
        $this->assertSame($expected, $body->toJson());
    }

    public function test_request_body_vo_to_json_returns_json_for_problem_json_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::PROBLEM_JSON);

        $expected = '{"name":"John","age":30,"tags":["php","test"]}';
        $this->assertSame($expected, $body->toJson());
    }

    public function test_request_body_vo_to_json_throws_exception_for_form_content_type(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::FORM);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot convert non-JSON content to JSON');

        $body->toJson();
    }

    // ==================== IS EMPTY TESTS ====================

    public function test_request_body_vo_is_empty_returns_false_for_non_empty_struct(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct);

        $this->assertFalse($body->isEmpty());
    }

    public function test_request_body_vo_is_empty_returns_true_for_empty_struct(): void
    {
        $struct = new class extends Struct
        {
            public function __construct() {}
        };

        $body = new RequestBodyVO($struct);

        $this->assertTrue($body->isEmpty());
    }

    // ==================== GET VALUE TESTS ====================

    public function test_request_body_vo_get_value_returns_struct(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct);

        $this->assertSame($struct, $body->getValue());
    }

    // ==================== WITH STRUCT TESTS ====================

    public function test_request_body_vo_with_struct_returns_new_instance(): void
    {
        $struct1 = $this->createTestStruct();
        $struct2 = new class extends Struct
        {
            public function __construct(
                public readonly string $key = 'value',
            ) {}
        };

        $body = new RequestBodyVO($struct1);
        $newBody = $body->withStruct($struct2);

        $this->assertNotSame($body, $newBody);
        $this->assertSame($struct1, $body->getStruct());
        $this->assertSame($struct2, $newBody->getStruct());
        $this->assertSame(ContentType::JSON, $newBody->getContentType());
    }

    // ==================== WITH CONTENT TYPE TESTS ====================

    public function test_request_body_vo_with_content_type_returns_new_instance(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct, ContentType::JSON);
        $newBody = $body->withContentType(ContentType::FORM);

        $this->assertNotSame($body, $newBody);
        $this->assertSame(ContentType::JSON, $body->getContentType());
        $this->assertSame(ContentType::FORM, $newBody->getContentType());
        $this->assertSame($struct, $newBody->getStruct());
    }

    // ==================== IMMUTABILITY TESTS ====================

    public function test_request_body_vo_is_immutable(): void
    {
        $struct = $this->createTestStruct();
        $body = new RequestBodyVO($struct);

        $originalStruct = $body->getStruct();
        $originalContentType = $body->getContentType();

        $body->withStruct(new class extends Struct {});
        $body->withContentType(ContentType::FORM);

        $this->assertSame($originalStruct, $body->getStruct());
        $this->assertSame($originalContentType, $body->getContentType());
    }

    // ==================== EDGE CASES TESTS ====================

    public function test_request_body_vo_with_empty_struct_and_form_content_type(): void
    {
        $struct = new class extends Struct
        {
            public function __construct() {}
        };

        $body = new RequestBodyVO($struct, ContentType::FORM);

        $this->assertTrue($body->isEmpty());
        $this->assertSame('', $body->toString());
        $this->assertSame([], $body->toArray());
    }

    public function test_request_body_vo_with_nested_struct(): void
    {
        $nested = new class extends Struct
        {
            public function __construct(
                public readonly string $nested_key = 'nested_value',
            ) {}
        };

        $struct = new class($nested) extends Struct
        {
            public function __construct(
                public readonly object $nested,
            ) {}
        };

        $body = new RequestBodyVO($struct, ContentType::JSON);

        $this->assertStringContainsString('"nested_key":"nested_value"', $body->toString());
        $this->assertIsArray($body->toArray());
        $this->assertArrayHasKey('nested', $body->toArray());
    }

    // ==================== CHAINING TESTS ====================

    public function test_request_body_vo_can_chain_modifications(): void
    {
        $struct1 = $this->createTestStruct();
        $struct2 = new class extends Struct
        {
            public function __construct(
                public readonly string $data = 'test',
            ) {}
        };

        $body = (new RequestBodyVO($struct1))
            ->withContentType(ContentType::FORM)
            ->withStruct($struct2);

        $this->assertSame($struct2, $body->getStruct());
        $this->assertSame(ContentType::FORM, $body->getContentType());
        $this->assertTrue($body->isForm());
        $this->assertFalse($body->isJson());
    }
}
