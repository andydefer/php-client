<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\ValueObjects;

use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\Encoding;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonDetailStruct;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonListStruct;
use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;
use InvalidArgumentException;

final class ResponseBodyVOTest extends TestCase
{
    // ==================== CONSTRUCTION TESTS ====================

    public function test_response_body_vo_can_be_created_with_pokemon_list_json(): void
    {
        $json = '{
            "count": 2,
            "next": "https://pokeapi.co/api/v2/pokemon?offset=2",
            "previous": null,
            "results": [
                {"name": "bulbasaur", "url": "https://pokeapi.co/api/v2/pokemon/1/"},
                {"name": "ivysaur", "url": "https://pokeapi.co/api/v2/pokemon/2/"}
            ]
        }';

        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertInstanceOf(ResponseBodyVO::class, $body);
        $this->assertSame($json, $body->getContent());
        $this->assertSame(ContentType::JSON, $body->getContentType());
        $this->assertSame(Encoding::UTF_8, $body->getEncoding());
        $this->assertInstanceOf(PokemonListStruct::class, $body->getValue());
        $this->assertTrue($body->isValidJson());
        $this->assertFalse($body->isEmpty());
        $this->assertFalse($body->isProblemJson());

        $struct = $body->getValue();
        $this->assertSame(2, $struct->count);
        $this->assertSame('https://pokeapi.co/api/v2/pokemon?offset=2', $struct->next);
        $this->assertNull($struct->previous);
        $this->assertCount(2, $struct->results);
        $this->assertInstanceOf(PokemonGraph::class, $struct->results[0]);
        $this->assertSame('bulbasaur', $struct->results[0]->name);
    }

    public function test_response_body_vo_can_be_created_with_pokemon_detail_json(): void
    {
        $json = '{
            "data": {
                "id": "pikachu-001",
                "name": "Pikachu",
                "height": 4,
                "weight": 60,
                "types": [
                    {"name": "electric", "url": "https://pokeapi.co/api/v2/type/13/"}
                ],
                "abilities": [],
                "stats": []
            }
        }';

        $body = new ResponseBodyVO($json, PokemonDetailStruct::class);

        $this->assertInstanceOf(ResponseBodyVO::class, $body);
        $this->assertInstanceOf(PokemonDetailStruct::class, $body->getValue());
        $this->assertTrue($body->isValidJson());

        $struct = $body->getValue();
        $this->assertSame('Pikachu', $struct->data->name->getValue());
        $this->assertSame(4.0, $struct->data->height->getValue());
        $this->assertSame(60.0, $struct->data->weight->getValue());
        $this->assertCount(1, $struct->data->types);
    }

    public function test_response_body_vo_throws_exception_for_invalid_json(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON');

        new ResponseBodyVO('{invalid json}', PokemonListStruct::class);
    }

    // ==================== GETTER TESTS ====================

    public function test_response_body_vo_get_content_returns_original_content(): void
    {
        $json = '{"count":1,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertSame($json, $body->getContent());
    }

    public function test_response_body_vo_get_content_as_string_returns_string(): void
    {
        $json = '{"count":1,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertSame($json, $body->getContentAsString());
    }

    public function test_response_body_vo_get_content_type_returns_content_type(): void
    {
        $json = '{"count":1,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class, ContentType::PROBLEM_JSON);

        $this->assertSame(ContentType::PROBLEM_JSON, $body->getContentType());
    }

    public function test_response_body_vo_get_encoding_returns_encoding(): void
    {
        $json = '{"count":1,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class, ContentType::JSON, Encoding::UTF_8);

        $this->assertSame(Encoding::UTF_8, $body->getEncoding());
    }

    // ==================== VALIDATION TESTS ====================

    public function test_response_body_vo_is_valid_json_returns_true_for_valid_json(): void
    {
        $json = '{"count":1,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertTrue($body->isValidJson());
    }

    public function test_response_body_vo_is_valid_json_returns_false_for_form_content(): void
    {
        $body = new ResponseBodyVO(
            'count=1',
            PokemonListStruct::class,
            ContentType::FORM
        );

        $this->assertFalse($body->isValidJson());
    }

    public function test_response_body_vo_is_empty_returns_true_for_null_string(): void
    {
        $body = new ResponseBodyVO('null', PokemonListStruct::class);
        $this->assertTrue($body->isEmpty());
    }

    public function test_response_body_vo_is_empty_returns_true_for_empty_array_string(): void
    {
        $body = new ResponseBodyVO('[]', PokemonListStruct::class);
        $this->assertTrue($body->isEmpty());
    }

    public function test_response_body_vo_is_empty_returns_false_for_non_empty_content(): void
    {
        $json = '{"count":1,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertFalse($body->isEmpty());
    }

    public function test_response_body_vo_is_problem_json_returns_true_for_problem_json(): void
    {
        $json = '{"type":"https://example.com/errors","title":"Error"}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class, ContentType::PROBLEM_JSON);

        $this->assertTrue($body->isProblemJson());
    }

    public function test_response_body_vo_is_problem_json_returns_false_for_json(): void
    {
        $json = '{"count":1,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertFalse($body->isProblemJson());
    }

    // ==================== FORMAT TESTS ====================

    public function test_response_body_vo_format_handles_empty_content(): void
    {
        $body = new ResponseBodyVO('[]', PokemonListStruct::class);

        $formatted = $body->format();

        $this->assertIsArray($formatted);
        $this->assertEmpty($formatted);
    }

    // ==================== GET VALUE WITH STRUCT ====================

    public function test_response_body_vo_get_value_returns_pokemon_list_struct(): void
    {
        $json = '{"count":2,"next":null,"previous":null,"results":[{"name":"bulbasaur","url":"..."}]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $struct = $body->getValue();

        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertSame(2, $struct->count);
        $this->assertCount(1, $struct->results);
        $this->assertSame('bulbasaur', $struct->results[0]->name);
    }

    public function test_response_body_vo_get_value_returns_pokemon_detail_struct(): void
    {
        $json = '{
            "data": {
                "id": "pikachu-001",
                "name": "Pikachu",
                "height": 4,
                "weight": 60,
                "types": [],
                "abilities": [],
                "stats": []
            }
        }';

        $body = new ResponseBodyVO($json, PokemonDetailStruct::class);

        $struct = $body->getValue();

        $this->assertInstanceOf(PokemonDetailStruct::class, $struct);
        $this->assertSame('Pikachu', $struct->data->name->getValue());
        $this->assertSame(4.0, $struct->data->height->getValue());
        $this->assertSame(60.0, $struct->data->weight->getValue());
    }

    // ==================== EDGE CASES ====================

    public function test_response_body_vo_with_empty_json_object(): void
    {
        $json = '{"count":0,"next":null,"previous":null,"results":[]}';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertFalse($body->isEmpty());
        $this->assertTrue($body->isValidJson());
        $this->assertInstanceOf(PokemonListStruct::class, $body->getValue());

        $struct = $body->getValue();
        $this->assertSame(0, $struct->count);
        $this->assertNull($struct->next);
        $this->assertNull($struct->previous);
        $this->assertCount(0, $struct->results);
    }

    public function test_response_body_vo_with_whitespace_json(): void
    {
        $json = '  {"count": 1, "next": null, "previous": null, "results": []}  ';
        $body = new ResponseBodyVO($json, PokemonListStruct::class);

        $this->assertTrue($body->isValidJson());
        $this->assertInstanceOf(PokemonListStruct::class, $body->getValue());
        $this->assertSame(1, $body->getValue()->count);
    }

    public function test_response_body_vo_hydration_fails_with_invalid_data(): void
    {
        $body = new ResponseBodyVO('{}', PokemonListStruct::class);

        $this->assertTrue($body->isValidJson());
        $this->assertFalse($body->isEmpty());
        $this->assertNull($body->getValue());
    }
}
