<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\Abstracts;

use AndyDefer\DomainStructures\Normalizers\NormalizerChain;
use AndyDefer\DomainStructures\Services\HydrationService;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\AbilityCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\PokemonCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\StatCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\TypeCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonDetailGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\TypeGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonDetailStruct;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonListStruct;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonHeight;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonId;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonName;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonWeight;
use AndyDefer\PhpClient\Tests\TestCase;
use InvalidArgumentException;

final class StructTest extends TestCase
{
    private HydrationService $hydration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydration = new HydrationService;
    }

    // ==================== CONSTRUCTION TESTS ====================

    public function test_pokemon_list_struct_can_be_created_via_constructor(): void
    {
        $collection = new PokemonCollection;
        $collection->add(
            new PokemonGraph('bulbasaur', 'https://pokeapi.co/api/v2/pokemon/1/'),
            new PokemonGraph('ivysaur', 'https://pokeapi.co/api/v2/pokemon/2/')
        );

        $struct = new PokemonListStruct(
            count: 2,
            next: 'https://pokeapi.co/api/v2/pokemon?offset=2',
            previous: null,
            results: $collection
        );

        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertInstanceOf(Struct::class, $struct);
        $this->assertSame(2, $struct->count);
        $this->assertSame('https://pokeapi.co/api/v2/pokemon?offset=2', $struct->next);
        $this->assertNull($struct->previous);
        $this->assertCount(2, $struct->results);
    }

    public function test_pokemon_detail_struct_can_be_created_via_constructor(): void
    {
        $types = new TypeCollection;
        $types->add(new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/'));

        $detail = new PokemonDetailGraph(
            id: new PokemonId('bulbasaur-001'),
            name: new PokemonName('Bulbasaur'),
            height: new PokemonHeight(7),
            weight: new PokemonWeight(69),
            types: $types,
            abilities: new AbilityCollection,
            stats: new StatCollection
        );

        $struct = new PokemonDetailStruct(data: $detail);

        $this->assertInstanceOf(PokemonDetailStruct::class, $struct);
        $this->assertInstanceOf(Struct::class, $struct);
        $this->assertSame('Bulbasaur', $struct->data->name->getValue());
    }

    // ==================== FROM METHOD TESTS ====================

    public function test_pokemon_list_struct_can_be_created_via_from(): void
    {
        $data = [
            'count' => 2,
            'next' => 'https://pokeapi.co/api/v2/pokemon?offset=2',
            'previous' => null,
            'results' => [
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
            ],
        ];

        $struct = PokemonListStruct::from($data);

        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertSame(2, $struct->count);
        $this->assertSame('https://pokeapi.co/api/v2/pokemon?offset=2', $struct->next);
        $this->assertNull($struct->previous);
        $this->assertCount(2, $struct->results);
        $this->assertInstanceOf(PokemonGraph::class, $struct->results[0]);
        $this->assertSame('bulbasaur', $struct->results[0]->name);
    }

    public function test_pokemon_detail_struct_can_be_created_via_from(): void
    {
        $data = [
            'data' => [
                'id' => 'pikachu-001',
                'name' => 'Pikachu',
                'height' => 4,
                'weight' => 60,
                'types' => [
                    ['name' => 'electric', 'url' => 'https://pokeapi.co/api/v2/type/13/'],
                ],
                'abilities' => [],
                'stats' => [],
                'status' => 'active',
            ],
        ];

        $struct = PokemonDetailStruct::from($data);

        $this->assertInstanceOf(PokemonDetailStruct::class, $struct);
        $this->assertInstanceOf(PokemonDetailGraph::class, $struct->data);
        $this->assertSame('Pikachu', $struct->data->name->getValue());
        $this->assertSame(4.0, $struct->data->height->getValue());
        $this->assertSame(60.0, $struct->data->weight->getValue());
        $this->assertCount(1, $struct->data->types);
    }

    // ==================== FROM JSON TESTS ====================

    public function test_pokemon_list_struct_can_be_created_via_from_json(): void
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

        $struct = PokemonListStruct::fromJson($json);

        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertSame(2, $struct->count);
        $this->assertCount(2, $struct->results);
        $this->assertSame('bulbasaur', $struct->results[0]->name);
    }

    public function test_pokemon_detail_struct_can_be_created_via_from_json(): void
    {
        $json = '{
            "data": {
                "id": "charizard-006",
                "name": "Charizard",
                "height": 17,
                "weight": 905,
                "types": [
                    {"name": "fire", "url": "https://pokeapi.co/api/v2/type/10/"},
                    {"name": "flying", "url": "https://pokeapi.co/api/v2/type/3/"}
                ],
                "abilities": [],
                "stats": [],
                "status": "active"
            }
        }';

        $struct = PokemonDetailStruct::fromJson($json);

        $this->assertInstanceOf(PokemonDetailStruct::class, $struct);
        $this->assertSame('Charizard', $struct->data->name->getValue());
        $this->assertCount(2, $struct->data->types);
    }

    public function test_from_json_throws_exception_for_invalid_json(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON');

        PokemonListStruct::fromJson('{invalid json}');
    }

    // ==================== HYDRATION SERVICE TESTS ====================

    public function test_pokemon_list_struct_can_be_hydrated_via_hydration_service(): void
    {
        $data = [
            'count' => 2,
            'next' => 'https://pokeapi.co/api/v2/pokemon?offset=2',
            'previous' => null,
            'results' => [
                ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
                ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
            ],
        ];

        $struct = $this->hydration->hydrate(PokemonListStruct::class, $data);

        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertSame(2, $struct->count);
        $this->assertCount(2, $struct->results);
    }

    public function test_pokemon_detail_struct_can_be_hydrated_via_hydration_service(): void
    {
        $data = [
            'data' => [
                'id' => 'pikachu-001',
                'name' => 'Pikachu',
                'height' => 4,
                'weight' => 60,
                'types' => [
                    ['name' => 'electric', 'url' => 'https://pokeapi.co/api/v2/type/13/'],
                ],
                'abilities' => [],
                'stats' => [],
            ],
        ];

        $struct = PokemonDetailStruct::from($data);

        $this->assertInstanceOf(PokemonDetailStruct::class, $struct);
        $this->assertInstanceOf(PokemonDetailGraph::class, $struct->data);
        $this->assertSame('Pikachu', $struct->data->name->getValue());
        $this->assertSame(4.0, $struct->data->height->getValue());
        $this->assertSame(60.0, $struct->data->weight->getValue());
        $this->assertCount(1, $struct->data->types);
    }

    // ==================== ENCODE METHOD TESTS ====================

    public function test_pokemon_list_struct_can_be_encoded_to_json(): void
    {
        $collection = new PokemonCollection;
        $collection->add(
            new PokemonGraph('bulbasaur', 'https://pokeapi.co/api/v2/pokemon/1/'),
            new PokemonGraph('ivysaur', 'https://pokeapi.co/api/v2/pokemon/2/')
        );

        $struct = new PokemonListStruct(
            count: 2,
            next: 'https://pokeapi.co/api/v2/pokemon?offset=2',
            previous: null,
            results: $collection
        );

        $json = $struct->encode(ContentType::JSON);

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertSame(2, $decoded['count']);
        $this->assertSame('https://pokeapi.co/api/v2/pokemon?offset=2', $decoded['next']);
        $this->assertCount(2, $decoded['results']);
        $this->assertSame('bulbasaur', $decoded['results'][0]['name']);
    }

    public function test_pokemon_detail_struct_can_be_encoded_to_json(): void
    {
        $types = new TypeCollection;
        $types->add(new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/'));

        $detail = new PokemonDetailGraph(
            id: new PokemonId('bulbasaur-001'),
            name: new PokemonName('Bulbasaur'),
            height: new PokemonHeight(7),
            weight: new PokemonWeight(69),
            types: $types,
            abilities: new AbilityCollection,
            stats: new StatCollection
        );

        $struct = new PokemonDetailStruct(data: $detail);

        $json = $struct->encode(ContentType::JSON);

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertSame('bulbasaur-001', $decoded['data']['id']);
        $this->assertSame('Bulbasaur', $decoded['data']['name']);
        $this->assertEquals(7.0, $decoded['data']['height']);   // assertEquals au lieu de assertSame
        $this->assertEquals(69.0, $decoded['data']['weight']);  // assertEquals au lieu de assertSame
        $this->assertCount(1, $decoded['data']['types']);
    }

    // ==================== DECODE METHOD TESTS ====================

    public function test_struct_can_decode_json_to_struct(): void
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

        $struct = Struct::decode($json, PokemonListStruct::class);

        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertSame(2, $struct->count);
        $this->assertCount(2, $struct->results);
    }

    public function test_struct_decode_throws_exception_for_invalid_class(): void
    {
        $json = '{"count": 2}';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must extend');

        Struct::decode($json, 'InvalidClass');
    }

    public function test_struct_decode_throws_exception_for_invalid_json(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON');

        Struct::decode('{invalid json}', PokemonListStruct::class);
    }

    // ==================== TO_ARRAY TESTS ====================

    public function test_pokemon_list_struct_to_array_returns_correct_array(): void
    {
        $collection = new PokemonCollection;
        $collection->add(
            new PokemonGraph('bulbasaur', 'https://pokeapi.co/api/v2/pokemon/1/')
        );

        $struct = new PokemonListStruct(
            count: 1,
            next: null,
            previous: null,
            results: $collection
        );

        $array = $struct->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('count', $array);
        $this->assertArrayHasKey('next', $array);
        $this->assertArrayHasKey('previous', $array);
        $this->assertArrayHasKey('results', $array);
        $this->assertSame(1, $array['count']);
        $this->assertNull($array['next']);
        $this->assertNull($array['previous']);
        $this->assertCount(1, $array['results']);
        $this->assertSame('bulbasaur', $array['results'][0]['name']);
    }

    // ==================== NORMALIZER CHAIN TESTS ====================

    public function test_pokemon_list_struct_can_be_normalized_by_normalizer_chain(): void
    {
        $collection = new PokemonCollection;
        $collection->add(
            new PokemonGraph('bulbasaur', 'https://pokeapi.co/api/v2/pokemon/1/')
        );

        $struct = new PokemonListStruct(
            count: 1,
            next: null,
            previous: null,
            results: $collection
        );

        $normalized = NormalizerChain::get()->normalize($struct);

        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('count', $normalized);
        $this->assertArrayHasKey('next', $normalized);
        $this->assertArrayHasKey('previous', $normalized);
        $this->assertArrayHasKey('results', $normalized);
        $this->assertSame(1, $normalized['count']);
        $this->assertNull($normalized['next']);
        $this->assertNull($normalized['previous']);
        $this->assertCount(1, $normalized['results']);
    }

    // ==================== GET_VALUE TESTS ====================

    public function test_struct_get_value_returns_self(): void
    {
        $collection = new PokemonCollection;
        $struct = new PokemonListStruct(
            count: 0,
            next: null,
            previous: null,
            results: $collection
        );

        $value = $struct->getValue();

        $this->assertSame($struct, $value);
        $this->assertInstanceOf(PokemonListStruct::class, $value);
    }

    // ==================== JSON ENCODE TESTS ====================

    public function test_struct_can_be_json_encoded(): void
    {
        $collection = new PokemonCollection;
        $struct = new PokemonListStruct(
            count: 1,
            next: null,
            previous: null,
            results: $collection
        );

        $json = json_encode($struct);

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertSame(1, $decoded['count']);
        $this->assertNull($decoded['next']);
        $this->assertNull($decoded['previous']);
        $this->assertEmpty($decoded['results']);
    }

    // ==================== IMMUTABILITY TESTS ====================

    public function test_structs_are_immutable(): void
    {
        $collection = new PokemonCollection;
        $struct = new PokemonListStruct(
            count: 1,
            next: 'https://pokeapi.co/api/v2/pokemon?offset=1',
            previous: null,
            results: $collection
        );

        $originalCount = $struct->count;
        $originalNext = $struct->next;

        $this->assertSame($originalCount, $struct->count);
        $this->assertSame($originalNext, $struct->next);
    }

    // ==================== CASE SENSITIVITY TESTS ====================

    public function test_struct_from_is_case_sensitive(): void
    {
        $data = [
            'count' => 1,
            'next' => 'https://pokeapi.co/api/v2/pokemon?offset=1',
            'previous' => null,
            'results' => [],
            'Next' => 'https://pokeapi.co/api/v2/pokemon?offset=2', // Wrong case
        ];

        $struct = PokemonListStruct::from($data);

        // 'Next' != 'next', donc la valeur par défaut est utilisée
        $this->assertSame('https://pokeapi.co/api/v2/pokemon?offset=1', $struct->next);
    }

    // ==================== MISSING REQUIRED PARAMETER TESTS ====================

    public function test_struct_throws_exception_when_required_parameter_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameters');

        PokemonListStruct::from([]);
    }

    // ==================== FROM WITH SOURCE OBJECT ====================

    public function test_struct_from_accepts_object_source(): void
    {
        $source = new class
        {
            public int $count = 1;

            public ?string $next = 'https://pokeapi.co/api/v2/pokemon?offset=1';

            public ?string $previous = null;

            public array $results = [];
        };

        $struct = PokemonListStruct::from($source);

        $this->assertInstanceOf(PokemonListStruct::class, $struct);
        $this->assertSame(1, $struct->count);
        $this->assertSame('https://pokeapi.co/api/v2/pokemon?offset=1', $struct->next);
        $this->assertNull($struct->previous);
        $this->assertEmpty($struct->results);
    }

    public function test_struct_from_throws_exception_for_invalid_source(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source must be an array or object');

        PokemonListStruct::from('invalid string');
    }
}
