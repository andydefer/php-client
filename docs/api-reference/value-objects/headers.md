# HeadersVO - Référence Technique

## Description

`HeadersVO` est un Value Object qui gère les en-têtes HTTP de manière immutable. Il fournit des méthodes type-safe pour définir les en-têtes les plus courants (authentification, contenu, cache, sécurité, etc.) ainsi qu'une méthode générique pour les en-têtes personnalisés.

## Hiérarchie

```
AbstractValueObject
    └── HeadersVO
```

**Étend :** `AbstractValueObject`

## Rôle principal

`HeadersVO` assure :

1. **Gestion type-safe** des en-têtes HTTP via des énumérations
2. **Méthodes dédiées** pour les en-têtes courants (Authorization, Content-Type, etc.)
3. **Support** des en-têtes d'authentification (Bearer, Basic, API Key)
4. **Support** des en-têtes de sécurité (CSRF, HSTS)
5. **Conversion** en tableau pour l'intégration avec Guzzle

---

## API / Méthodes publiques

### `setHost(string $host): self`

Définit l'en-tête `Host`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$host` | `string` | Hôte (ex: 'api.example.com') |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$headers->setHost('api.example.com');
```

---

### `setUserAgent(string $userAgent): self`

Définit l'en-tête `User-Agent`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$userAgent` | `string` | User-Agent (ex: 'PHP/8.2') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setAccept(ContentType $accept): self`

Définit l'en-tête `Accept`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$accept` | `ContentType` | Type de contenu accepté |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$headers->setAccept(ContentType::JSON);
```

---

### `setAcceptLanguage(string $acceptLanguage): self`

Définit l'en-tête `Accept-Language`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$acceptLanguage` | `string` | Langue acceptée (ex: 'fr-FR') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setAcceptEncoding(ContentEncoding $acceptEncoding): self`

Définit l'en-tête `Accept-Encoding`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$acceptEncoding` | `ContentEncoding` | Encodage accepté |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setConnection(ConnectionType $connection): self`

Définit l'en-tête `Connection`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$connection` | `ConnectionType` | Type de connexion |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setAuthorization(string $token): self`

Définit l'en-tête `Authorization` avec un token Bearer.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$token` | `string` | Token d'authentification |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$headers->setAuthorization('eyJhbGciOiJIUzI1NiIs...');
// Résultat: Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
```

---

### `setBasicAuth(string $username, string $password): self`

Définit l'en-tête `Authorization` avec Basic Auth.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$username` | `string` | Nom d'utilisateur |
| `$password` | `string` | Mot de passe |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$headers->setBasicAuth('user', 'pass');
// Résultat: Authorization: Basic dXNlcjpwYXNz
```

---

### `setApiKey(string $apiKey): self`

Définit l'en-tête `X-API-Key`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$apiKey` | `string` | Clé API |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setCookie(string $cookie): self`

Définit l'en-tête `Cookie`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$cookie` | `string` | Cookie (ex: 'sessionId=xyz') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setContentType(ContentType $contentType): self`

Définit l'en-tête `Content-Type`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$contentType` | `ContentType` | Type de contenu |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$headers->setContentType(ContentType::JSON);
// Résultat: Content-Type: application/json
```

---

### `setContentLength(int $contentLength): self`

Définit l'en-tête `Content-Length`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$contentLength` | `int` | Longueur du contenu en octets |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setContentEncoding(ContentEncoding $contentEncoding): self`

Définit l'en-tête `Content-Encoding`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$contentEncoding` | `ContentEncoding` | Encodage du contenu |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setContentLanguage(string $contentLanguage): self`

Définit l'en-tête `Content-Language`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$contentLanguage` | `string` | Langue du contenu |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setCacheControl(CacheControl $cacheControl): self`

Définit l'en-tête `Cache-Control`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$cacheControl` | `CacheControl` | Contrôle de cache |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setIfModifiedSince(string $ifModifiedSince): self`

Définit l'en-tête `If-Modified-Since`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$ifModifiedSince` | `string` | Date au format HTTP (ex: 'Mon, 15 Jun 2025 12:00:00 GMT') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setIfNoneMatch(string $ifNoneMatch): self`

Définit l'en-tête `If-None-Match`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$ifNoneMatch` | `string` | ETag (ex: '"abc123"') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setReferer(string $referer): self`

Définit l'en-tête `Referer`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$referer` | `string` | URL de provenance |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setOrigin(string $origin): self`

Définit l'en-tête `Origin`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$origin` | `string` | Origine de la requête (ex: 'https://example.com') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setXRequestedWith(string $xRequestedWith): self`

Définit l'en-tête `X-Requested-With`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$xRequestedWith` | `string` | Valeur (ex: 'XMLHttpRequest') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setXForwardedFor(string $xForwardedFor): self`

Définit l'en-tête `X-Forwarded-For`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$xForwardedFor` | `string` | IP source (ex: '192.168.1.1') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setXRequestId(string $xRequestId): self`

Définit l'en-tête `X-Request-Id`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$xRequestId` | `string` | Identifiant de requête |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setXCorrelationId(string $xCorrelationId): self`

Définit l'en-tête `X-Correlation-Id`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$xCorrelationId` | `string` | Identifiant de corrélation |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setXsrfToken(string $xsrfToken): self`

Définit l'en-tête `X-CSRF-Token`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$xsrfToken` | `string` | Token CSRF |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setStrictTransportSecurity(string $sts): self`

Définit l'en-tête `Strict-Transport-Security`.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$sts` | `string` | HSTS (ex: 'max-age=31536000') |

**Retourne :** `self` - L'instance pour le chaînage

---

### `setCustom(string $key, string $value): self`

Définit un en-tête personnalisé.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$key` | `string` | Nom de l'en-tête |
| `$value` | `string` | Valeur de l'en-tête |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$headers->setCustom('X-Custom-Header', 'custom-value');
```

---

### `getValue(): StrictDataObject`

Retourne les en-têtes sous forme de `StrictDataObject`.

**Retourne :** `StrictDataObject` - Objet contenant tous les en-têtes

---

### `toArray(): array`

Retourne les en-têtes sous forme de tableau.

**Retourne :** `array` - Tableau associatif des en-têtes

**Exemple :**
```php
$array = $headers->toArray();
// ['Content-Type' => 'application/json', 'Authorization' => 'Bearer token']
```

---

### `has(HeaderType $header): bool`

Vérifie si un en-tête existe.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$header` | `HeaderType` | Type d'en-tête |

**Retourne :** `bool` - True si l'en-tête existe

**Exemple :**
```php
$headers->has(HeaderType::CONTENT_TYPE); // true
```

---

### `get(HeaderType $header): ?string`

Récupère la valeur d'un en-tête.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$header` | `HeaderType` | Type d'en-tête |

**Retourne :** `string|null` - Valeur de l'en-tête ou null si absent

**Exemple :**
```php
$contentType = $headers->get(HeaderType::CONTENT_TYPE); // 'application/json'
```

---

## Cas d'utilisation

### Cas 1 : Configuration d'en-têtes d'authentification

```php
$headers = new HeadersVO();
$headers
    ->setAuthorization('token-123')
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON);
```

### Cas 2 : En-têtes d'authentification Basic

```php
$headers = new HeadersVO();
$headers->setBasicAuth('username', 'password');
```

### Cas 3 : En-têtes de cache

```php
$headers = new HeadersVO();
$headers
    ->setCacheControl(CacheControl::NO_CACHE)
    ->setIfModifiedSince('Mon, 15 Jun 2025 12:00:00 GMT')
    ->setIfNoneMatch('"abc123"');
```

### Cas 4 : Intégration avec Request

```php
$request = new CreatePostRequest();
$request->getHeaders()
    ->setAuthorization('token-123')
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON)
    ->setXRequestId('req-123-456');
```

---

## Flux d'exécution

```
new HeadersVO()
    ↓
setAuthorization('token-123')
    ↓
headers['Authorization'] = 'Bearer token-123'
    ↓
setContentType(ContentType::JSON)
    ↓
headers['Content-Type'] = 'application/json'
    ↓
toArray() → ['Authorization' => 'Bearer token-123', 'Content-Type' => 'application/json']
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| En-tête invalide | Aucune | - |
| Valeur invalide | Aucune | - |

---

## Intégration

### Avec Guzzle

```php
$headers = new HeadersVO();
$headers->setAuthorization('token');
$headers->setContentType(ContentType::JSON);

$options['headers'] = $headers->toArray();
$guzzle->request('POST', $uri, $options);
```

### Avec Request

```php
$request = new CreatePostRequest();
$request->getHeaders()->setAuthorization('token');
```

### Avec Response

```php
$response = $client->get($uri, $request, ResponseClass::class);
$headers = $response->getHeaders();
$contentType = $headers->get(HeaderType::CONTENT_TYPE);
```

---

## Performance

- **Stockage** : Tableau associatif simple
- **Accès** : O(1)
- **Conversion** : `toArray()` en O(n) où n est le nombre d'en-têtes

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

use AndyDefer\PhpClient\ValueObjects\HeadersVO;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\CacheControl;
use AndyDefer\PhpClient\Enums\ContentEncoding;
use AndyDefer\PhpClient\Enums\HeaderType;

// 1. Création
$headers = new HeadersVO();

// 2. Configuration
$headers
    ->setAuthorization('eyJhbGciOiJIUzI1NiIs...')
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON)
    ->setHost('api.example.com')
    ->setUserAgent('PHP/8.2')
    ->setAcceptEncoding(ContentEncoding::GZIP)
    ->setCacheControl(CacheControl::NO_CACHE)
    ->setXRequestId('req-123-456')
    ->setXsrfToken('csrf-token-xyz')
    ->setCustom('X-Custom-Header', 'custom-value');

// 3. Accès
echo "Authorization: " . $headers->get(HeaderType::AUTHORIZATION) . "\n";
echo "Content-Type: " . $headers->get(HeaderType::CONTENT_TYPE) . "\n";
echo "Has X-Request-Id: " . ($headers->has(HeaderType::X_REQUEST_ID) ? 'true' : 'false') . "\n";

// 4. Export
$array = $headers->toArray();
print_r($array);
// [
//     'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIs...',
//     'Content-Type' => 'application/json',
//     'Accept' => 'application/json',
//     'Host' => 'api.example.com',
//     'User-Agent' => 'PHP/8.2',
//     'Accept-Encoding' => 'gzip',
//     'Cache-Control' => 'no-cache',
//     'X-Request-Id' => 'req-123-456',
//     'X-CSRF-Token' => 'csrf-token-xyz',
//     'X-Custom-Header' => 'custom-value'
// ]

// 5. Basic Auth
$headers2 = new HeadersVO();
$headers2->setBasicAuth('username', 'password');
echo $headers2->get(HeaderType::AUTHORIZATION); // 'Basic dXNlcm5hbWU6cGFzc3dvcmQ='
```

---

## Voir aussi

- `OptionsVO` - Options HTTP
- `ContentType` - Enum des types de contenu
- `CacheControl` - Enum des contrôles de cache
- `HeaderType` - Enum des types d'en-têtes
- `Request` - Requête HTTP utilisant HeadersVO