# PHP HTTP Client - Documentation Complète

## 📖 Introduction

**PHP HTTP Client** est une bibliothèque PHP moderne qui transforme les appels HTTP en **objets métier typés, immutables et sécurisés**.

Elle repose sur GuzzleHttp mais ajoute une couche d'abstraction qui élimine les problèmes de parsing manuel, de données non typées et de configuration dispersée.

---

## 🎯 Pourquoi ce package existe

### Le problème : Les appels HTTP bruts sont fragiles

```php
// ❌ Sans ce package - Fragile, non typé, difficile à maintenir
$response = $client->get('https://api.example.com/users');
$data = json_decode($response->getBody(), true);
$userName = $data['data']['user']['name'] ?? null; 
// ↑ Si l'API change, tout casse sans prévenir
// ↑ Pas d'autocomplétion
// ↑ Pas de typage
```

### La solution : Des objets métier typés

```php
// ✅ Avec PHP HTTP Client - Typé, sûr, auto-complétion
$request = new GetUserRequest();
$request->setUserId(123);

$response = $client->get(
    'https://api.example.com/users', 
    $request, 
    UserListResponse::class
);

$users = $response->getUsers(); // Typé : UserCollection
// ↑ Autocomplétion
// ↑ Types sûrs (int, string, Enum, etc.)
// ↑ Validation automatique des données
```

---

## 🎯 Problèmes résolus

| Problème | Solution PHP HTTP Client |
|----------|--------------------------|
| **Données non typées** | Objets PHP typés avec PHPDoc et `readonly` |
| **Parsing manuel** | Hydratation automatique via `from()` |
| **Configuration dispersée** | Value Objects centralisés (HeadersVO, OptionsVO) |
| **Pas de standardisation** | Architecture cohérente Request/Response |
| **Données mutables** | Immutabilité totale (`readonly`) |
| **Pas de validation** | Validation automatique des URLs, JSON, types |

---

## 🚀 Installation

```bash
composer require andydefer/php-client
```

### Prérequis

- PHP 8.1+
- GuzzleHttp 7.0+
- extension JSON

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        ClientService                            │
│  (Envoi des requêtes, gestion des options, hydratation)        │
└────────────────────┬────────────────────────────────────────────┘
                     │
      ┌──────────────┴──────────────┐
      │                             │
┌─────▼─────┐              ┌────────▼────────┐
│  Request  │              │    Response     │
│ (Abstrait)│              │   (Abstrait)    │
│  + Method │              │ + StatusCode    │
│  + URL    │              │ + Body          │
│  + Body   │              │ + Headers       │
│  + Headers│              └────────┬────────┘
│  + Options│                       │
└─────┬─────┘              ┌────────▼────────┐
      │                    │   Concrete      │
┌─────▼─────┐              │   Response      │
│  Concrete │              │ + getUsers()    │
│  Request  │              │ + getTotal()    │
│  + setUserId()           └─────────────────┘
└───────────┘
```

### Les 4 piliers

| Pilier | Rôle |
|--------|------|
| **Request** | Encapsule la requête HTTP (méthode, URL, corps, headers, options) |
| **Response** | Encapsule la réponse HTTP (status, corps, headers) |
| **Value Objects** | Objets immutables pour headers, options, URL, corps |
| **ClientService** | Envoie la requête et hydrate la réponse |

---

## 📦 Composants principaux

### 1. Request - La requête HTTP

Une `Request` encapsule toutes les informations d'une requête HTTP. Elle est **immutable** dans sa structure (méthode, URL, corps) mais permet de configurer les headers et options.

```php
use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;

final class GetUserRequest extends Request
{
    private ?int $userId = null;

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        $url = new UrlVO('https://api.example.com/users');
        if ($this->userId !== null) {
            $url = $url->withPath('/users/' . $this->userId);
        }
        return $url;
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}
```

**Points clés :**
- `setMethod()` : Définit la méthode HTTP (GET, POST, etc.)
- `setUrl()` : Définit l'URL avec validation automatique via `UrlVO`
- `setBody()` : Définit le corps de la requête avec son Content-Type
- Les méthodes `getHeaders()` et `getOptions()` sont disponibles pour la configuration

---

### 2. Response - La réponse HTTP

Une `Response` encapsule la réponse HTTP et fournit des méthodes métier pour y accéder.

```php
use AndyDefer\PhpClient\Abstracts\Response;

final class UserListResponse extends Response
{
    public function getUsers(): array
    {
        return $this->getBody()->getValue()->users ?? [];
    }

    public function getTotal(): int
    {
        return $this->getBody()->getValue()->total ?? 0;
    }

    public static function getStructClass(): string
    {
        return UserListStruct::class;
    }
}
```

**Points clés :**
- `getBody()->getValue()` retourne l'objet structuré typé
- Les méthodes `isSuccess()` et `isError()` sont disponibles
- `getStatusCode()` retourne un enum `HttpStatusCode`

---

### 3. ClientService - Le client HTTP

Le `ClientService` est le point d'entrée. Il envoie les requêtes et retourne des réponses typées.

```php
use AndyDefer\PhpClient\Clients\ClientService;

$client = new ClientService();

$response = $client->get(
    'https://api.example.com/users',  // URI (absolue ou relative)
    $request,                          // Instance de Request
    UserListResponse::class            // Classe de réponse attendue
);
```

**Méthodes disponibles :**
- `get()`, `post()`, `put()`, `patch()`, `delete()`

---

## 🔧 Value Objects

### HeadersVO - Gestion des en-têtes

Les en-têtes HTTP sont gérés via un Value Object immutable.

```php
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\Enums\ContentType;

$headers = new HeadersVO();
$headers
    ->setAuthorization('token123')                 // Authorization: Bearer token123
    ->setContentType(ContentType::JSON)            // Content-Type: application/json
    ->setAccept(ContentType::JSON)                 // Accept: application/json
    ->setHost('api.example.com')                   // Host: api.example.com
    ->setUserAgent('PHP/8.2')                      // User-Agent: PHP/8.2
    ->setXRequestId('req-123-456')                 // X-Request-Id: req-123-456
    ->setCustom('X-Custom-Header', 'value');       // Header personnalisé
```

**Méthodes disponibles :**

| Catégorie | Méthodes |
|-----------|----------|
| Généraux | `setHost()`, `setUserAgent()`, `setAccept()`, `setAcceptEncoding()` |
| Authentification | `setAuthorization()`, `setBasicAuth()`, `setApiKey()`, `setCookie()` |
| Contenu | `setContentType()`, `setContentLength()`, `setContentEncoding()` |
| Cache | `setCacheControl()`, `setIfModifiedSince()`, `setIfNoneMatch()` |
| Sécurité | `setXsrfToken()`, `setStrictTransportSecurity()` |
| Personnalisés | `setCustom()`, `setXRequestId()`, `setXCorrelationId()` |

---

### OptionsVO - Options de configuration

Les options de transfert sont centralisées dans un Value Object.

```php
use AndyDefer\PhpClient\ValueObjects\OptionsVO;

$options = new OptionsVO();
$options
    // Timeouts
    ->setTimeout(30)                    // Délai d'attente global
    ->setConnectTimeout(10)             // Délai de connexion
    
    // SSL
    ->setVerify(true)                   // Vérification SSL
    ->setCert('/path/to/cert.pem')      // Certificat SSL
    ->setSslKey('/path/to/key.pem')     // Clé SSL
    
    // Redirections
    ->setAllowRedirects(false)          // Suivre les redirections
    ->setMaxRedirects(5)                // Nombre max de redirections
    
    // Proxy
    ->setProxy('tcp://localhost:8080')  // Proxy
    ->setNoProxy(['.example.com'])      // Exclure du proxy
    
    // Authentification
    ->setAuth(['username', 'password']) // Basic auth
    
    // Environnement
    ->setBaseUri('https://api.example.com')  // Base URI
    ->setQuery(['page' => 1])                // Query params
    ->setDecodeContent(true)                 // Décoder le contenu
    ->setHttpErrors(false);                  // Ne pas lancer d'exception sur erreur HTTP
```

---

### RequestBodyVO - Corps de requête

Le corps de requête est typé et peut être en JSON ou en formulaire.

```php
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\Abstracts\Struct;

// Corps JSON
$struct = new UserCreateStruct(name: 'John', email: 'john@example.com');
$body = new RequestBodyVO($struct, ContentType::JSON);

$json = $body->toString();   // '{"name":"John","email":"..."}'
$array = $body->toArray();   // ['name' => 'John', 'email' => '...']
$struct = $body->getStruct(); // UserCreateStruct

// Corps formulaire
$body = new RequestBodyVO($struct, ContentType::FORM);
$formData = $body->toString(); // 'name=John&email=...'
```

---

### ResponseBodyVO - Corps de réponse

Le corps de réponse est automatiquement hydraté en objet structuré.

```php
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;

$body = new ResponseBodyVO(
    $jsonResponse,              // Chaîne JSON
    UserListStruct::class,      // Classe de structure
    ContentType::JSON           // Content-Type attendu
);

$struct = $body->getValue();    // UserListStruct hydraté
$formatted = $body->format();   // stdClass ou array
$isEmpty = $body->isEmpty();    // true si le corps est vide
$isValid = $body->isValidJson(); // true si le JSON est valide
```

---

### UrlVO - URL validée

L'URL est automatiquement validée et découpée en parties.

```php
use AndyDefer\PhpClient\ValueObjects\UrlVO;

$url = new UrlVO('https://api.example.com:8080/v2/users?page=1#section');

$url->getScheme();    // 'https'
$url->getHost();      // 'api.example.com'
$url->getPort();      // 8080
$url->getPath();      // '/v2/users'
$url->getQuery();     // UrlQueryVO avec 'page=1'
$url->getFragment();  // 'section'
$url->getBaseUrl();   // 'https://api.example.com:8080'
$url->getFullPath();  // '/v2/users?page=1#section'

// Modification (retourne une nouvelle instance)
$newUrl = $url->withPath('/v3/users');
$newUrl = $url->withQuery(new UrlQueryVO('limit=10'));
$newUrl = $url->withFragment(null);
```

### UrlQueryVO - Paramètres de la query

```php
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;

$query = new UrlQueryVO('page=1&limit=10');
$query = $query
    ->withParameter('page', 2)
    ->withParameter('sort', 'asc')
    ->withoutParameter('limit');

echo $query->toString(); // 'page=2&sort=asc'

// Intégration avec UrlVO
$url = new UrlVO('https://api.example.com/users?page=1');
$newUrl = $url->withQuery($query);
echo $newUrl->getValue(); // 'https://api.example.com/users?page=2&sort=asc'
```

---

## 📝 Enums disponibles

| Enum | Description | Valeurs |
|------|-------------|---------|
| `HttpMethod` | Méthodes HTTP | GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS |
| `HttpStatusCode` | Codes HTTP | 100-599 avec messages standards |
| `ContentType` | Types de contenu | JSON, JSON_UTF8, PROBLEM_JSON, FORM |
| `ContentEncoding` | Encodages de contenu | GZIP, DEFLATE, BR, ZSTD, IDENTITY |
| `HeaderType` | Types d'en-têtes | HOST, USER_AGENT, ACCEPT, AUTHORIZATION, etc. |
| `OptionType` | Types d'options | TIMEOUT, CONNECT_TIMEOUT, VERIFY, etc. |
| `CacheControl` | Cache | NO_CACHE, NO_STORE, MAX_AGE, etc. |
| `ConnectionType` | Connexion | KEEP_ALIVE, CLOSE, UPGRADE |
| `AcceptLanguage` | Langues | FR, FR_FR, EN, EN_US, etc. |
| `Encoding` | Encodages caractères | UTF_8, UTF_16, ISO_8859_1, etc. |

---

## 💡 Cas d'utilisation avec JSONPlaceholder

### Exemple complet : Client JSONPlaceholder

```php
<?php

declare(strict_types=1);

require './vendor/autoload.php';

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

// 1. Enum - Centralisation des URLs avec UrlVO
enum PlaceholderEndpoint: string
{
    case POSTS = 'https://jsonplaceholder.typicode.com/posts';
    case COMMENTS = 'https://jsonplaceholder.typicode.com/comments';

    public function getUrl(): UrlVO
    {
        return new UrlVO($this->value);
    }

    public function withId(int $id): UrlVO
    {
        $baseUrl = $this->getUrl();
        $path = parse_url($this->value, PHP_URL_PATH) . '/' . $id;
        return $baseUrl->withPath($path);
    }

    public function withQuery(array $params): UrlVO
    {
        $baseUrl = $this->getUrl();
        $query = http_build_query($params);
        return $baseUrl->withQuery(new UrlQueryVO($query));
    }
}

// 2. Graph
final class PostGraph extends Graph
{
    public function __construct(
        public readonly int $userId,
        public readonly int $id,
        public readonly string $title,
        public readonly string $body,
    ) {}
}

final class CommentGraph extends Graph
{
    public function __construct(
        public readonly int $postId,
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $body,
    ) {}
}

// 3. Struct
final class PostListStruct extends Struct
{
    public function __construct(
        public readonly array $posts,
    ) {}
}

final class CommentListStruct extends Struct
{
    public function __construct(
        public readonly array $comments,
    ) {}
}

final class CreatedPostStruct extends Struct
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $body,
        public readonly int $user_id,
    ) {}
}

final class CreatePostStruct extends Struct
{
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly int $userId,
    ) {}
}

// 4. Requests avec UrlVO
final class GetPostsRequest extends Request
{
    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        return PlaceholderEndpoint::POSTS->getUrl();
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}

final class GetCommentsRequest extends Request
{
    private ?int $postId = null;

    public function setPostId(int $postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        if ($this->postId !== null) {
            return PlaceholderEndpoint::COMMENTS->withQuery(['postId' => $this->postId]);
        }
        return PlaceholderEndpoint::COMMENTS->getUrl();
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}

final class GetCommentRequest extends Request
{
    private int $id = 0;

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        return PlaceholderEndpoint::COMMENTS->withId($this->id);
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            new class extends Struct {},
            ContentType::JSON
        );
    }
}

final class CreatePostRequest extends Request
{
    private string $title = '';
    private string $content = '';
    private int $userId = 0;

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    protected function setUrl(): UrlVO
    {
        return PlaceholderEndpoint::POSTS->getUrl();
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new CreatePostStruct(
            title: $this->title,
            body: $this->content,
            userId: $this->userId
        );
        return new RequestBodyVO($struct, ContentType::JSON);
    }
}

// 5. Responses
final class PostListResponse extends Response
{
    public function getPosts(): array
    {
        $data = $this->getBody()->format();
        $posts = [];
        foreach ($data as $item) {
            $posts[] = PostGraph::from($item);
        }
        return $posts;
    }

    public static function getStructClass(): string
    {
        return PostListStruct::class;
    }
}

final class CommentListResponse extends Response
{
    public function getComments(): array
    {
        $data = $this->getBody()->format();
        $comments = [];
        foreach ($data as $item) {
            $comments[] = CommentGraph::from($item);
        }
        return $comments;
    }

    public static function getStructClass(): string
    {
        return CommentListStruct::class;
    }
}

final class CommentResponse extends Response
{
    public function getComment(): CommentGraph
    {
        $data = $this->getBody()->format();
        return CommentGraph::from($data);
    }

    public static function getStructClass(): string
    {
        return CommentStruct::class;
    }
}

final class CreatePostResponse extends Response
{
    public function getCreatedPost(): CreatedPostStruct
    {
        $data = $this->getBody()->format();
        if (is_object($data)) {
            $data = (array) $data;
        }
        return new CreatedPostStruct(
            id: (int) ($data['id'] ?? 0),
            title: (string) ($data['title'] ?? ''),
            body: (string) ($data['body'] ?? ''),
            user_id: (int) ($data['user_id'] ?? 0)
        );
    }

    public function getId(): int
    {
        return $this->getCreatedPost()->id;
    }

    public static function getStructClass(): string
    {
        return CreatedPostStruct::class;
    }
}

// 6. Client
class JsonPlaceholderClient
{
    private ClientService $client;

    public function __construct()
    {
        $this->client = new ClientService();
    }

    public function getPosts(): array
    {
        $request = new GetPostsRequest();
        $response = $this->client->get(
            PlaceholderEndpoint::POSTS->getUrl()->getValue(),
            $request,
            PostListResponse::class
        );
        return $response->getPosts();
    }

    public function getComments(?int $postId = null): array
    {
        $request = new GetCommentsRequest();
        if ($postId !== null) {
            $request->setPostId($postId);
        }
        $response = $this->client->get(
            PlaceholderEndpoint::COMMENTS->getUrl()->getValue(),
            $request,
            CommentListResponse::class
        );
        return $response->getComments();
    }

    public function getComment(int $id): CommentGraph
    {
        $request = new GetCommentRequest();
        $request->setId($id);
        $response = $this->client->get(
            PlaceholderEndpoint::COMMENTS->withId($id)->getValue(),
            $request,
            CommentResponse::class
        );
        return $response->getComment();
    }

    public function createPost(string $title, string $content, int $userId): CreatedPostStruct
    {
        $request = new CreatePostRequest();
        $request
            ->setTitle($title)
            ->setContent($content)
            ->setUserId($userId);

        $request->getHeaders()
            ->setContentType(ContentType::JSON)
            ->setAccept(ContentType::JSON);

        $response = $this->client->post(
            PlaceholderEndpoint::POSTS->getUrl()->getValue(),
            $request,
            CreatePostResponse::class
        );
        return $response->getCreatedPost();
    }
}

// 7. Utilisation avec HeadersVO et OptionsVO
$client = new JsonPlaceholderClient();

// Récupérer tous les posts
$posts = $client->getPosts();
echo "Posts: " . count($posts) . "\n";

// Récupérer un commentaire
$comment = $client->getComment(1);
echo "Commentaire: " . $comment->name . "\n";

// Créer un post avec headers et options
$request = new CreatePostRequest();
$request
    ->setTitle('Mon titre')
    ->setContent('Mon contenu')
    ->setUserId(1);

$request->getHeaders()
    ->setAuthorization('token-123')
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON)
    ->setXRequestId('req-123-456');

$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false);

$response = $client->post(
    PlaceholderEndpoint::POSTS->getUrl()->getValue(),
    $request,
    CreatePostResponse::class
);

$created = $response->getCreatedPost();
echo "Post créé: " . $created->id . "\n";
```

---

## 🧩 Composants avancés

### Struct - Structure complète de réponse API

`Struct` est une structure de données complète représentant une réponse API. Elle étend `HydratableStructure` et ajoute des méthodes d'encodage et de décodage.

```php
final class PokemonListStruct extends Struct
{
    public function __construct(
        public readonly int $count,
        public readonly ?string $next,
        public readonly ?string $previous,
        public readonly PokemonCollection $results,
    ) {}
}

// Utilisation
$struct = PokemonListStruct::fromJson($json);
$json = $struct->encode(ContentType::JSON);
```

### Graph - Portion de structure de réponse API

Un `Graph` représente une portion de structure de réponse API. Il sert à documenter et structurer les fragments d'une réponse JSON.

```php
final class TypeGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}

// Hydratation automatique
$type = TypeGraph::from([
    'name' => 'grass',
    'url' => 'https://pokeapi.co/api/v2/type/12/'
]);
```

---

## 🔧 Options disponibles (référence complète)

### Headers via HeadersVO

```php
$request->getHeaders()
    ->setContentType(ContentType::JSON)               // Content-Type
    ->setAuthorization('token123')                    // Authorization: Bearer token123
    ->setBasicAuth('username', 'password')            // Basic auth
    ->setApiKey('abc123')                             // X-API-Key: abc123
    ->setCookie('session=xyz')                        // Cookie
    ->setAccept(ContentType::JSON)                    // Accept
    ->setAcceptEncoding(ContentEncoding::GZIP)        // Accept-Encoding
    ->setAcceptLanguage('fr-FR')                      // Accept-Language
    ->setHost('api.example.com')                      // Host
    ->setUserAgent('PHP/8.2')                         // User-Agent
    ->setCacheControl(CacheControl::NO_CACHE)         // Cache-Control
    ->setXRequestId('req-123')                        // X-Request-Id
    ->setXCorrelationId('corr-123')                   // X-Correlation-Id
    ->setXsrfToken('token-xyz')                       // X-CSRF-Token
    ->setCustom('X-Custom', 'value');                 // Custom header
```

### Options via OptionsVO

```php
$request->getOptions()
    // Timeouts
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setReadTimeout(60)
    
    // SSL
    ->setVerify(true)
    ->setCert('/path/to/cert.pem')
    ->setSslKey('/path/to/key.pem')
    
    // Redirections
    ->setAllowRedirects(false)
    ->setMaxRedirects(5)
    
    // Proxy
    ->setProxy('tcp://localhost:8080')
    ->setNoProxy(['.example.com'])
    
    // Authentification
    ->setAuth(['username', 'password'])
    
    // Environnement
    ->setBaseUri('https://api.example.com')
    ->setQuery(['page' => 1])
    ->setDecodeContent(true)
    ->setForceIpResolve('v4')
    ->setHttpErrors(false)
    ->setDebug(true);
```

### UrlVO - Manipulation d'URL

```php
use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;

$url = new UrlVO('https://api.example.com:8080/v2/users?page=1#section');

// Accès aux composants
$url->getScheme();    // 'https'
$url->getHost();      // 'api.example.com'
$url->getPort();      // 8080
$url->getPath();      // '/v2/users'
$url->getQuery();     // UrlQueryVO
$url->getFragment();  // 'section'
$url->getBaseUrl();   // 'https://api.example.com:8080'
$url->getFullPath();  // '/v2/users?page=1#section'

// Modifications (immuables)
$newUrl = $url
    ->withPath('/v3/users')
    ->withQuery(new UrlQueryVO('limit=20'))
    ->withFragment('new-section');
```

### UrlQueryVO - Manipulation de query

```php
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;

$query = new UrlQueryVO('page=1&limit=10');

// Accès
$query->get('page');     // '1'
$query->has('limit');    // true
$query->isEmpty();       // false
$query->getParameters(); // ['page' => '1', 'limit' => '10']

// Modifications (immuables)
$newQuery = $query
    ->withParameter('page', 2)
    ->withParameter('sort', 'asc')
    ->withoutParameter('limit')
    ->merge(['filter' => 'active']);

echo $newQuery->toString(); // 'page=2&sort=asc&filter=active'

// Comparaison (ordre ignoré)
$query1 = new UrlQueryVO('page=1&limit=10');
$query2 = new UrlQueryVO('limit=10&page=1');
$query1->equals($query2); // true
```

---

## 📊 Gestion des erreurs

### Exceptions possibles

| Situation | Exception | Message |
|-----------|-----------|---------|
| URL invalide | `InvalidArgumentException` | `Invalid URL: X` |
| JSON invalide | `InvalidArgumentException` | `Invalid JSON: Syntax error` |
| Paramètre manquant | `InvalidArgumentException` | `Missing required parameters for X: $Y` |
| Erreur Guzzle | `RuntimeException` | `HTTP request failed: X` |
| Classe invalide | `TypeError` | - |

### Codes HTTP gérés via HttpStatusCode

```php
$statusCode = $response->getStatusCode();
$statusCode->isSuccess();        // 2xx
$statusCode->isError();          // 4xx ou 5xx
$statusCode->isOk();             // 200
$statusCode->isNotFound();       // 404
$statusCode->isUnauthorized();   // 401
$statusCode->isUnprocessableEntity(); // 422
$statusCode->getPhrase();        // 'OK', 'Not Found', etc.
```

---

## 🎯 Bonnes pratiques

### 1. Structurer ses requêtes avec UrlVO

```php
// ✅ Bon - Une classe par endpoint avec UrlVO
final class GetUserRequest extends Request
{
    private ?int $userId = null;

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    protected function setUrl(): UrlVO
    {
        $url = new UrlVO('https://api.example.com/users');
        if ($this->userId !== null) {
            $url = $url->withPath('/users/' . $this->userId);
        }
        return $url;
    }
}

// ❌ Mauvais - Concaténation de strings
protected function setUrl(): UrlVO
{
    return new UrlVO('https://api.example.com/users/' . $this->userId);
}
```

### 2. Centraliser les URLs avec un Enum

```php
// ✅ Bon - URLs centralisées
enum ApiEndpoint: string
{
    case USERS = 'https://api.example.com/users';
    case POSTS = 'https://api.example.com/posts';

    public function getUrl(): UrlVO
    {
        return new UrlVO($this->value);
    }

    public function withId(int $id): UrlVO
    {
        return $this->getUrl()->withPath(parse_url($this->value, PHP_URL_PATH) . '/' . $id);
    }
}

// ❌ Mauvais - URLs dispersées dans le code
protected function setUrl(): UrlVO
{
    return new UrlVO('https://api.example.com/users/123');
}
```

### 3. Utiliser les enums pour les valeurs fixes

```php
// ✅ Bon - Enum pour les valeurs fixes
$request->getHeaders()->setContentType(ContentType::JSON);
$request->getOptions()->setTimeout(30);

// ❌ Mauvais - String magique
$request->getHeaders()->setCustom('Content-Type', 'application/json');
$request->getOptions()->setCustom('timeout', 30);
```

### 4. Configurer les headers dans un bloc

```php
// ✅ Bon - Configuration groupée
$request->getHeaders()
    ->setAuthorization($token)
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON)
    ->setXRequestId($requestId);

// ❌ Mauvais - Configuration dispersée
$request->getHeaders()->setAuthorization($token);
// ... 20 lignes plus tard
$request->getHeaders()->setContentType(ContentType::JSON);
```

---

## 🔒 Sécurité

### Validations automatiques

| Validation | Mécanisme |
|------------|-----------|
| **URL** | `FILTER_VALIDATE_URL` dans `UrlVO` |
| **JSON** | `json_decode()` avec `JSON_THROW_ON_ERROR` dans `ResponseBodyVO` |
| **Types** | Conversion automatique via `HydratableStructure::convertValue()` |
| **Enums** | `Enum::from()` pour les valeurs d'enum |

### Headers de sécurité recommandés via HeadersVO

```php
$headers = new HeadersVO();
$headers
    ->setStrictTransportSecurity('max-age=31536000')
    ->setXsrfToken($csrfToken)
    ->setAuthorization($token)
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON);
```

---

## 🧪 Tests

```bash
composer test
```

### Exemple de test

```php
public function test_get_users_success(): void
{
    $client = new ClientService();
    $request = new GetUserRequest();
    $request->setUserId(1);
    
    $response = $client->get(
        ApiEndpoint::USERS->withId(1)->getValue(),
        $request,
        UserResponse::class
    );
    
    $this->assertTrue($response->isSuccess());
    $this->assertSame(1, $response->getId());
}
```

---

## 📝 Licence

MIT © Andy Defer