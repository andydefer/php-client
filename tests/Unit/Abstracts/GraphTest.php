<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\Abstracts;

use AndyDefer\DomainStructures\Normalizers\NormalizerChain;
use AndyDefer\DomainStructures\Services\HydrationService;
use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpClient\Tests\Fixtures\Enums\TestStatus;
use AndyDefer\PhpClient\Tests\Fixtures\Graphs\TestGraphWithDefaults;
use AndyDefer\PhpClient\Tests\Fixtures\Graphs\TestGraphWithEnum;
use AndyDefer\PhpClient\Tests\Fixtures\Graphs\TestGraphWithNullable;
use AndyDefer\PhpClient\Tests\Fixtures\Graphs\TestGraphWithRequiredParams;
use AndyDefer\PhpClient\Tests\Fixtures\Graphs\TestGraphWithZeroParams;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\AbilityCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\StatCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\TypeCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Enums\PokemonStatus;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\AbilityGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonDetailGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\StatGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\TypeGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonHeight;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonId;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonName;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\ValueObjects\PokemonWeight;
use AndyDefer\PhpClient\Tests\TestCase;
use InvalidArgumentException;

final class GraphTest extends TestCase
{
    private HydrationService $hydration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hydration = new HydrationService;
    }

    // ==================== CONSTRUCTION TESTS ====================

    public function test_type_graph_can_be_created_via_constructor(): void
    {
        $type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');

        $this->assertInstanceOf(TypeGraph::class, $type);
        $this->assertInstanceOf(Graph::class, $type);
        $this->assertSame('grass', $type->name);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $type->url);
    }

    public function test_pokemon_graph_can_be_created_via_constructor(): void
    {
        $pokemon = new PokemonGraph('bulbasaur', 'https://pokeapi.co/api/v2/pokemon/1/');

        $this->assertInstanceOf(PokemonGraph::class, $pokemon);
        $this->assertInstanceOf(Graph::class, $pokemon);
        $this->assertSame('bulbasaur', $pokemon->name);
        $this->assertSame('https://pokeapi.co/api/v2/pokemon/1/', $pokemon->url);
    }

    public function test_pokemon_detail_graph_can_be_created_via_constructor(): void
    {
        $types = new TypeCollection;
        $types->add(new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/'));

        $abilities = new AbilityCollection;
        $abilities->add(new AbilityGraph('overgrow', 'https://pokeapi.co/api/v2/ability/65/'));

        $stats = new StatCollection;
        $stats->add(new StatGraph('hp', 45));

        $pokemonDetail = new PokemonDetailGraph(
            id: new PokemonId('bulbasaur-001'),
            name: new PokemonName('Bulbasaur'),
            height: new PokemonHeight(7),
            weight: new PokemonWeight(69),
            types: $types,
            abilities: $abilities,
            stats: $stats
        );

        $this->assertInstanceOf(PokemonDetailGraph::class, $pokemonDetail);
        $this->assertInstanceOf(Graph::class, $pokemonDetail);
        $this->assertSame('bulbasaur-001', $pokemonDetail->id->getValue());
        $this->assertSame('Bulbasaur', $pokemonDetail->name->getValue());
        $this->assertCount(1, $pokemonDetail->types);
        $this->assertCount(1, $pokemonDetail->abilities);
        $this->assertCount(1, $pokemonDetail->stats);
    }

    // ==================== FROM METHOD TESTS ====================

    public function test_type_graph_can_be_created_via_from(): void
    {
        $type = TypeGraph::from([
            'name' => 'grass',
            'url' => 'https://pokeapi.co/api/v2/type/12/',
        ]);

        $this->assertInstanceOf(TypeGraph::class, $type);
        $this->assertSame('grass', $type->name);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $type->url);
    }

    public function test_pokemon_graph_can_be_created_via_from(): void
    {
        $pokemon = PokemonGraph::from([
            'name' => 'bulbasaur',
            'url' => 'https://pokeapi.co/api/v2/pokemon/1/',
        ]);

        $this->assertInstanceOf(PokemonGraph::class, $pokemon);
        $this->assertSame('bulbasaur', $pokemon->name);
        $this->assertSame('https://pokeapi.co/api/v2/pokemon/1/', $pokemon->url);
    }

    public function test_pokemon_detail_graph_can_be_created_via_from_with_value_objects(): void
    {
        $data = [
            'id' => 'pikachu-001',
            'name' => 'Pikachu',
            'height' => 4,
            'weight' => 60,
            'types' => [
                ['name' => 'electric', 'url' => 'https://pokeapi.co/api/v2/type/13/'],
            ],
            'abilities' => [
                ['name' => 'static', 'url' => 'https://pokeapi.co/api/v2/ability/9/'],
            ],
            'stats' => [
                ['name' => 'hp', 'base_stat' => 35],
                ['name' => 'attack', 'base_stat' => 55],
            ],
            'status' => 'active',
            'description' => 'A cute electric mouse',
        ];

        $pokemon = PokemonDetailGraph::from($data);

        $this->assertInstanceOf(PokemonDetailGraph::class, $pokemon);
        $this->assertInstanceOf(PokemonId::class, $pokemon->id);
        $this->assertSame('pikachu-001', $pokemon->id->getValue());
        $this->assertInstanceOf(PokemonName::class, $pokemon->name);
        $this->assertSame('Pikachu', $pokemon->name->getValue());
        $this->assertInstanceOf(PokemonHeight::class, $pokemon->height);
        $this->assertSame(4.0, $pokemon->height->getValue());
        $this->assertInstanceOf(PokemonWeight::class, $pokemon->weight);
        $this->assertSame(60.0, $pokemon->weight->getValue());
        $this->assertSame(PokemonStatus::ACTIVE, $pokemon->status);
        $this->assertSame('A cute electric mouse', $pokemon->description);
    }

    // ==================== GRAPH WITH ZERO PARAMETER ====================

    public function test_graph_with_zero_parameter_can_be_created(): void
    {
        $instance = TestGraphWithZeroParams::from([]);
        $this->assertInstanceOf(TestGraphWithZeroParams::class, $instance);
        $this->assertInstanceOf(Graph::class, $instance);
    }

    // ==================== GRAPH WITH DEFAULT VALUES ====================

    public function test_graph_with_default_values_uses_default_when_missing(): void
    {
        $instance = TestGraphWithDefaults::from([
            'name' => 'Hello',
        ]);
        $this->assertSame('Hello', $instance->name);
        $this->assertSame('default', $instance->full_name);
    }

    // ==================== CASE SENSITIVE HYDRATION ====================

    public function test_graph_from_is_case_sensitive(): void
    {
        // camelCase → ignoré
        $instance = TestGraphWithDefaults::from([
            'name' => 'Hello',
            'fullName' => 'Benji',
        ]);
        $this->assertSame('default', $instance->full_name);

        // snake_case → pris en compte
        $instance2 = TestGraphWithDefaults::from([
            'name' => 'Hello',
            'full_name' => 'Benji',
        ]);
        $this->assertSame('Benji', $instance2->full_name);
    }

    // ==================== MISSING REQUIRED PARAMETER ====================

    public function test_graph_throws_exception_when_required_parameter_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required parameters');

        TestGraphWithRequiredParams::from([]);
    }

    // ==================== GRAPH WITH NULLABLE PARAMETER ====================

    public function test_graph_with_nullable_parameter_accepts_null(): void
    {
        $instance = TestGraphWithNullable::from(['description' => null]);
        $this->assertNull($instance->description);

        $instance2 = TestGraphWithNullable::from([]);
        $this->assertNull($instance2->description);
    }

    // ==================== GRAPH WITH ENUM PARAMETER ====================

    public function test_graph_with_enum_parameter_can_be_hydrated(): void
    {
        $instance = TestGraphWithEnum::from(['status' => 'active']);
        $this->assertSame(TestStatus::ACTIVE, $instance->status);

        $instance2 = TestGraphWithEnum::from(['status' => 'inactive']);
        $this->assertSame(TestStatus::INACTIVE, $instance2->status);
    }

    // ==================== FROM JSON TESTS ====================

    public function test_type_graph_can_be_created_via_from_json(): void
    {
        $json = '{"name":"grass","url":"https://pokeapi.co/api/v2/type/12/"}';
        $type = TypeGraph::fromJson($json);

        $this->assertInstanceOf(TypeGraph::class, $type);
        $this->assertSame('grass', $type->name);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $type->url);
    }

    public function test_pokemon_detail_graph_can_be_created_via_from_json(): void
    {
        $json = '{
            "id": "charizard-006",
            "name": "Charizard",
            "height": 17,
            "weight": 905,
            "types": [
                {"name": "fire", "url": "https://pokeapi.co/api/v2/type/10/"},
                {"name": "flying", "url": "https://pokeapi.co/api/v2/type/3/"}
            ],
            "abilities": [
                {"name": "blaze", "url": "https://pokeapi.co/api/v2/ability/66/"}
            ],
            "stats": [
                {"name": "hp", "base_stat": 78},
                {"name": "attack", "base_stat": 84}
            ],
            "status": "legendary"
        }';

        $pokemon = PokemonDetailGraph::fromJson($json);

        $this->assertInstanceOf(PokemonDetailGraph::class, $pokemon);
        $this->assertSame('Charizard', $pokemon->name->getValue());
        $this->assertCount(2, $pokemon->types);
        $this->assertCount(1, $pokemon->abilities);
        $this->assertCount(2, $pokemon->stats);
        $this->assertSame(PokemonStatus::LEGENDARY, $pokemon->status);
    }

    public function test_from_json_throws_exception_for_invalid_json(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON');

        TypeGraph::fromJson('{invalid json}');
    }

    // ==================== HYDRATION SERVICE TESTS ====================

    public function test_type_graph_can_be_hydrated_via_hydration_service(): void
    {
        $data = ['name' => 'grass', 'url' => 'https://pokeapi.co/api/v2/type/12/'];
        $type = $this->hydration->hydrate(TypeGraph::class, $data);

        $this->assertInstanceOf(TypeGraph::class, $type);
        $this->assertSame('grass', $type->name);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $type->url);
    }

    public function test_pokemon_detail_graph_can_be_hydrated_via_hydration_service(): void
    {
        $data = [
            'id' => 'mewtwo-150',
            'name' => 'Mewtwo',
            'height' => 20,
            'weight' => 1220,
            'types' => [
                ['name' => 'psychic', 'url' => 'https://pokeapi.co/api/v2/type/14/'],
            ],
            'abilities' => [
                ['name' => 'pressure', 'url' => 'https://pokeapi.co/api/v2/ability/46/'],
            ],
            'stats' => [
                ['name' => 'hp', 'base_stat' => 106],
                ['name' => 'attack', 'base_stat' => 110],
            ],
            'status' => 'legendary',
        ];

        $pokemon = $this->hydration->hydrate(PokemonDetailGraph::class, $data);

        $this->assertInstanceOf(PokemonDetailGraph::class, $pokemon);
        $this->assertSame('Mewtwo', $pokemon->name->getValue());
        $this->assertSame(PokemonStatus::LEGENDARY, $pokemon->status);
    }

    public function test_type_graph_can_be_hydrated_from_json_via_hydration_service(): void
    {
        $json = '{"name":"grass","url":"https://pokeapi.co/api/v2/type/12/"}';
        $type = $this->hydration->hydrateFromJson(TypeGraph::class, $json);

        $this->assertInstanceOf(TypeGraph::class, $type);
        $this->assertSame('grass', $type->name);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $type->url);
    }

    // ==================== NORMALIZER CHAIN TESTS ====================

    public function test_type_graph_can_be_normalized_by_normalizer_chain(): void
    {
        $type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');
        $normalized = NormalizerChain::get()->normalize($type);

        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('name', $normalized);
        $this->assertArrayHasKey('url', $normalized);
        $this->assertSame('grass', $normalized['name']);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $normalized['url']);
    }

    public function test_pokemon_detail_graph_can_be_normalized_by_normalizer_chain(): void
    {
        $types = new TypeCollection;
        $types->add(new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/'));

        $abilities = new AbilityCollection;
        $abilities->add(new AbilityGraph('overgrow', 'https://pokeapi.co/api/v2/ability/65/'));

        $stats = new StatCollection;
        $stats->add(new StatGraph('hp', 45));

        $pokemonDetail = new PokemonDetailGraph(
            id: new PokemonId('bulbasaur-001'),
            name: new PokemonName('Bulbasaur'),
            height: new PokemonHeight(7),
            weight: new PokemonWeight(69),
            types: $types,
            abilities: $abilities,
            stats: $stats
        );

        $normalized = NormalizerChain::get()->normalize($pokemonDetail);

        $this->assertIsArray($normalized);
        $this->assertArrayHasKey('id', $normalized);
        $this->assertArrayHasKey('name', $normalized);
        $this->assertArrayHasKey('height', $normalized);
        $this->assertArrayHasKey('weight', $normalized);
        $this->assertArrayHasKey('types', $normalized);
        $this->assertArrayHasKey('abilities', $normalized);
        $this->assertArrayHasKey('stats', $normalized);
        $this->assertArrayHasKey('status', $normalized);
        $this->assertSame('bulbasaur-001', $normalized['id']);
        $this->assertSame('Bulbasaur', $normalized['name']);
        $this->assertSame(7.0, $normalized['height']);
        $this->assertSame(69.0, $normalized['weight']);
        $this->assertSame('active', $normalized['status']);
        $this->assertIsArray($normalized['types']);
        $this->assertIsArray($normalized['abilities']);
        $this->assertIsArray($normalized['stats']);
    }

    // ==================== GET_VALUE TESTS ====================

    public function test_type_graph_get_value_returns_self(): void
    {
        $type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');
        $value = $type->getValue();

        $this->assertSame($type, $value);
        $this->assertInstanceOf(TypeGraph::class, $value);
    }

    // ==================== TO_ARRAY TESTS ====================

    public function test_type_graph_to_array_returns_correct_array(): void
    {
        $type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');
        $array = $type->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('url', $array);
        $this->assertSame('grass', $array['name']);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $array['url']);
    }

    public function test_pokemon_detail_graph_to_array_returns_array_with_value_objects(): void
    {
        $types = new TypeCollection;
        $types->add(new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/'));

        $abilities = new AbilityCollection;
        $abilities->add(new AbilityGraph('overgrow', 'https://pokeapi.co/api/v2/ability/65/'));

        $stats = new StatCollection;
        $stats->add(new StatGraph('hp', 45));

        $pokemon = new PokemonDetailGraph(
            id: new PokemonId('bulbasaur-001'),
            name: new PokemonName('Bulbasaur'),
            height: new PokemonHeight(7),
            weight: new PokemonWeight(69),
            types: $types,
            abilities: $abilities,
            stats: $stats
        );

        $array = $pokemon->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('height', $array);
        $this->assertArrayHasKey('weight', $array);
        $this->assertArrayHasKey('types', $array);
        $this->assertArrayHasKey('abilities', $array);
        $this->assertArrayHasKey('stats', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertSame('bulbasaur-001', $array['id']);
        $this->assertSame('Bulbasaur', $array['name']);
        $this->assertSame(7.0, $array['height']);
        $this->assertSame(69.0, $array['weight']);
        $this->assertSame('active', $array['status']);
    }

    // ==================== JSON ENCODE TESTS ====================

    public function test_type_graph_can_be_json_encoded(): void
    {
        $type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');
        $json = json_encode($type);

        $this->assertIsString($json);
        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertSame('grass', $decoded['name']);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $decoded['url']);
    }

    // ==================== COLLECTION HYDRATION TESTS ====================

    public function test_graph_collection_can_be_hydrated_via_hydration_service(): void
    {
        $data = [
            ['name' => 'grass', 'url' => 'https://pokeapi.co/api/v2/type/12/'],
            ['name' => 'poison', 'url' => 'https://pokeapi.co/api/v2/type/4/'],
            ['name' => 'fire', 'url' => 'https://pokeapi.co/api/v2/type/10/'],
        ];

        $collection = $this->hydration->collect($data, TypeCollection::class);

        $this->assertInstanceOf(TypeCollection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertSame('grass', $collection[0]->name);
        $this->assertSame('poison', $collection[1]->name);
        $this->assertSame('fire', $collection[2]->name);
    }

    public function test_graph_collection_can_be_hydrated_from_json_via_hydration_service(): void
    {
        $json = '[
            {"name": "grass", "url": "https://pokeapi.co/api/v2/type/12/"},
            {"name": "poison", "url": "https://pokeapi.co/api/v2/type/4/"},
            {"name": "fire", "url": "https://pokeapi.co/api/v2/type/10/"}
        ]';

        $collection = $this->hydration->collectFromJson($json, TypeCollection::class);

        $this->assertInstanceOf(TypeCollection::class, $collection);
        $this->assertCount(3, $collection);
        $this->assertSame('grass', $collection[0]->name);
        $this->assertSame('poison', $collection[1]->name);
        $this->assertSame('fire', $collection[2]->name);
    }

    public function test_graph_collect_method_creates_collection(): void
    {
        $data = [
            ['name' => 'grass', 'url' => 'https://pokeapi.co/api/v2/type/12/'],
            ['name' => 'poison', 'url' => 'https://pokeapi.co/api/v2/type/4/'],
        ];

        $collection = TypeGraph::collect($data, TypeCollection::class);

        $this->assertInstanceOf(TypeCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(TypeGraph::class, $collection[0]);
        $this->assertSame('grass', $collection[0]->name);
    }

    public function test_graph_collect_throws_exception_for_invalid_collection_class(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TypeGraph::collect([], 'InvalidCollectionClass');
    }

    // ==================== VALUE OBJECT METHODS TESTS ====================

    public function test_pokemon_detail_graph_value_object_methods_work(): void
    {
        $pokemon = new PokemonDetailGraph(
            id: new PokemonId('pikachu-001'),
            name: new PokemonName('Pikachu'),
            height: new PokemonHeight(4),
            weight: new PokemonWeight(60),
            types: new TypeCollection,
            abilities: new AbilityCollection,
            stats: new StatCollection
        );

        $this->assertSame(0.4, $pokemon->height->getInMeters());
        $this->assertSame(40.0, $pokemon->height->getInCentimeters());
        $this->assertSame(6.0, $pokemon->weight->getInKg());
        $this->assertFalse($pokemon->name->isLegendary());
    }

    // ==================== IMMUTABILITY TESTS ====================

    public function test_graphs_are_immutable(): void
    {
        $type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');
        $originalName = $type->name;
        $originalUrl = $type->url;

        $this->assertSame($originalName, $type->name);
        $this->assertSame($originalUrl, $type->url);
    }

    // ==================== CASE SENSITIVITY WITH POKEMON ====================

    public function test_pokemon_detail_graph_preserves_case_sensitivity(): void
    {
        $data = [
            'id' => 'eevee-133',
            'name' => 'Eevee',
            'height' => 3,
            'weight' => 65,
            'types' => [],
            'abilities' => [],
            'stats' => [],
            'Status' => 'legendary', // Wrong case
        ];

        $pokemon = PokemonDetailGraph::from($data);

        // Status should be default because 'Status' != 'status'
        $this->assertSame(PokemonStatus::ACTIVE, $pokemon->status);
    }

    // ==================== FROM WITH SOURCE OBJECT ====================

    public function test_graph_from_accepts_object_source(): void
    {
        $source = new class
        {
            public string $name = 'grass';

            public string $url = 'https://pokeapi.co/api/v2/type/12/';
        };

        $type = TypeGraph::from($source);

        $this->assertInstanceOf(TypeGraph::class, $type);
        $this->assertSame('grass', $type->name);
        $this->assertSame('https://pokeapi.co/api/v2/type/12/', $type->url);
    }

    public function test_graph_from_throws_exception_for_invalid_source(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Source must be an array or object');

        TypeGraph::from('invalid string');
    }
}
