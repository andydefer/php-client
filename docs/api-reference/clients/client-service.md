# ClientService - Référence Technique

## Description

`ClientService` est un client HTTP qui implémente l'interface `ClientInterface`. Il encapsule GuzzleHttp pour envoyer des requêtes HTTP et retourner des réponses typées. Il gère la construction des options, l'hydratation des corps de réponse et la gestion des erreurs.

## Hiérarchie

```
ClientInterface
    └── ClientService
```

**Dépendance :** `GuzzleHttp\Client`

## Rôle principal

`ClientService` assure :

1. **Envoi** de requêtes HTTP (GET, POST, PUT, PATCH, DELETE)
2. **Construction** des options (headers, body, timeouts)
3. **Hydratation** automatique des réponses en `Response`
4. **Gestion** des erreurs HTTP et Guzzle
5. **Typage** générique des réponses

## API / Méthodes publiques

### `__construct(?GuzzleClient $client = null)`

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$client` | `?GuzzleClient` | Instance de Guzzle (défaut : nouvelle instance) |

**Exemple :**
```php
$client = new ClientService();
// ou avec une instance Guzzle personnalisée
$client = new ClientService(new GuzzleClient(['timeout' => 60]));
```

---

### `get(string $uri, Request $request, string $responseClass): Response`

Envoie une requête GET.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Objet requête |
| `$responseClass` | `class-string<TResponse>` | Classe de la réponse |

**Retourne :** `TResponse` - Réponse typée

**Exceptions :** `RuntimeException` si la requête échoue

**Exemple :**
```php
$response = $client->get(
    'https://api.example.com/users',
    $request,
    UserListResponse::class
);
```

---

### `post(string $uri, Request $request, string $responseClass): Response`

Envoie une requête POST.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Objet requête |
| `$responseClass` | `class-string<TResponse>` | Classe de la réponse |

**Retourne :** `TResponse` - Réponse typée

**Exceptions :** `RuntimeException` si la requête échoue

**Exemple :**
```php
$response = $client->post(
    'https://api.example.com/users',
    $request,
    CreateUserResponse::class
);
```

---

### `put(string $uri, Request $request, string $responseClass): Response`

Envoie une requête PUT.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Objet requête |
| `$responseClass` | `class-string<TResponse>` | Classe de la réponse |

**Retourne :** `TResponse` - Réponse typée

**Exceptions :** `RuntimeException` si la requête échoue

**Exemple :**
```php
$response = $client->put(
    'https://api.example.com/users/1',
    $request,
    UpdateUserResponse::class
);
```

---

### `patch(string $uri, Request $request, string $responseClass): Response`

Envoie une requête PATCH.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Objet requête |
| `$responseClass` | `class-string<TResponse>` | Classe de la réponse |

**Retourne :** `TResponse` - Réponse typée

**Exceptions :** `RuntimeException` si la requête échoue

**Exemple :**
```php
$response = $client->patch(
    'https://api.example.com/users/1',
    $request,
    PatchUserResponse::class
);
```

---

### `delete(string $uri, Request $request, string $responseClass): Response`

Envoie une requête DELETE.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Objet requête |
| `$responseClass` | `class-string<TResponse>` | Classe de la réponse |

**Retourne :** `TResponse` - Réponse typée

**Exceptions :** `RuntimeException` si la requête échoue

**Exemple :**
```php
$response = $client->delete(
    'https://api.example.com/users/1',
    $request,
    DeleteUserResponse::class
);
```

---

## Cas d'utilisation

### Cas 1 : Requête GET avec headers personnalisés

```php
$request = new GetPokemonRequest();
$request->setLimit(20);
$request->getHeaders()
    ->setAccept(ContentType::JSON)
    ->setAuthorization('token123');

$response = $client->get(
    'https://pokeapi.co/api/v2/pokemon',
    $request,
    PokemonListResponse::class
);

if ($response->isSuccess()) {
    $pokemons = $response->getPokemons();
}
```

### Cas 2 : Requête POST avec corps JSON

```php
$request = new CreateUserRequest();
$request->setName('John Doe')->setEmail('john@example.com');

$response = $client->post(
    'https://api.example.com/users',
    $request,
    CreateUserResponse::class
);

if ($response->isSuccess()) {
    $userId = $response->getUserId();
}
```

### Cas 3 : Gestion des erreurs

```php
try {
    $response = $client->get(
        'https://api.example.com/unknown',
        $request,
        ErrorResponse::class
    );
} catch (RuntimeException $e) {
    echo 'HTTP request failed: ' . $e->getMessage();
}
```

---

## Flux d'exécution

```
ClientService::get/post/put/patch/delete()
    ↓
send(HttpMethod, $uri, $request, $responseClass)
    ↓
buildOptions($request)
    ├── headers → $options['headers']
    ├── body → $options['body'] (si non vide)
    └── options → array_merge($options, $request->getOptions())
    ↓
GuzzleClient::request($method, $uri, $options)
    ↓
    ├── Succès → GuzzleResponse
    │   ↓
    │   HttpStatusCode::tryFrom()
    │   ↓
    │   new ResponseBodyVO($content, $structClass, $contentType)
    │   ↓
    │   new $responseClass($statusCode, $body, $headers)
    │
    └── GuzzleException → RuntimeException
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Erreur Guzzle | `RuntimeException` | `HTTP request failed: X` |
| Réponse invalide | `InvalidArgumentException` | Varie selon `ResponseBodyVO` |
| Classe de réponse invalide | `TypeError` | - |

## Intégration

### Avec Request

```php
$request = new GetPokemonRequest();
$request->getHeaders()->setAuthorization('token');
```

### Avec Response

```php
$response = $client->get($uri, $request, PokemonListResponse::class);
$pokemons = $response->getPokemons();
```

### Avec Guzzle

```php
// ClientService utilise Guzzle en interne
$client = new ClientService(new GuzzleClient([
    'base_uri' => 'https://api.example.com',
    'timeout' => 30
]));
```

## Performance

- Encapsulation légère de Guzzle
- Pas de cache interne
- `buildOptions()` O(1)
- Hydratation des réponses O(n) pour les collections

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.2+ | ✅ Complet |
| Guzzle 7+ | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpStatusCode;

// 1. Créer le client
$client = new ClientService();

// 2. Créer la requête
$request = new GetPokemonRequest();
$request
    ->setLimit(20)
    ->getHeaders()
    ->setAccept(ContentType::JSON)
    ->setAuthorization('your-token');

$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10);

// 3. Envoyer la requête
$response = $client->get(
    'https://pokeapi.co/api/v2/pokemon',
    $request,
    PokemonListResponse::class
);

// 4. Traiter la réponse
if ($response->isSuccess()) {
    $pokemons = $response->getPokemons();
    $count = $response->getCount();
    echo "Found {$count} Pokémon\n";
    
    foreach ($pokemons as $pokemon) {
        echo "- {$pokemon->name}\n";
    }
} else {
    echo "Error: " . $response->getStatusCode()->value;
}

// 5. Requête POST
$createRequest = new CreateUserRequest();
$createRequest
    ->setName('John Doe')
    ->setEmail('john@example.com')
    ->getHeaders()->setContentType(ContentType::JSON);

$createResponse = $client->post(
    'https://api.example.com/users',
    $createRequest,
    CreateUserResponse::class
);

if ($createResponse->isSuccess()) {
    echo "User created: " . $createResponse->getUserId();
}
```

## Voir aussi

- `Request` - Requête HTTP
- `Response` - Réponse HTTP
- `HeadersVO` - En-têtes HTTP
- `OptionsVO` - Options HTTP
- `ResponseBodyVO` - Corps de réponse
- `HttpMethod` - Enum des méthodes HTTP
- `HttpStatusCode` - Enum des codes de statut
---