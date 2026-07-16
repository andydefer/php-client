# Request - Référence Technique

## Description

`Request` est une classe abstraite qui représente une requête HTTP. Elle encapsule tous les composants d'une requête : méthode HTTP, URL, corps, en-têtes et options. Elle sert de base pour toutes les requêtes spécifiques à une API.

## Hiérarchie

```
RequestInterface
    └── Request (abstract)
            ├── GetPostsRequest
            ├── CreatePostRequest
            ├── GetCommentRequest
            └── ...
```

**Implémente :** `RequestInterface`

## Rôle principal

`Request` assure :

1. **Encapsulation** de tous les composants d'une requête HTTP
2. **Configuration** via des méthodes abstraites
3. **Accès immuable** aux composants (méthode, URL, corps)
4. **Mutabilité** des en-têtes et options (via les Value Objects)
5. **Reconstruction dynamique** du corps à chaque appel de `getBody()`
6. **Base** pour toutes les requêtes spécifiques

---

## API / Méthodes publiques

### `__construct()`

Initialise les composants de la requête.

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Exceptions :** Aucune

**Exemple :**
```php
$request = new GetPostsRequest();
```

---

### `getMethod(): HttpMethod`

Retourne la méthode HTTP.

**Retourne :** `HttpMethod` - Méthode HTTP (GET, POST, PUT, etc.)

**Exemple :**
```php
$method = $request->getMethod(); // HttpMethod::GET
```

---

### `getUrl(): UrlVO`

Retourne l'URL de la requête.

**Retourne :** `UrlVO` - URL de la requête

**Exemple :**
```php
$url = $request->getUrl(); // UrlVO
echo $url->getValue(); // 'https://jsonplaceholder.typicode.com/posts'
```

---

### `getBody(): RequestBodyVO`

Retourne le corps de la requête. **Reconstruit le corps à chaque appel** pour garantir que les modifications apportées après la construction soient prises en compte.

**Retourne :** `RequestBodyVO` - Corps de la requête

**Exemple :**
```php
$body = $request->getBody();
$json = $body->toString(); // '{"title":"Mon post","body":"...","userId":1}'
```

---

### `getHeaders(): HeadersVO`

Retourne les en-têtes de la requête.

**Retourne :** `HeadersVO` - En-têtes HTTP

**Exemple :**
```php
$headers = $request->getHeaders();
$headers->setAuthorization('token');
$headers->setContentType(ContentType::JSON);
```

---

### `getOptions(): OptionsVO`

Retourne les options de la requête.

**Retourne :** `OptionsVO` - Options de configuration

**Exemple :**
```php
$options = $request->getOptions();
$options->setTimeout(30);
$options->setConnectTimeout(10);
```

---

## Méthodes abstraites

### `setMethod(): HttpMethod`

Définit la méthode HTTP.

**Retourne :** `HttpMethod` - Méthode HTTP à utiliser

**Exemple :**
```php
protected function setMethod(): HttpMethod
{
    return HttpMethod::POST;
}
```

---

### `setUrl(): UrlVO`

Définit l'URL de la requête.

**Retourne :** `UrlVO` - URL de la requête

**Exemple :**
```php
protected function setUrl(): UrlVO
{
    return PlaceholderEndpoint::POSTS->getUrl();
}
```

---

### `setBody(): RequestBodyVO`

Définit le corps de la requête.

**Retourne :** `RequestBodyVO` - Corps de la requête

**Exemple :**
```php
protected function setBody(): RequestBodyVO
{
    $struct = new CreatePostStruct(
        title: $this->title,
        body: $this->content,
        userId: $this->userId
    );

    return new RequestBodyVO($struct, ContentType::JSON);
}
```

---

## Cas d'utilisation

### Cas 1 : Requête GET simple

```php
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

// Utilisation
$request = new GetPostsRequest();
$response = $client->get($request->getUrl()->getValue(), $request, PostListResponse::class);
```

### Cas 2 : Requête POST avec corps dynamique

```php
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

// Utilisation
$request = new CreatePostRequest();
$request
    ->setTitle('Mon post')
    ->setContent('Contenu du post')
    ->setUserId(1);

// Le corps est reconstruit dynamiquement avec les valeurs définies
$response = $client->post($request->getUrl()->getValue(), $request, CreatePostResponse::class);
```

### Cas 3 : Ajout d'en-têtes et options

```php
$request = new CreatePostRequest();
$request
    ->setTitle('Mon post')
    ->setContent('Contenu du post')
    ->setUserId(1);

// Ajout des en-têtes
$request->getHeaders()
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON)
    ->setAuthorization('token-123');

// Ajout des options
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false);
```

---

## Flux d'exécution

```
new Request()
    ↓
__construct()
    ├── new HeadersVO()
    ├── new OptionsVO()
    ├── $this->setMethod() → HttpMethod
    ├── $this->setUrl() → UrlVO
    └── $this->setBody() → RequestBodyVO (première construction)
    ↓
getMethod() → HttpMethod (immuable)
getUrl() → UrlVO (immuable)
getHeaders() → HeadersVO (modifiable)
getOptions() → OptionsVO (modifiable)
    ↓
getBody() → RequestBodyVO (reconstruit dynamiquement à chaque appel)
    ├── $this->setBody()
    └── retourne le nouveau body
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| URL invalide | `InvalidArgumentException` | `Invalid URL: X` |
| Body invalide | `InvalidArgumentException` | Varie selon le contexte |
| Options invalides | `InvalidArgumentException` | Varie selon le contexte |

---

## Intégration

### Avec le Client

```php
$client = new ClientService();
$response = $client->post(
    $request->getUrl()->getValue(),
    $request,
    CreatePostResponse::class
);
```

### Avec les Value Objects

```php
// Headers
$request->getHeaders()
    ->setContentType(ContentType::JSON)
    ->setAuthorization('token');

// Options
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10);

// Url
$url = $request->getUrl();
$baseUrl = $url->getBaseUrl();
$path = $url->getPath();
$query = $url->getQuery();
```

### Avec les Structures

```php
$struct = $request->getBody()->getStruct(); // Struct
$json = $request->getBody()->toString(); // JSON string
```

---

## Performance

- **Construction** : O(1)
- **`getBody()`** : Reconstruit le corps à chaque appel (O(1) - création d'objet)
- **`getHeaders()` et `getOptions()`** : Retournent des références modifiables
- **Pas de cache**

---

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.2+ | ✅ Complet |

---

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

// 1. Définir une requête
final class CreateUserRequest extends Request
{
    private string $name;
    private string $email;

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    protected function setUrl(): UrlVO
    {
        return new UrlVO('https://api.example.com/v2/users');
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new class($this->name, $this->email) extends Struct {
            public function __construct(
                public readonly string $name,
                public readonly string $email,
            ) {}
        };

        return new RequestBodyVO(new $struct($this->name, $this->email), ContentType::JSON);
    }
}

// 2. Utilisation
$request = new CreateUserRequest();
$request
    ->setName('John Doe')
    ->setEmail('john@example.com');

// Ajout des en-têtes
$request->getHeaders()
    ->setAuthorization('token-xyz')
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON);

// Ajout des options
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false);

// Accès aux composants
$method = $request->getMethod(); // HttpMethod::POST
$url = $request->getUrl(); // UrlVO
$body = $request->getBody(); // RequestBodyVO (reconstruit dynamiquement)
$headers = $request->getHeaders(); // HeadersVO
$options = $request->getOptions(); // OptionsVO

// Envoi de la requête
$client = new ClientService();
$response = $client->post(
    $url->getValue(),
    $request,
    CreateUserResponse::class
);

// Traitement de la réponse
if ($response->isSuccess()) {
    echo "User created: " . $response->getUserId();
}
```

---

## Voir aussi

- `Response` - Réponse HTTP
- `RequestBodyVO` - Corps de requête
- `HeadersVO` - En-têtes HTTP
- `OptionsVO` - Options HTTP
- `UrlVO` - URL
- `HttpMethod` - Enum des méthodes HTTP
- `ClientService` - Client HTTP