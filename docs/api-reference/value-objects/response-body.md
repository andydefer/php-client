# ResponseBodyVO - Référence Technique

## Description

`ResponseBodyVO` est un Value Object qui représente le corps d'une réponse HTTP de manière immutable et typée. Il gère la validation, le formatage et l'hydratation des données JSON et formulaire vers des structures (`Struct`).

## Hiérarchie

```
AbstractValueObject
    └── ResponseBodyVO<T of Struct>
```

**Template :** `@template T of Struct`

## Rôle principal

`ResponseBodyVO` assure :

1. **Validation** des contenus JSON et formulaire
2. **Formatage** selon le Content-Type (JSON, FORM)
3. **Hydratation** automatique vers des `Struct` typées
4. **Gestion des erreurs** d'hydratation (retourne `null` si échec)
5. **Immutabilité** des données

## API / Méthodes publiques

### `__construct(mixed $content, string $structClass, ContentType $contentType = ContentType::JSON, Encoding $encoding = Encoding::UTF_8)`

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$content` | `mixed` | Contenu brut de la réponse |
| `$structClass` | `class-string<T>` | Classe de la structure cible |
| `$contentType` | `ContentType` | Type de contenu (défaut: JSON) |
| `$encoding` | `Encoding` | Encodage (défaut: UTF-8) |

**Exceptions :** `InvalidArgumentException` si le contenu est invalide

**Exemple :**
```php
$body = new ResponseBodyVO(
    '{"count":2,"results":[]}',
    PokemonListStruct::class,
    ContentType::JSON
);
```

---

### `getContent(): mixed`

Retourne le contenu brut.

**Retourne :** `mixed` - Contenu original

**Exemple :**
```php
$raw = $body->getContent(); // '{"count":2,"results":[]}'
```

---

### `getContentAsString(): string`

Retourne le contenu sous forme de chaîne.

**Retourne :** `string` - Contenu converti en chaîne

**Exemple :**
```php
$string = $body->getContentAsString(); // '{"count":2,"results":[]}'
```

---

### `getValue(): ?Struct`

Retourne la structure hydratée.

**Retourne :** `T|null` - Structure hydratée ou `null` si échec

**Exemple :**
```php
$struct = $body->getValue();
if ($struct !== null) {
    echo $struct->count; // 2
}
```

---

### `getContentType(): ContentType`

Retourne le type de contenu.

**Retourne :** `ContentType` - Type de contenu

**Exemple :**
```php
$type = $body->getContentType(); // ContentType::JSON
```

---

### `getEncoding(): Encoding`

Retourne l'encodage.

**Retourne :** `Encoding` - Encodage

**Exemple :**
```php
$encoding = $body->getEncoding(); // Encoding::UTF_8
```

---

### `isValidJson(): bool`

Vérifie si le contenu est un JSON valide.

**Retourne :** `bool` - `true` si JSON valide

**Exemple :**
```php
if ($body->isValidJson()) {
    // Traitement JSON
}
```

---

### `isEmpty(): bool`

Vérifie si le contenu est vide.

**Retourne :** `bool` - `true` si vide

**Exemple :**
```php
if ($body->isEmpty()) {
    // Réponse vide
}
```

---

### `isProblemJson(): bool`

Vérifie si le contenu est un Problem JSON (RFC 7807).

**Retourne :** `bool` - `true` si Problem JSON

**Exemple :**
```php
if ($body->isProblemJson()) {
    // Traitement des erreurs RFC 7807
}
```

---

### `format(): array|object`

Formate le contenu selon le Content-Type.

**Retourne :** `array|object` - Contenu formaté

**Exceptions :** `InvalidArgumentException` si le formatage échoue

**Exemple :**
```php
// JSON → object stdClass
$formatted = $body->format();
echo $formatted->count; // 2

// FORM → array
$formatted = $body->format();
echo $formatted['count']; // '2'
```

---

## Cas d'utilisation

### Cas 1 : Hydrater une réponse JSON en Structure

```php
// Réponse de l'API Pokémon
$json = '{"count":2,"results":[{"name":"bulbasaur","url":"..."}]}';

$body = new ResponseBodyVO($json, PokemonListStruct::class);
$struct = $body->getValue();

if ($struct !== null) {
    echo $struct->count; // 2
    foreach ($struct->results as $pokemon) {
        echo $pokemon->name;
    }
}
```

### Cas 2 : Gérer une erreur d'hydratation

```php
// Le JSON ne correspond pas à la structure attendue
$json = '{"invalid":"data"}';

$body = new ResponseBodyVO($json, PokemonListStruct::class);
$struct = $body->getValue();

if ($struct === null) {
    // L'hydratation a échoué, gérer l'erreur
    $raw = $body->getContentAsString();
    // Log ou traitement alternatif
}
```

### Cas 3 : Détection des contenus vides

```php
$body = new ResponseBodyVO('null', PokemonListStruct::class);

if ($body->isEmpty()) {
    // La réponse est vide
    return null;
}

if ($body->isValidJson()) {
    $data = $body->format();
    // Traitement des données
}
```

---

## Flux d'exécution

```
Contenu brut (JSON/FORM)
    ↓
validateContent()
    ├── isJson() → validateJson()
    └── isForm() → validateForm()
    ↓
format()
    ├── isJson() → formatJson()
    └── isForm() → formatForm()
    ↓
hydrateStruct()
    ├── try { Struct::from($data) → Structure hydratée }
    └── catch (InvalidArgumentException) { return null }
    ↓
getValue() → Struct|null
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| JSON invalide | `InvalidArgumentException` | `Invalid JSON: Syntax error` |
| JSON non-string | `InvalidArgumentException` | `JSON content must be a string, X given` |
| FORM invalide | `InvalidArgumentException` | `Invalid form data format` |
| Classe non-Struct | `InvalidArgumentException` | `Class X must extend Y` |
| Échec d'hydratation | Aucune (retourne `null`) | - |

## Intégration

### Avec les Struct

```php
// ResponseBodyVO hydrate automatiquement les Struct
$body = new ResponseBodyVO($json, PokemonListStruct::class);
$struct = $body->getValue(); // PokemonListStruct
```

### Avec Response

```php
final class PokemonListResponse extends Response
{
    public function getPokemons(): PokemonCollection
    {
        $struct = $this->getBody()->getValue();
        return $struct->results;
    }
}
```

### Avec le Client

```php
$response = $client->post('/v2/deposits', $request);
$body = new ResponseBodyVO(
    $response->getBody()->getContents(),
    InitiateDepositStructure::class
);
```

## Performance

- Validation et formatage en O(1) pour JSON
- Hydratation des collections en O(n)
- Pas de cache interne (immutable)
- `json_decode` natif (performant)

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.2+ | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpClient\ValueObjects\ResponseBodyVO;
use AndyDefer\PhpClient\Enums\ContentType;

// 1. Réponse JSON valide
$json = '{"count":2,"results":[{"name":"bulbasaur","url":"..."}]}';
$body = new ResponseBodyVO($json, PokemonListStruct::class);

// 2. Vérifications
if ($body->isValidJson() && !$body->isEmpty()) {
    $struct = $body->getValue();
    if ($struct !== null) {
        echo $struct->count; // 2
    }
}

// 3. Formatage
$formatted = $body->format();
echo $formatted->count; // 2

// 4. Réponse Problem JSON
$problemJson = '{"type":"https://example.com/errors","title":"Invalid input"}';
$problemBody = new ResponseBodyVO(
    $problemJson,
    PokemonListStruct::class,
    ContentType::PROBLEM_JSON
);

if ($problemBody->isProblemJson()) {
    // Traitement des erreurs RFC 7807
}

// 5. Réponse vide
$emptyBody = new ResponseBodyVO('null', PokemonListStruct::class);
if ($emptyBody->isEmpty()) {
    echo 'Body is empty';
}

// 6. Échec d'hydratation
$invalidBody = new ResponseBodyVO('{}', PokemonListStruct::class);
$struct = $invalidBody->getValue(); // null
```

## Voir aussi

- `Struct` - Structure de données pour les réponses API
- `Request` - Requête HTTP
- `Response` - Réponse HTTP
- `ContentType` - Enum des types de contenu
- `Encoding` - Enum des encodages
---