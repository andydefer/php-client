# RequestBodyVO - Référence Technique

## Description

`RequestBodyVO` est un Value Object qui représente le corps d'une requête HTTP de manière immutable et typée. Il encapsule une structure (`Struct`) et gère son encodage vers différents formats (JSON, formulaire).

## Hiérarchie

```
AbstractValueObject
    └── RequestBodyVO
```

## Rôle principal

`RequestBodyVO` assure :

1. **Encapsulation** d'une `Struct` pour le corps de requête
2. **Encodage** automatique vers JSON ou formulaire
3. **Validation** du type de contenu
4. **Immutabilité** des données
5. **Méthodes fluentes** pour la modification

## API / Méthodes publiques

### `__construct(Struct $struct, ContentType $contentType = ContentType::JSON)`

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$struct` | `Struct` | Structure à encapsuler |
| `$contentType` | `ContentType` | Type de contenu (défaut: JSON) |

**Exceptions :** Aucune

**Exemple :**
```php
$body = new RequestBodyVO(
    new PokemonListStruct(...),
    ContentType::JSON
);
```

---

### `getStruct(): Struct`

Retourne la structure encapsulée.

**Retourne :** `Struct` - Structure interne

**Exemple :**
```php
$struct = $body->getStruct();
echo $struct->count; // 2
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

### `isJson(): bool`

Vérifie si le type de contenu est JSON.

**Retourne :** `bool` - `true` si JSON

**Exemple :**
```php
if ($body->isJson()) {
    $json = $body->toJson();
}
```

---

### `isForm(): bool`

Vérifie si le type de contenu est formulaire.

**Retourne :** `bool` - `true` si formulaire

**Exemple :**
```php
if ($body->isForm()) {
    $form = $body->toString();
}
```

---

### `isEmpty(): bool`

Vérifie si la structure est vide.

**Retourne :** `bool` - `true` si vide

**Exemple :**
```php
if ($body->isEmpty()) {
    // Body vide
}
```

---

### `toString(): string`

Convertit la structure en chaîne selon le Content-Type.

**Retourne :** `string` - Contenu encodé

**Exceptions :** `JsonException` si JSON invalide

**Exemple :**
```php
$string = $body->toString();
// JSON: '{"count":2,"results":[]}'
// FORM: 'count=2&results%5B0%5D%5Bname%5D=...'
```

---

### `toArray(): array`

Retourne la structure sous forme de tableau.

**Retourne :** `array` - Structure en tableau

**Exemple :**
```php
$array = $body->toArray();
echo $array['count']; // 2
```

---

### `toJson(): string`

Retourne la structure encodée en JSON.

**Retourne :** `string` - JSON

**Exceptions :** 
- `InvalidArgumentException` si le type n'est pas JSON
- `JsonException` si l'encodage échoue

**Exemple :**
```php
$json = $body->toJson(); // '{"count":2,"results":[]}'
```

---

### `withStruct(Struct $struct): self`

Retourne une nouvelle instance avec une structure différente.

**Retourne :** `self` - Nouvelle instance

**Exemple :**
```php
$newBody = $body->withStruct($newStruct);
```

---

### `withContentType(ContentType $contentType): self`

Retourne une nouvelle instance avec un Content-Type différent.

**Retourne :** `self` - Nouvelle instance

**Exemple :**
```php
$newBody = $body->withContentType(ContentType::FORM);
```

---

### `getValue(): Struct`

Retourne la structure encapsulée (alias de `getStruct`).

**Retourne :** `Struct` - Structure interne

**Exemple :**
```php
$struct = $body->getValue();
```

---

### `__toString(): string`

Alias de `toString()`.

**Retourne :** `string` - Contenu encodé

**Exemple :**
```php
echo $body; // '{"count":2,"results":[]}'
```

---

## Cas d'utilisation

### Cas 1 : Envoyer une requête JSON

```php
$struct = new PokemonListStruct(
    count: 2,
    next: null,
    previous: null,
    results: $collection
);

$body = new RequestBodyVO($struct, ContentType::JSON);

$client->post('/api/pokemon', [
    'body' => $body->toString(),
    'headers' => ['Content-Type' => 'application/json']
]);
```

### Cas 2 : Envoyer un formulaire

```php
$struct = new LoginStruct(
    username: 'john',
    password: 'secret'
);

$body = new RequestBodyVO($struct, ContentType::FORM);

$client->post('/login', [
    'body' => $body->toString(),
    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
]);
```

### Cas 3 : Modification fluente

```php
$body = new RequestBodyVO($initialStruct)
    ->withContentType(ContentType::FORM)
    ->withStruct($updatedStruct);

$content = $body->toString();
```

---

## Flux d'exécution

```
Struct + ContentType
    ↓
RequestBodyVO
    ↓
toString()
    ├── isForm() → http_build_query()
    └── isJson() → json_encode()
    ↓
Contenu encodé (JSON/FORM)
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| `toJson()` sur non-JSON | `InvalidArgumentException` | `Cannot convert non-JSON content to JSON` |
| Encodage JSON invalide | `JsonException` | - |

## Intégration

### Avec les Struct

```php
// RequestBodyVO encapsule une Struct
$body = new RequestBodyVO($pokemonListStruct);
$json = $body->toString();
```

### Avec Request

```php
final class GetPokemonRequest extends Request
{
    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            $this->buildStruct(),
            ContentType::JSON
        );
    }
}
```

### Avec le Client

```php
$client->post('/v2/deposits', [
    'body' => $request->getBody()->toString(),
    'headers' => $request->getHeaders()->toArray()
]);
```

## Performance

- `toString()` utilise `json_encode()` ou `http_build_query()` natifs
- `toArray()` utilise `Struct::toArray()` (normalisation)
- Pas de cache (immutable)
- O(1) pour l'encodage JSON

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.2+ | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\Enums\ContentType;

// 1. Créer une structure
$struct = new PokemonListStruct(
    count: 2,
    next: 'https://pokeapi.co/api/v2/pokemon?offset=2',
    previous: null,
    results: $pokemonCollection
);

// 2. Créer le body
$body = new RequestBodyVO($struct, ContentType::JSON);

// 3. Vérifications
if ($body->isJson()) {
    echo "Contenu JSON\n";
}

if (!$body->isEmpty()) {
    echo "Body non vide\n";
}

// 4. Encodage
$json = $body->toJson();
echo $json;
// {"count":2,"next":"https:\/\/pokeapi.co\/api\/v2\/pokemon?offset=2","previous":null,"results":[...]}

// 5. Modification fluente
$newBody = $body
    ->withContentType(ContentType::FORM)
    ->withStruct($newStruct);

$form = $newBody->toString();
// count=2&next=https%3A%2F%2Fpokeapi.co%2Fapi%2Fv2%2Fpokemon%3Foffset%3D2

// 6. Utilisation dans une requête
$request = new InitiateDepositRequest();
$request->setBody(new RequestBodyVO($depositStruct, ContentType::JSON));

// 7. Envoi
$client->post('/v2/deposits', [
    'body' => $request->getBody()->toString(),
    'headers' => ['Content-Type' => 'application/json']
]);
```

## Voir aussi

- `Struct` - Structure de données
- `ResponseBodyVO` - Corps de réponse HTTP
- `ContentType` - Enum des types de contenu
- `Request` - Requête HTTP
---