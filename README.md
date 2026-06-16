# PHP HTTP Client - Documentation Complète

## 📖 Introduction

**PHP HTTP Client** est une bibliothèque PHP moderne qui simplifie les appels HTTP en offrant une **API type-safe, immutable et hautement configurable**. Elle repose sur GuzzleHttp mais ajoute une couche d'abstraction qui transforme les appels HTTP bruts en objets métier typés.

---

## 🎯 Problèmes résolus

### 1. **Manque de typage dans les appels HTTP**
```php
// ❌ Sans cette bibliothèque
$response = $client->get('https://api.example.com/users');
$data = json_decode($response->getBody(), true);
$userName = $data['data']['user']['name'] ?? null; // Fragile, non typé
```

```php
// ✅ Avec PHP HTTP Client
$response = $client->get('/users', $request, UserListResponse::class);
$users = $response->getUsers(); // Typé, sûr, auto-complétion
```

### 2. **Validation et hydratation automatique**
Les réponses API sont automatiquement hydratées en objets PHP typés, avec conversion des types (string → int, string → Enum, array → Collection).

### 3. **Immutabilité et type-safety**
Toutes les structures sont immutables (`readonly`), ce qui garantit l'intégrité des données.

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
┌─────────────────────────────────────────────────────────────┐
│                        ClientService                        │
│  (Envoi des requêtes, gestion des options, hydratation)     │
└────────────────────┬────────────────────────────────────────┘
                     │
      ┌──────────────┴──────────────┐
      │                             │
┌─────▼─────┐              ┌────────▼────────┐
│  Request  │              │    Response     │
│ (Abstrait)│              │   (Abstrait)    │
└─────┬─────┘              └────────┬────────┘
      │                             │
┌─────▼─────┐              ┌────────▼────────┐
│  Concrete │              │   Concrete      │
│  Request  │              │   Response      │
└───────────┘              └─────────────────┘
```

---

## 📦 Composants principaux

### 1. Request - Requête HTTP

Les `Request` encapsulent une requête HTTP complète : méthode, URL, corps, headers et options.

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
        $url = 'https://api.example.com/users';
        if ($this->userId !== null) {
            return new UrlVO($url . '/' . $this->userId);
        }
        return new UrlVO($url);
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new class extends Struct {};
        return new RequestBodyVO($struct, ContentType::JSON);
    }
}
```

### 2. Response - Réponse HTTP

Les `Response` encapsulent une réponse HTTP : status code, body et headers.

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

### 3. ClientService - Client HTTP

Le `ClientService` envoie les requêtes et retourne des réponses typées.

```php
use AndyDefer\PhpClient\Clients\ClientService;

$client = new ClientService();

$response = $client->get(
    'https://api.example.com/users',
    $request,
    UserListResponse::class
);
```

---

## 🔧 Value Objects

### HeadersVO - Gestion des en-têtes

```php
use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HeaderType;

$headers = new HeadersVO();
$headers
    ->setAuthorization('token123')
    ->setContentType(ContentType::JSON)
    ->setHost('api.example.com');

// Accès
$auth = $headers->get(HeaderType::AUTHORIZATION); // 'Bearer token123'
$hasContentType = $headers->has(HeaderType::CONTENT_TYPE); // true
```

### OptionsVO - Options de configuration

```php
use AndyDefer\PhpClient\ValueObjects\OptionsVO;
use AndyDefer\PhpClient\Enums\OptionType;

$options = new OptionsVO();
$options
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(true);
```

### RequestBodyVO - Corps de requête

```php
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;

$struct = new UserCreateStruct(...);
$body = new RequestBodyVO($struct, ContentType::JSON);

$json = $body->toString(); // '{"name":"John","email":"..."}'
$array = $body->toArray(); // ['name' => 'John', 'email' => '...']
```

### ResponseBodyVO - Corps de réponse

```php
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;

$body = new ResponseBodyVO(
    $jsonResponse,
    UserListStruct::class,
    ContentType::JSON
);

$struct = $body->getValue(); // UserListStruct hydraté
$formatted = $body->format(); // stdClass ou array
```

---

## 📝 Enums disponibles

| Enum | Description |
|------|-------------|
| `HttpMethod` | GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS |
| `HttpStatusCode` | Tous les codes HTTP (100-599) |
| `ContentType` | JSON, JSON_UTF8, PROBLEM_JSON, FORM |
| `ContentEncoding` | GZIP, DEFLATE, BR, ZSTD, IDENTITY |
| `ConnectionType` | KEEP_ALIVE, CLOSE, UPGRADE |
| `CacheControl` | NO_CACHE, NO_STORE, MAX_AGE, etc. |
| `OptionType` | TIMEOUT, CONNECT_TIMEOUT, VERIFY, etc. |
| `HeaderType` | HOST, USER_AGENT, ACCEPT, AUTHORIZATION, etc. |

---

## 💡 Cas d'utilisation

### Cas 1 : Requête GET avec headers personnalisés

```php
use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;

$client = new ClientService();

$request = new GetUserRequest();
$request->setUserId(123);

$request->getHeaders()
    ->setAccept(ContentType::JSON)
    ->setAuthorization('your-token');

$response = $client->get(
    'https://api.example.com/users',
    $request,
    UserListResponse::class
);

if ($response->isSuccess()) {
    $users = $response->getUsers();
    $total = $response->getTotal();
    echo "Found {$total} users\n";
} else {
    echo "Error: " . $response->getStatusCode()->value;
}
```

### Cas 2 : Requête POST avec corps JSON et options

```php
$request = new CreateUserRequest();
$request
    ->setName('John Doe')
    ->setEmail('john@example.com');

// Ajouter les headers
$request->getHeaders()
    ->setContentType(ContentType::JSON)
    ->setAuthorization('your-api-token')
    ->setAccept(ContentType::JSON);

// Ajouter les options
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false)
    ->setVerify(true);

$response = $client->post(
    'https://api.example.com/users',
    $request,
    CreateUserResponse::class
);

if ($response->isSuccess()) {
    $userId = $response->getUserId();
    echo "User created with ID: " . $userId;
} else {
    $statusCode = $response->getStatusCode();
    echo "Request failed with status: " . $statusCode->value;
}
```

### Cas 3 : Configuration avancée du client

```php
use GuzzleHttp\Client as GuzzleClient;

// Client avec base URI et timeout
$guzzle = new GuzzleClient([
    'base_uri' => 'https://api.example.com',
    'timeout' => 30,
    'verify' => false
]);

$client = new ClientService($guzzle);

// Toutes les requêtes utiliseront base_uri
$response = $client->get(
    '/v2/users', // URL relative
    $request,
    UserListResponse::class
);
```

---

## 🔧 Options disponibles

### Headers
```php
$request->getHeaders()
    ->setContentType(ContentType::JSON)        // Content-Type
    ->setAuthorization('token123')             // Authorization: Bearer token123
    ->setAccept(ContentType::JSON)             // Accept
    ->setHost('api.example.com')               // Host
    ->setUserAgent('PHP/8.2')                  // User-Agent
    ->setAcceptEncoding(ContentEncoding::GZIP) // Accept-Encoding
    ->setCacheControl(CacheControl::NO_CACHE)  // Cache-Control
    ->setXRequestId('req-123-456')             // X-Request-Id
    ->setCustom('X-Custom-Header', 'value');   // Custom header
```

### Options de transfert
```php
$request->getOptions()
    ->setTimeout(30)                  // Délai d'attente global
    ->setConnectTimeout(10)           // Délai de connexion
    ->setHttpErrors(false)            // Ne pas lancer d'exception sur erreur HTTP
    ->setVerify(true)                 // Vérification SSL
    ->setAllowRedirects(false)        // Suivre les redirections
    ->setMaxRedirects(5)              // Nombre max de redirections
    ->setDebug(true);                 // Mode debug
```

### Options de proxy
```php
$request->getOptions()
    ->setProxy('tcp://localhost:8080')                    // Proxy simple
    ->setProxy(['http' => 'tcp://localhost:8080'])       // Proxy HTTP/HTTPS
    ->setNoProxy(['.example.com', '.test.com']);         // Exclure du proxy
```

### Options d'authentification
```php
$request->getOptions()
    ->setAuth(['username', 'password'])                  // Basic auth
    ->setAuth(['username', 'password', 'digest'])        // Digest auth
    ->setCert('/path/to/cert.pem')                       // Certificat SSL
    ->setCert(['/path/to/cert.pem', 'password'])         // Certificat avec mot de passe
    ->setSslKey('/path/to/key.pem');                     // Clé SSL
```

### Options d'environnement
```php
$request->getOptions()
    ->setBaseUri('https://api.example.com')              // Base URI
    ->setQuery(['page' => 1, 'limit' => 10])             // Query params
    ->setDecodeContent(true)                             // Décoder le contenu
    ->setForceIpResolve('v4');                           // Résolution IPv4/v6
```

### Options de logging
```php
$request->getOptions()
    ->setOnStats(function($stats) {
        echo $stats->getTransferTime();
    });
```

## Exemple complet avec toutes les options

```php
$request = new CreateUserRequest();
$request
    ->setName('John Doe')
    ->setEmail('john@example.com');

// Ajouter les headers
$request->getHeaders()
    ->setContentType(ContentType::JSON)
    ->setAuthorization('token123')
    ->setAccept(ContentType::JSON)
    ->setHost('api.example.com')
    ->setUserAgent('MyApp/1.0')
    ->setAcceptEncoding(ContentEncoding::GZIP)
    ->setCacheControl(CacheControl::NO_CACHE)
    ->setXRequestId('req-' . uniqid());

// Ajouter les options
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false)
    ->setVerify(true)
    ->setAllowRedirects(false)
    ->setProxy('tcp://proxy.example.com:8080')
    ->setAuth(['username', 'password'])
    ->setQuery(['format' => 'json']);

$response = $client->post(
    'https://api.example.com/users',
    $request,
    CreateUserResponse::class
);
```

---

## 📊 Gestion des erreurs

### Exceptions levées

| Situation | Exception | Message |
|-----------|-----------|---------|
| URL invalide | `InvalidArgumentException` | `Invalid URL: X` |
| JSON invalide | `InvalidArgumentException` | `Invalid JSON: Syntax error` |
| Paramètre requis manquant | `InvalidArgumentException` | `Missing required parameters for X: $Y` |
| Erreur Guzzle | `RuntimeException` | `HTTP request failed: X` |
| Classe de réponse invalide | `TypeError` | - |

### Bonnes pratiques

```php
try {
    $response = $client->get($uri, $request, $responseClass);
    
    if ($response->isError()) {
        $statusCode = $response->getStatusCode();
        // Log, retry, fallback...
        return;
    }
    
    // Traitement du succès
    $data = $response->getData();
    
} catch (InvalidArgumentException $e) {
    // Erreur de validation des données
    echo "Invalid data: " . $e->getMessage();
    
} catch (RuntimeException $e) {
    // Erreur HTTP
    echo "HTTP error: " . $e->getMessage();
}
```

---

## 🎯 Bonnes pratiques

### 1. Utiliser les headers dans des blocs séparés

```php
// ✅ Bon - Headers et options séparés
$request->getHeaders()
    ->setAuthorization('token')
    ->setContentType(ContentType::JSON);

$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10);
```

### 2. Structurer les réponses

```php
// ✅ Bon - Structure claire
final class UserListResponse extends Response
{
    public function getUsers(): array { ... }
    public function getTotal(): int { ... }
}

// ❌ Mauvais - Accès direct aux données
$data = $response->getBody()->format();
echo $data['users'][0]['name'];
```

### 3. Utiliser les enums

```php
// ✅ Bon - Enum pour les valeurs fixes
$request->getHeaders()->setContentType(ContentType::JSON);

// ❌ Mauvais - String magique
$request->getHeaders()->setCustom('Content-Type', 'application/json');
```

---

## 🔒 Sécurité

### Validation automatique

- **JSON** : Validation et parsing automatique
- **URL** : Validation `FILTER_VALIDATE_URL`
- **Enums** : Conversion automatique avec `Enum::from()`

### Headers de sécurité

```php
$headers = new HeadersVO();
$headers
    ->setStrictTransportSecurity('max-age=31536000')
    ->setXsrfToken($csrfToken)
    ->setAuthorization($token);
```

---

## 🧪 Tests

```bash
composer test
```

---

## 📝 Licence

MIT © Andy Kani

---