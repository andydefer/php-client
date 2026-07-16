# Response - Référence Technique

## Description

`Response` est une classe abstraite qui représente une réponse HTTP. Elle encapsule le code de statut, le corps et les en-têtes d'une réponse HTTP de manière immutable. Elle sert de base pour toutes les réponses spécifiques à une API.

## Hiérarchie

```
ResponseInterface
    └── Response (abstract)
            ├── PokemonListResponse
            ├── CommentListResponse
            ├── CreatePostResponse
            └── ...
```

**Implémente :** `ResponseInterface`

## Rôle principal

`Response` assure :

1. **Encapsulation** des composants d'une réponse HTTP
2. **Immutabilité** totale (toutes les propriétés sont `readonly`)
3. **Validation** du statut via `isSuccess()` et `isError()`
4. **Base** pour toutes les réponses spécifiques
5. **Accès** type-safe aux composants

---

## API / Méthodes publiques

### `__construct(HttpStatusCode $statusCode, ResponseBodyVO $body, HeadersVO $headers)`

Initialise la réponse avec ses composants.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$statusCode` | `HttpStatusCode` | Code de statut HTTP |
| `$body` | `ResponseBodyVO` | Corps de la réponse |
| `$headers` | `HeadersVO` | En-têtes HTTP |

**Exceptions :** Aucune

**Exemple :**
```php
$response = new PokemonListResponse(
    HttpStatusCode::OK,
    new ResponseBodyVO($json, PokemonListStruct::class),
    new HeadersVO()
);
```

---

### `getStatusCode(): HttpStatusCode`

Retourne le code de statut HTTP.

**Retourne :** `HttpStatusCode` - Code de statut

**Exemple :**
```php
$statusCode = $response->getStatusCode(); // HttpStatusCode::OK
echo $statusCode->value; // 200
```

---

### `getBody(): ResponseBodyVO`

Retourne le corps de la réponse.

**Retourne :** `ResponseBodyVO` - Corps de la réponse

**Exemple :**
```php
$body = $response->getBody();
$struct = $body->getValue(); // Structure hydratée
```

---

### `getHeaders(): HeadersVO`

Retourne les en-têtes de la réponse.

**Retourne :** `HeadersVO` - En-têtes HTTP

**Exemple :**
```php
$headers = $response->getHeaders();
$contentType = $headers->get(HeaderType::CONTENT_TYPE);
```

---

### `isSuccess(): bool`

Vérifie si la réponse est un succès (code 2xx).

**Retourne :** `bool` - `true` si code 2xx

**Exemple :**
```php
if ($response->isSuccess()) {
    // Traitement du succès
}
```

---

### `isError(): bool`

Vérifie si la réponse est une erreur (code 4xx ou 5xx).

**Retourne :** `bool` - `true` si code 4xx ou 5xx

**Exemple :**
```php
if ($response->isError()) {
    // Traitement de l'erreur
}
```

---

## Cas d'utilisation

### Cas 1 : Réponse de succès avec données

```php
final class PokemonListResponse extends Response
{
    public function getPokemons(): PokemonCollection
    {
        $struct = $this->getBody()->getValue();
        return $struct->results;
    }

    public function getCount(): int
    {
        $struct = $this->getBody()->getValue();
        return $struct->count;
    }
}

// Utilisation
$response = new PokemonListResponse(
    HttpStatusCode::OK,
    new ResponseBodyVO($json, PokemonListStruct::class),
    new HeadersVO()
);

if ($response->isSuccess()) {
    $pokemons = $response->getPokemons();
    $count = $response->getCount();
}
```

### Cas 2 : Réponse d'erreur avec Problem JSON

```php
final class ErrorResponse extends Response
{
    public function getErrorType(): string
    {
        $data = $this->getBody()->format();
        return $data['type'] ?? 'unknown';
    }

    public function getErrorMessage(): string
    {
        $data = $this->getBody()->format();
        return $data['title'] ?? 'Unknown error';
    }
}

// Utilisation
$response = new ErrorResponse(
    HttpStatusCode::BAD_REQUEST,
    new ResponseBodyVO($json, PokemonListStruct::class, ContentType::PROBLEM_JSON),
    new HeadersVO()
);

if ($response->isError()) {
    echo $response->getErrorMessage();
}
```

### Cas 3 : Accès aux headers

```php
$response = new PokemonListResponse(
    HttpStatusCode::OK,
    $body,
    $headers
);

$contentType = $response->getHeaders()->get(HeaderType::CONTENT_TYPE);
$contentLength = $response->getHeaders()->get(HeaderType::CONTENT_LENGTH);
```

---

## Flux d'exécution

```
new Response($statusCode, $body, $headers)
    ↓
Propriétés readonly
    ├── statusCode → HttpStatusCode
    ├── body → ResponseBodyVO
    └── headers → HeadersVO
    ↓
getStatusCode() → HttpStatusCode
getBody() → ResponseBodyVO
getHeaders() → HeadersVO
    ↓
isSuccess() → bool (2xx)
isError() → bool (4xx ou 5xx)
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Body invalide | `InvalidArgumentException` | Varie selon `ResponseBodyVO` |
| Headers invalides | Aucune | - |

---

## Intégration

### Avec le Client

```php
$response = $client->post('/v2/deposits', $request, InitiateDepositResponse::class);

if ($response->isSuccess()) {
    $deposit = $response->getDeposit();
} else {
    $error = $response->getFailureReason();
}
```

### Avec les Structures

```php
$struct = $response->getBody()->getValue(); // Struct hydraté
$array = $response->getBody()->toArray(); // Tableau
$object = $response->getBody()->format(); // stdClass
```

### Avec ResponseBodyVO

```php
// Hydratation automatique
$response = new PokemonListResponse(
    HttpStatusCode::OK,
    new ResponseBodyVO($json, PokemonListStruct::class),
    new HeadersVO()
);

// Accès direct à la structure
$struct = $response->getBody()->getValue(); // PokemonListStruct
```

---

## Performance

- **Construction** : O(1)
- **Toutes les propriétés** : `readonly` (immutable)
- **Pas de copie profonde**
- **`isSuccess()` et `isError()`** : O(1)

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

use AndyDefer\PhpClient\Abstracts\Response;
use AndyDefer\PhpClient\Enums\HttpStatusCode;
use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;
use AndyDefer\PhpClient\ValueObjects\HeadersVO;

// 1. Définir une réponse concrète
final class CreateUserResponse extends Response
{
    public function getUserId(): string
    {
        $struct = $this->getBody()->getValue();
        return $struct->id;
    }

    public function getStatus(): string
    {
        $struct = $this->getBody()->getValue();
        return $struct->status;
    }

    public static function getStructClass(): string
    {
        return CreateUserStruct::class;
    }
}

// 2. Utilisation avec succès
$json = '{"id":"123","status":"created"}';
$body = new ResponseBodyVO($json, CreateUserStruct::class);
$headers = new HeadersVO();
$headers->setContentType(ContentType::JSON);

$response = new CreateUserResponse(
    HttpStatusCode::CREATED,
    $body,
    $headers
);

// 3. Vérifications
if ($response->isSuccess()) {
    echo "User created: " . $response->getUserId();
    echo "Status: " . $response->getStatus();
}

// 4. Accès aux composants
$statusCode = $response->getStatusCode(); // HttpStatusCode::CREATED
$responseBody = $response->getBody(); // ResponseBodyVO
$responseHeaders = $response->getHeaders(); // HeadersVO

// 5. Formatage du body
$formatted = $response->getBody()->format(); // stdClass
$array = $response->getBody()->toArray(); // array

// 6. Réponse d'erreur
$errorJson = '{"type":"https://example.com/errors","title":"Invalid input"}';
$errorBody = new ResponseBodyVO($errorJson, CreateUserStruct::class, ContentType::PROBLEM_JSON);

$errorResponse = new CreateUserResponse(
    HttpStatusCode::BAD_REQUEST,
    $errorBody,
    new HeadersVO()
);

if ($errorResponse->isError()) {
    // Gestion de l'erreur
    echo "Error: " . $errorResponse->getStatus();
}
```

---

## Voir aussi

- `Request` - Requête HTTP
- `ResponseBodyVO` - Corps de réponse
- `HeadersVO` - En-têtes HTTP
- `HttpStatusCode` - Enum des codes de statut
- `ResponseInterface` - Interface de réponse