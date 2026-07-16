# ClientService - Référence Technique

## Description

`ClientService` est le client HTTP principal du package. Il envoie des requêtes HTTP via Guzzle et retourne des réponses typées. Il gère l'hydratation automatique des réponses en structures PHP typées.

## Hiérarchie

```
ClientInterface
    └── ClientService
```

**Implémente :** `ClientInterface`

## Rôle principal

`ClientService` assure :

1. **Envoi** de requêtes HTTP (GET, POST, PUT, PATCH, DELETE)
2. **Hydratation** automatique des réponses en objets PHP typés
3. **Gestion** des headers, body et options
4. **Validation** des codes de statut HTTP
5. **Traitement** des erreurs Guzzle
6. **Généricité** via les templates PHP (`@template`)

---

## API / Méthodes publiques

### `__construct(?GuzzleClient $client = null)`

Initialise le client HTTP.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$client` | `GuzzleClient|null` | Client Guzzle personnalisé (optionnel) |

**Exceptions :** Aucune

**Exemple :**
```php
$client = new ClientService();

// Avec Guzzle personnalisé
$guzzle = new GuzzleClient(['base_uri' => 'https://api.example.com']);
$client = new ClientService($guzzle);
```

---

### `get(string $uri, Request $request, string $responseClass): Response`

Envoie une requête GET.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête (absolue ou relative) |
| `$request` | `Request` | Instance de la requête |
| `$responseClass` | `class-string<Response>` | Classe de réponse attendue |

**Retourne :** `Response` - Réponse typée

**Exceptions :** 
- `RuntimeException` si la requête Guzzle échoue
- `InvalidArgumentException` si la réponse est invalide

**Exemple :**
```php
$response = $client->get(
    'https://api.example.com/posts',
    $request,
    PostListResponse::class
);
```

---

### `post(string $uri, Request $request, string $responseClass): Response`

Envoie une requête POST.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Instance de la requête |
| `$responseClass` | `class-string<Response>` | Classe de réponse attendue |

**Retourne :** `Response` - Réponse typée

**Exceptions :** 
- `RuntimeException` si la requête Guzzle échoue
- `InvalidArgumentException` si la réponse est invalide

**Exemple :**
```php
$response = $client->post(
    'https://api.example.com/posts',
    $request,
    CreatePostResponse::class
);
```

---

### `put(string $uri, Request $request, string $responseClass): Response`

Envoie une requête PUT.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Instance de la requête |
| `$responseClass` | `class-string<Response>` | Classe de réponse attendue |

**Retourne :** `Response` - Réponse typée

---

### `patch(string $uri, Request $request, string $responseClass): Response`

Envoie une requête PATCH.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Instance de la requête |
| `$responseClass` | `class-string<Response>` | Classe de réponse attendue |

**Retourne :** `Response` - Réponse typée

---

### `delete(string $uri, Request $request, string $responseClass): Response`

Envoie une requête DELETE.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Instance de la requête |
| `$responseClass` | `class-string<Response>` | Classe de réponse attendue |

**Retourne :** `Response` - Réponse typée

---

## Méthodes privées

### `send(HttpMethod $method, string $uri, Request $request, string $responseClass): Response`

Envoie la requête HTTP et hydrate la réponse.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$method` | `HttpMethod` | Méthode HTTP |
| `$uri` | `string` | URI de la requête |
| `$request` | `Request` | Instance de la requête |
| `$responseClass` | `class-string<Response>` | Classe de réponse attendue |

**Retourne :** `Response` - Réponse typée

**Exceptions :** `RuntimeException` si la requête Guzzle échoue

---

### `buildOptions(Request $request): array`

Construit les options pour Guzzle.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$request` | `Request` | Instance de la requête |

**Retourne :** `array` - Options Guzzle

---

### `getStructClassFromResponse(string $responseClass): ?string`

Récupère la classe Struct associée à la réponse.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$responseClass` | `class-string<Response>` | Classe de réponse |

**Retourne :** `string|null` - Classe Struct ou null

---

## Cas d'utilisation

### Cas 1 : Requête GET avec headers

```php
$client = new ClientService();

$request = new GetPostsRequest();
$request->getHeaders()
    ->setAuthorization('token-123')
    ->setAccept(ContentType::JSON);

$response = $client->get(
    'https://api.example.com/posts',
    $request,
    PostListResponse::class
);

if ($response->isSuccess()) {
    $posts = $response->getPosts();
}
```

### Cas 2 : Requête POST avec corps JSON

```php
$client = new ClientService();

$request = new CreatePostRequest();
$request
    ->setTitle('Mon post')
    ->setContent('Contenu du post')
    ->setUserId(1);

$request->getHeaders()
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON);

$response = $client->post(
    'https://api.example.com/posts',
    $request,
    CreatePostResponse::class
);

if ($response->isSuccess()) {
    $created = $response->getCreatedPost();
    echo "Post créé avec ID: " . $created->id;
}
```

### Cas 3 : Requête avec options personnalisées

```php
$client = new ClientService();

$request = new GetPostsRequest();
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false)
    ->setVerify(true);

$response = $client->get(
    'https://api.example.com/posts',
    $request,
    PostListResponse::class
);
```

---

## Flux d'exécution

```
ClientService::get/post/put/patch/delete()
    ↓
send()
    ↓
buildOptions()
    ├── Headers → $options['headers']
    ├── Body → $options['body'] (si non vide)
    └── Options → array_merge()
    ↓
Guzzle::request()
    ├── Succès → Continue
    └── Erreur → RuntimeException
    ↓
ResponseBodyVO
    ├── content → Guzzle response body
    ├── contentType → Request body contentType
    └── structClass → getStructClassFromResponse()
    ↓
new $responseClass($statusCode, $body, $headers)
    ↓
Retourne la réponse typée
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Erreur Guzzle | `RuntimeException` | `HTTP request failed: X` |
| URL invalide | `InvalidArgumentException` | `Invalid URL: X` |
| JSON invalide | `InvalidArgumentException` | `Invalid JSON: X` |
| Classe de réponse invalide | `TypeError` | - |

---

## Intégration

### Avec Request

```php
$request = new GetPostsRequest();
$request->getHeaders()->setAuthorization('token');
$request->getOptions()->setTimeout(30);

$response = $client->get($uri, $request, ResponseClass::class);
```

### Avec Response

```php
$response = $client->get($uri, $request, PostListResponse::class);

if ($response->isSuccess()) {
    $data = $response->getPosts();
} else {
    $status = $response->getStatusCode();
}
```

### Avec les Value Objects

```php
// Headers
$request->getHeaders()
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON);

// Options
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10);

// Body
$body = $request->getBody();
$json = $body->toString();
```

---

## Performance

- **Envoi** : Délégation à Guzzle (performant)
- **Hydratation** : O(1) par paramètre
- **Options** : Construction à chaque requête
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

use AndyDefer\PhpClient\Clients\ClientService;
use AndyDefer\PhpClient\Enums\ContentType;

// 1. Créer le client
$client = new ClientService();

// 2. Configurer la requête
$request = new CreatePostRequest();
$request
    ->setTitle('Mon post')
    ->setContent('Contenu du post')
    ->setUserId(1);

$request->getHeaders()
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON)
    ->setAuthorization('token-123');

$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false);

// 3. Envoyer la requête
$response = $client->post(
    'https://jsonplaceholder.typicode.com/posts',
    $request,
    CreatePostResponse::class
);

// 4. Traiter la réponse
if ($response->isSuccess()) {
    $post = $response->getCreatedPost();
    echo "Post créé !\n";
    echo "ID: " . $post->id . "\n";
    echo "Titre: " . $post->title . "\n";
    echo "User ID: " . $post->user_id . "\n";
} else {
    echo "Erreur: " . $response->getStatusCode()->getPhrase() . "\n";
}
```

---

## Voir aussi

- `Request` - Requête HTTP
- `Response` - Réponse HTTP
- `HeadersVO` - En-têtes HTTP
- `OptionsVO` - Options HTTP
- `ResponseBodyVO` - Corps de réponse
- `HttpStatusCode` - Enum des codes de statut
- `GuzzleClient` - Client Guzzle sous-jacent