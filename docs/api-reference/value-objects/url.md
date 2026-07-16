# UrlVO - Référence Technique

## Description

`UrlVO` est un Value Object qui représente une URL complète. Il valide automatiquement l'URL, la décompose en ses composants (schéma, hôte, port, chemin, query, fragment) et fournit des méthodes pour la manipuler de manière immutable.

## Hiérarchie

```
AbstractValueObject
    └── UrlVO
```

**Étend :** `AbstractValueObject`

## Rôle principal

`UrlVO` assure :

1. **Validation** automatique de l'URL (via `FILTER_VALIDATE_URL`)
2. **Décomposition** en composants (schéma, hôte, port, chemin, query, fragment)
3. **Manipulation** immutable (`withPath()`, `withQuery()`, `withFragment()`)
4. **Accès** type-safe à chaque composant
5. **Ajout** automatique du schéma `https://` si absent

---

## API / Méthodes publiques

### `__construct(string $value)`

Initialise l'URL avec validation automatique.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$value` | `string` | URL à valider |

**Exceptions :** `InvalidArgumentException` si l'URL est invalide

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/v2/users?page=1#section');
$url = new UrlVO('api.example.com/v2/users'); // https:// ajouté automatiquement
```

---

### `getValue(): string`

Retourne l'URL complète.

**Retourne :** `string` - L'URL complète

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/v2/users');
echo $url->getValue(); // 'https://api.example.com/v2/users'
```

---

### `getScheme(): string`

Retourne le schéma de l'URL.

**Retourne :** `string` - Schéma (ex: 'https', 'http')

**Exemple :**
```php
$url = new UrlVO('https://api.example.com');
echo $url->getScheme(); // 'https'
```

---

### `getHost(): string`

Retourne l'hôte de l'URL.

**Retourne :** `string` - Hôte (ex: 'api.example.com')

**Exemple :**
```php
$url = new UrlVO('https://api.example.com');
echo $url->getHost(); // 'api.example.com'
```

---

### `getPort(): ?int`

Retourne le port de l'URL.

**Retourne :** `int|null` - Port ou null si absent

**Exemple :**
```php
$url = new UrlVO('https://api.example.com:8080');
echo $url->getPort(); // 8080

$url = new UrlVO('https://api.example.com');
echo $url->getPort(); // null
```

---

### `getPath(): string`

Retourne le chemin de l'URL.

**Retourne :** `string` - Chemin (ex: '/v2/users')

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/v2/users');
echo $url->getPath(); // '/v2/users'
```

---

### `getQuery(): UrlQueryVO`

Retourne les paramètres de la query.

**Retourne :** `UrlQueryVO` - Objet représentant la query

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/users?page=1&limit=10');
$query = $url->getQuery();
echo $query->get('page'); // '1'
```

---

### `getFragment(): ?string`

Retourne le fragment de l'URL.

**Retourne :** `string|null` - Fragment ou null si absent

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/users#section');
echo $url->getFragment(); // 'section'
```

---

### `getFullPath(): string`

Retourne le chemin complet avec query et fragment.

**Retourne :** `string` - Chemin complet

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/v2/users?page=1#section');
echo $url->getFullPath(); // '/v2/users?page=1#section'
```

---

### `getBaseUrl(): string`

Retourne l'URL de base (schéma + hôte + port).

**Retourne :** `string` - URL de base

**Exemple :**
```php
$url = new UrlVO('https://api.example.com:8080/v2/users');
echo $url->getBaseUrl(); // 'https://api.example.com:8080'
```

---

### `withPath(string $path): self`

Modifie le chemin de l'URL. Retourne une nouvelle instance.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$path` | `string` | Nouveau chemin |

**Retourne :** `self` - Nouvelle instance avec le chemin modifié

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/v2/users');
$newUrl = $url->withPath('/v3/users');
echo $newUrl->getValue(); // 'https://api.example.com/v3/users'
```

---

### `withQuery(UrlQueryVO $query): self`

Modifie la query de l'URL. Retourne une nouvelle instance.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$query` | `UrlQueryVO` | Nouvelle query |

**Retourne :** `self` - Nouvelle instance avec la query modifiée

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/users?page=1');
$newQuery = new UrlQueryVO('limit=10');
$newUrl = $url->withQuery($newQuery);
echo $newUrl->getValue(); // 'https://api.example.com/users?limit=10'
```

---

### `withFragment(?string $fragment): self`

Modifie le fragment de l'URL. Retourne une nouvelle instance.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$fragment` | `string|null` | Nouveau fragment (null pour supprimer) |

**Retourne :** `self` - Nouvelle instance avec le fragment modifié

**Exemple :**
```php
$url = new UrlVO('https://api.example.com/users#section');
$newUrl = $url->withFragment('new-section');
echo $newUrl->getValue(); // 'https://api.example.com/users#new-section'
```

---

### `__toString(): string`

Retourne l'URL complète.

**Retourne :** `string` - L'URL complète

---

## Cas d'utilisation

### Cas 1 : Création et accès aux composants

```php
$url = new UrlVO('https://api.example.com:8080/v2/users?page=1&limit=10#section');

echo $url->getScheme();   // 'https'
echo $url->getHost();     // 'api.example.com'
echo $url->getPort();     // 8080
echo $url->getPath();     // '/v2/users'
echo $url->getFragment(); // 'section'

$query = $url->getQuery();
echo $query->get('page'); // '1'
```

### Cas 2 : Construction d'URL avec Enum

```php
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

// Utilisation
$url = PlaceholderEndpoint::COMMENTS->withId(1);
echo $url->getValue(); // 'https://jsonplaceholder.typicode.com/comments/1'
```

### Cas 3 : Modification d'URL

```php
$url = new UrlVO('https://api.example.com/v2/users?page=1');

// Modifier le chemin
$url = $url->withPath('/v3/users');

// Modifier la query
$query = $url->getQuery();
$query = $query->withParameter('limit', 10);
$url = $url->withQuery($query);

// Ajouter un fragment
$url = $url->withFragment('section');

echo $url->getValue(); // 'https://api.example.com/v3/users?page=1&limit=10#section'
```

---

## Flux d'exécution

```
new UrlVO('https://api.example.com/v2/users?page=1#section')
    ↓
Validation (FILTER_VALIDATE_URL)
    ↓
parse_url()
    ↓
Composants :
    ├── scheme → 'https'
    ├── host → 'api.example.com'
    ├── port → null
    ├── path → '/v2/users'
    ├── query → 'page=1'
    └── fragment → 'section'
    ↓
new UrlQueryVO('page=1')
    ↓
Instance UrlVO immuable
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| URL invalide | `InvalidArgumentException` | `Invalid URL: X` |
| URL sans schéma | Aucune (https:// ajouté) | - |

---

## Intégration

### Avec UrlQueryVO

```php
$url = new UrlVO('https://api.example.com/users?page=1');
$query = $url->getQuery(); // UrlQueryVO

// Modifier la query
$newQuery = $query->withParameter('limit', 10);
$newUrl = $url->withQuery($newQuery);
```

### Avec Request

```php
final class GetUsersRequest extends Request
{
    protected function setUrl(): UrlVO
    {
        return new UrlVO('https://api.example.com/users');
    }
}
```

### Avec ClientService

```php
$request = new GetUsersRequest();
$url = $request->getUrl();
$response = $client->get($url->getValue(), $request, UserListResponse::class);
```

---

## Performance

- **Validation** : `FILTER_VALIDATE_URL` en O(1)
- **Parsing** : `parse_url()` en O(1)
- **Manipulation** : Nouvelle instance à chaque modification

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

use AndyDefer\PhpClient\ValueObjects\UrlVO;
use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;

// 1. Création d'URL
$url = new UrlVO('https://api.example.com:8080/v2/users?page=1&limit=10#section');

// 2. Accès aux composants
echo "URL: " . $url->getValue() . "\n";
echo "Schéma: " . $url->getScheme() . "\n";
echo "Hôte: " . $url->getHost() . "\n";
echo "Port: " . ($url->getPort() ?? 'null') . "\n";
echo "Chemin: " . $url->getPath() . "\n";
echo "Fragment: " . ($url->getFragment() ?? 'null') . "\n";
echo "Base URL: " . $url->getBaseUrl() . "\n";
echo "Full path: " . $url->getFullPath() . "\n";

// 3. Manipulation de la query
$query = $url->getQuery();
echo "Page: " . $query->get('page') . "\n";
echo "Limit: " . $query->get('limit') . "\n";

// 4. Modifications (immuables)
$newUrl = $url
    ->withPath('/v3/users')
    ->withQuery(new UrlQueryVO('limit=20'))
    ->withFragment('new-section');

echo "Nouvelle URL: " . $newUrl->getValue() . "\n";
// 'https://api.example.com:8080/v3/users?limit=20#new-section'

// 5. URL sans schéma (https:// ajouté automatiquement)
$urlWithoutScheme = new UrlVO('api.example.com/v2/users');
echo $urlWithoutScheme->getValue(); // 'https://api.example.com/v2/users'

// 6. URL avec paramètres complexes
$url = new UrlVO('https://api.example.com/search?q=hello+world&filter[type]=active');
$query = $url->getQuery();
echo $query->get('q'); // 'hello world'
```

---

## Voir aussi

- `UrlQueryVO` - Paramètres de la query
- `AbstractValueObject` - Classe de base des Value Objects
- `Request` - Requête HTTP utilisant UrlVO
- `HeadersVO` - En-têtes HTTP