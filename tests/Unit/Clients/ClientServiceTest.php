<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Unit\Clients;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\Enums\HttpStatusCode;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\PokemonCollection;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Requests\GetPokemonRequest;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Responses\PokemonDetailResponse;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Responses\PokemonListResponse;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonListStruct;
use AndyDefer\PhpClient\Tests\TestCase;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;

#[AllowMockObjectsWithoutExpectations]
final class ClientServiceTest extends TestCase
{
    private ClientService $client;

    private GuzzleClient|MockObject $guzzleMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guzzleMock = $this->createMock(GuzzleClient::class);
        $this->client = new ClientService($this->guzzleMock);
    }

    // ==================== SUCCESSFUL REQUESTS TESTS ====================

    public function test_get_request_success(): void
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

        $guzzleResponse = new GuzzleResponse(200, [], $json);

        // Ajouter un header Accept pour que le callback fonctionne
        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://pokeapi.co/api/v2/pokemon',
                $this->callback(function ($options) {
                    // Vérifier que les options sont un tableau
                    // Les headers peuvent être vides ou présents
                    return is_array($options);
                })
            )
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;
        $request->setLimit(20);
        // Ajouter un header Accept pour que le test ait des headers
        $request->getHeaders()->setAccept(ContentType::JSON);

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );

        $this->assertInstanceOf(PokemonListResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertSame(HttpStatusCode::OK, $response->getStatusCode());

        $pokemons = $response->getPokemons();
        $this->assertInstanceOf(PokemonCollection::class, $pokemons);
        $this->assertCount(2, $pokemons);
        $this->assertSame('bulbasaur', $pokemons[0]->name);
        $this->assertSame('ivysaur', $pokemons[1]->name);
        $this->assertSame(2, $response->getCount());
    }

    public function test_post_request_success(): void
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

        $guzzleResponse = new GuzzleResponse(201, [], $json);

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://pokeapi.co/api/v2/pokemon',
                $this->callback(function ($options) {
                    // Vérifier que le body existe et est une chaîne non vide
                    return isset($options['body'])
                        && is_string($options['body'])
                        && ! empty($options['body']);
                })
            )
            ->willReturn($guzzleResponse);

        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::POST;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://pokeapi.co/api/v2/pokemon');
            }

            protected function setBody(): RequestBodyVO
            {
                $struct = new class extends Struct
                {
                    public function __construct(
                        public readonly string $name = 'pikachu',
                    ) {}
                };

                return new RequestBodyVO(new $struct, ContentType::JSON);
            }
        };

        $response = $this->client->post(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonDetailResponse::class
        );

        $this->assertInstanceOf(PokemonDetailResponse::class, $response);
        $this->assertTrue($response->isSuccess());
        $this->assertSame(HttpStatusCode::CREATED, $response->getStatusCode());

        $pokemon = $response->getPokemon();
        $this->assertSame('Pikachu', $pokemon->name->getValue());
        $this->assertSame('pikachu-001', $pokemon->id->getValue());
        $this->assertCount(1, $pokemon->types);
    }

    public function test_put_request_success(): void
    {
        $guzzleResponse = new GuzzleResponse(200, [], '{"success":true}');

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with('PUT', $this->stringContains('/pokemon/1'))
            ->willReturn($guzzleResponse);

        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::PUT;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://pokeapi.co/api/v2/pokemon/1');
            }

            protected function setBody(): RequestBodyVO
            {
                $struct = new class extends Struct
                {
                    public function __construct(
                        public readonly string $name = 'pikachu',
                    ) {}
                };

                return new RequestBodyVO(new $struct, ContentType::JSON);
            }
        };

        $response = $this->client->put(
            'https://pokeapi.co/api/v2/pokemon/1',
            $request,
            PokemonDetailResponse::class
        );

        $this->assertTrue($response->isSuccess());
        $this->assertSame(HttpStatusCode::OK, $response->getStatusCode());
    }

    public function test_delete_request_success(): void
    {
        $guzzleResponse = new GuzzleResponse(204, [], 'null');

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with('DELETE', $this->stringContains('/pokemon/1'))
            ->willReturn($guzzleResponse);

        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::DELETE;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://pokeapi.co/api/v2/pokemon/1');
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

        $response = $this->client->delete(
            'https://pokeapi.co/api/v2/pokemon/1',
            $request,
            PokemonListResponse::class
        );

        $this->assertTrue($response->isSuccess());
        $this->assertSame(HttpStatusCode::NO_CONTENT, $response->getStatusCode());
    }

    public function test_patch_request_success(): void
    {
        $guzzleResponse = new GuzzleResponse(200, [], '{"updated":true}');

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with('PATCH', $this->stringContains('/pokemon/1'))
            ->willReturn($guzzleResponse);

        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::PATCH;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://pokeapi.co/api/v2/pokemon/1');
            }

            protected function setBody(): RequestBodyVO
            {
                $struct = new class extends Struct
                {
                    public function __construct(
                        public readonly string $name = 'raichu',
                    ) {}
                };

                return new RequestBodyVO(new $struct, ContentType::JSON);
            }
        };

        $response = $this->client->patch(
            'https://pokeapi.co/api/v2/pokemon/1',
            $request,
            PokemonListResponse::class
        );

        $this->assertTrue($response->isSuccess());
        $this->assertSame(HttpStatusCode::OK, $response->getStatusCode());
    }

    // ==================== ERROR RESPONSES TESTS ====================

    public function test_request_with_404_error(): void
    {
        $errorJson = '{"status":"NOT_FOUND"}';
        $guzzleResponse = new GuzzleResponse(404, [], $errorJson);

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;
        $request->setPokemonName('unknown');

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon/unknown',
            $request,
            PokemonListResponse::class
        );

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertSame(HttpStatusCode::NOT_FOUND, $response->getStatusCode());
    }

    public function test_request_with_401_error(): void
    {
        $errorJson = '{"status":"REJECTED","failureReason":{"failureCode":"AUTHENTICATION_ERROR","failureMessage":"Invalid token"}}';
        $guzzleResponse = new GuzzleResponse(401, [], $errorJson);

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertSame(HttpStatusCode::UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_request_with_500_error(): void
    {
        $errorJson = '{"failureReason":{"failureCode":"UNKNOWN_ERROR","failureMessage":"Internal server error"}}';
        $guzzleResponse = new GuzzleResponse(500, [], $errorJson);

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertSame(HttpStatusCode::INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    // ==================== HEADERS AND OPTIONS TESTS ====================

    public function test_request_with_custom_headers(): void
    {
        $guzzleResponse = new GuzzleResponse(200, [], '{"count":0,"results":[]}');

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return isset($options['headers']['Authorization'])
                        && $options['headers']['Authorization'] === 'Bearer token123'
                        && isset($options['headers']['X-Request-Id']);
                })
            )
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;
        $request->getHeaders()
            ->setAuthorization('token123')
            ->setXRequestId('req-123');

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );

        $this->assertTrue($response->isSuccess());
    }

    public function test_request_with_custom_options(): void
    {
        $guzzleResponse = new GuzzleResponse(200, [], '{"count":0,"results":[]}');

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return isset($options['timeout'])
                        && $options['timeout'] === 30
                        && isset($options['connect_timeout'])
                        && $options['connect_timeout'] === 10;
                })
            )
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;
        $request->getOptions()
            ->setTimeout(30)
            ->setConnectTimeout(10);

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );

        $this->assertTrue($response->isSuccess());
    }

    // ==================== EMPTY BODY TESTS ====================

    public function test_request_with_empty_body(): void
    {
        $guzzleResponse = new GuzzleResponse(200, [], 'null');

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($options) {
                    return ! isset($options['body']);
                })
            )
            ->willReturn($guzzleResponse);

        $request = new class extends Request
        {
            protected function setMethod(): HttpMethod
            {
                return HttpMethod::GET;
            }

            protected function setUrl(): UrlVO
            {
                return new UrlVO('https://pokeapi.co/api/v2/pokemon');
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

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );

        $this->assertTrue($response->isSuccess());
    }

    // ==================== EXCEPTION TESTS ====================

    public function test_request_throws_exception_on_guzzle_error(): void
    {
        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new class extends \Exception implements GuzzleException
            {
                public function __construct()
                {
                    parent::__construct('Connection timeout');
                }
            });

        $request = new GetPokemonRequest;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HTTP request failed: Connection timeout');

        $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );
    }

    // ==================== INVALID RESPONSE CLASS TESTS ====================

    public function test_request_with_invalid_response_class(): void
    {
        $guzzleResponse = new GuzzleResponse(200, [], '{"count":0,"results":[]}');

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;

        $this->expectException(\TypeError::class);

        $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            'InvalidResponseClass'
        );
    }

    // ==================== BUILD OPTIONS TESTS ====================

    public function test_build_options_with_headers_and_body(): void
    {
        $request = new GetPokemonRequest;
        $request->getHeaders()->setAuthorization('token');

        $struct = new class extends Struct
        {
            public function __construct(
                public readonly string $data = 'test',
            ) {}
        };

        $body = new RequestBodyVO(new $struct, ContentType::JSON);

        $reflection = new \ReflectionClass($request);
        $bodyProperty = $reflection->getProperty('body');
        $bodyProperty->setValue($request, $body);

        // Invoquer buildOptions sur le client, pas sur la requête
        $options = $this->invokeMethod($this->client, 'buildOptions', [$request]);

        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('Authorization', $options['headers']);
        $this->assertArrayHasKey('body', $options);
        $this->assertIsString($options['body']);
    }

    // ==================== GET STRUCT CLASS FROM RESPONSE TESTS ====================

    public function test_get_struct_class_from_response_with_method(): void
    {
        $responseClass = PokemonListResponse::class;
        $structClass = $this->invokeMethod(
            $this->client,
            'getStructClassFromResponse',
            [$responseClass]
        );

        $this->assertSame(PokemonListStruct::class, $structClass);
    }

    public function test_get_struct_class_from_response_without_method(): void
    {
        $responseClass = (new class extends Response
        {
            public function __construct()
            {
                parent::__construct(
                    HttpStatusCode::OK,
                    new ResponseBodyVO('{}', PokemonListStruct::class),
                    new HeadersVO
                );
            }
        })::class;

        $structClass = $this->invokeMethod(
            $this->client,
            'getStructClassFromResponse',
            [$responseClass]
        );

        $this->assertNull($structClass);
    }

    // ==================== HELPER METHODS ====================

    private function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }

    // ==================== INTEGRATION STYLE TESTS ====================

    public function test_full_request_response_cycle(): void
    {
        $json = '{
            "count": 1,
            "next": null,
            "previous": null,
            "results": [
                {"name": "pikachu", "url": "https://pokeapi.co/api/v2/pokemon/25/"}
            ]
        }';

        $guzzleResponse = new GuzzleResponse(200, ['Content-Type' => 'application/json'], $json);

        $this->guzzleMock
            ->expects($this->once())
            ->method('request')
            ->willReturn($guzzleResponse);

        $request = new GetPokemonRequest;
        $request
            ->setLimit(1)
            ->getHeaders()
            ->setAccept(ContentType::JSON);

        $response = $this->client->get(
            'https://pokeapi.co/api/v2/pokemon',
            $request,
            PokemonListResponse::class
        );

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(1, $response->getCount());

        $pokemons = $response->getPokemons();
        $this->assertCount(1, $pokemons);
        $this->assertSame('pikachu', $pokemons[0]->name);
    }
}
