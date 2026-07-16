# UrlQueryVO - Référence Technique

## Description

`UrlQueryVO` est un Value Object qui représente les paramètres d'une requête URL (query string). Il permet de manipuler les paramètres de manière immutable avec des méthodes pour ajouter, supprimer ou fusionner des paramètres.

## Hiérarchie

```
AbstractValueObject
    └── UrlQueryVO
```

**Étend :** `AbstractValueObject`

## Rôle principal

`UrlQueryVO` assure :

1. **Parsing** automatique des paramètres depuis une chaîne de requête
2. **Accès** type-safe aux paramètres via `get()`
3. **Immutabilité** (toutes les modifications retournent une nouvelle instance)
4. **Manipulation** fluide des paramètres (`withParameter()`, `withoutParameter()`, `merge()`)
5. **Comparaison** sémantique (ignore l'ordre des paramètres)

---

## API / Méthodes publiques

### `__construct(string $value = '')`

Initialise l'URL query avec une chaîne de requête.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$value` | `string` | Chaîne de requête (ex: 'page=1&limit=10') |

**Exceptions :** Aucune

**Exemple :**
```php
$query = new UrlQueryVO('page=1&limit=10');
$query = new UrlQueryVO(); // Vide
```

---

### `getValue(): string`

Retourne la chaîne de requête brute.

**Retourne :** `string` - La chaîne de requête

**Exemple :**
```php
$query = new UrlQueryVO('page=1&limit=10');
echo $query->getValue(); // 'page=1&limit=10'
```

---

### `getParameters(): array`

Retourne les paramètres parsés.

**Retourne :** `array` - Tableau associatif des paramètres

**Exemple :**
```php
$query = new UrlQueryVO('page=1&limit=10');
$params = $query->getParameters();
// ['page' => '1', 'limit' => '10']
```

---

### `get(string $key): mixed`

Récupère la valeur d'un paramètre.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$key` | `string` | Clé du paramètre |

**Retourne :** `mixed` - Valeur du paramètre ou null si absent

**Exemple :**
```php
$query = new UrlQueryVO('page=1&limit=10');
$page = $query->get('page'); // '1'
$sort = $query->get('sort'); // null
```

---

### `has(string $key): bool`

Vérifie si un paramètre existe.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$key` | `string` | Clé du paramètre |

**Retourne :** `bool` - True si le paramètre existe

**Exemple :**
```php
$query = new UrlQueryVO('page=1&limit=10');
$query->has('page'); // true
$query->has('sort'); // false
```

---

### `isEmpty(): bool`

Vérifie si la query est vide.

**Retourne :** `bool` - True si aucun paramètre

**Exemple :**
```php
$query = new UrlQueryVO('page=1');
$query->isEmpty(); // false

$query = new UrlQueryVO();
$query->isEmpty(); // true
```

---

### `toString(): string`

Retourne la chaîne de requête.

**Retourne :** `string` - La chaîne de requête

**Exemple :**
```php
$query = new UrlQueryVO('page=1&limit=10');
echo $query->toString(); // 'page=1&limit=10'
```

---

### `withParameter(string $key, mixed $value): self`

Ajoute ou modifie un paramètre. Retourne une nouvelle instance.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$key` | `string` | Clé du paramètre |
| `$value` | `mixed` | Valeur du paramètre |

**Retourne :** `self` - Nouvelle instance avec le paramètre ajouté

**Exemple :**
```php
$query = new UrlQueryVO('page=1');
$newQuery = $query->withParameter('limit', 10);
echo $newQuery->toString(); // 'page=1&limit=10'
```

---

### `withoutParameter(string $key): self`

Supprime un paramètre. Retourne une nouvelle instance.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$key` | `string` | Clé du paramètre à supprimer |

**Retourne :** `self` - Nouvelle instance sans le paramètre

**Exemple :**
```php
$query = new UrlQueryVO('page=1&limit=10');
$newQuery = $query->withoutParameter('limit');
echo $newQuery->toString(); // 'page=1'
```

---

### `merge(array $parameters): self`

Fusionne des paramètres. Retourne une nouvelle instance.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$parameters` | `array` | Paramètres à fusionner |

**Retourne :** `self` - Nouvelle instance avec les paramètres fusionnés

**Exemple :**
```php
$query = new UrlQueryVO('page=1');
$newQuery = $query->merge(['limit' => 10, 'sort' => 'asc']);
echo $newQuery->toString(); // 'page=1&limit=10&sort=asc'
```

---

### `equals(AbstractValueObject $other): bool`

Compare deux UrlQueryVO en ignorant l'ordre des paramètres.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$other` | `AbstractValueObject` | Autre instance à comparer |

**Retourne :** `bool` - True si les paramètres sont identiques (ordre ignoré)

**Exemple :**
```php
$query1 = new UrlQueryVO('page=1&limit=10');
$query2 = new UrlQueryVO('limit=10&page=1');
$query1->equals($query2); // true (ordre ignoré)
```

---

### `__toString(): string`

Retourne la chaîne de requête.

**Retourne :** `string` - La chaîne de requête

---

## Cas d'utilisation

### Cas 1 : Construction d'une query

```php
$query = new UrlQueryVO();
$query = $query
    ->withParameter('page', 1)
    ->withParameter('limit', 10)
    ->withParameter('sort', 'asc');

echo $query->toString(); // 'page=1&limit=10&sort=asc'
```

### Cas 2 : Modification d'une query existante

```php
$query = new UrlQueryVO('page=1&limit=10');
$query = $query
    ->withParameter('page', 2)      // Modifie page
    ->withoutParameter('limit');    // Supprime limit

echo $query->toString(); // 'page=2'
```

### Cas 3 : Intégration avec UrlVO

```php
$url = new UrlVO('https://api.example.com/users?page=1');
$query = $url->getQuery();

// Modifier la query
$newQuery = $query
    ->withParameter('limit', 20)
    ->withoutParameter('page');

// Nouvelle URL avec la query modifiée
$newUrl = $url->withQuery($newQuery);
echo $newUrl->getValue(); // 'https://api.example.com/users?limit=20'
```

---

## Flux d'exécution

```
new UrlQueryVO('page=1&limit=10')
    ↓
parse_str() → ['page' => '1', 'limit' => '10']
    ↓
withParameter('sort', 'asc')
    ↓
http_build_query(['page' => '1', 'limit' => '10', 'sort' => 'asc'])
    ↓
new UrlQueryVO('page=1&limit=10&sort=asc')
    ↓
withoutParameter('limit')
    ↓
http_build_query(['page' => '1', 'sort' => 'asc'])
    ↓
new UrlQueryVO('page=1&sort=asc')
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Valeur invalide | Aucune | - |
| Clé inexistante | Aucune (retourne null) | - |

---

## Intégration

### Avec UrlVO

```php
$url = new UrlVO('https://api.example.com/users?page=1');
$query = $url->getQuery(); // UrlQueryVO
$newQuery = $query->withParameter('limit', 10);
$newUrl = $url->withQuery($newQuery);
```

### Avec http_build_query

```php
$query = new UrlQueryVO('page=1&limit=10');
$params = $query->getParameters();
$queryString = http_build_query($params);
```

---

## Performance

- **Parsing** : `parse_str()` en O(n) où n est le nombre de paramètres
- **withParameter/withoutParameter/merge** : Copie du tableau et `http_build_query()`
- **Immuable** : Nouvelle instance à chaque modification

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

use AndyDefer\PhpClient\ValueObjects\UrlQueryVO;

// 1. Créer une query
$query = new UrlQueryVO('page=1&limit=10');

// 2. Accès aux paramètres
echo "Page: " . $query->get('page') . "\n"; // '1'
echo "Limit: " . $query->get('limit') . "\n"; // '10'
echo "Has sort: " . ($query->has('sort') ? 'true' : 'false') . "\n"; // false

// 3. Modifications (immuables)
$newQuery = $query
    ->withParameter('page', 2)
    ->withParameter('sort', 'asc')
    ->withoutParameter('limit');

echo "Original: " . $query->toString() . "\n"; // 'page=1&limit=10'
echo "Modifié: " . $newQuery->toString() . "\n"; // 'page=2&sort=asc'

// 4. Fusion
$merged = $query->merge(['sort' => 'desc', 'filter' => 'active']);
echo "Fusion: " . $merged->toString() . "\n"; // 'page=1&limit=10&sort=desc&filter=active'

// 5. Comparaison (ordre ignoré)
$query1 = new UrlQueryVO('page=1&limit=10');
$query2 = new UrlQueryVO('limit=10&page=1');
echo "Égaux: " . ($query1->equals($query2) ? 'true' : 'false') . "\n"; // true

// 6. Intégration avec UrlVO
use AndyDefer\PhpClient\ValueObjects\UrlVO;

$url = new UrlVO('https://api.example.com/users?page=1');
$query = $url->getQuery();
$newQuery = $query->withParameter('limit', 20);
$newUrl = $url->withQuery($newQuery);
echo "Nouvelle URL: " . $newUrl->getValue() . "\n";
// 'https://api.example.com/users?page=1&limit=20'
```

---

## Voir aussi

- `UrlVO` - URL complète avec query
- `AbstractValueObject` - Classe de base des Value Objects
- `HeadersVO` - En-têtes HTTP
- `OptionsVO` - Options HTTP