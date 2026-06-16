# Struct - Référence Technique

## Description

`Struct` est une structure de données complète représentant une réponse API. Elle étend `HydratableStructure` et ajoute des méthodes d'encodage et de décodage pour faciliter la sérialisation/désérialisation des données.

## Hiérarchie

```
AbstractRecord
    └── HydratableStructure (conversion automatique)
            └── Struct (encode/decode)
                    ├── PokemonListStruct
                    ├── PokemonDetailStruct
                    └── ...
```

## Rôle principal

Les `Struct` servent à :

1. **Représenter la structure racine** d'une réponse API
2. **Encoder** les données en JSON ou formulaire (`encode()`)
3. **Décoder** du JSON vers une structure (`decode()`)
4. **Garantir l'immutabilité** des données
5. **Convertir automatiquement** les types (Value Objects, Enums, Graphs)

> ⚠️ **Différence avec Graph :** Une `Struct` est une structure **complète** de réponse API, alors qu'un `Graph` est une **portion** de réponse. Une `Struct` peut contenir des `Graph`.

## API / Méthodes publiques

### `toArray(): array`

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Retourne :** `array<string, mixed>` - La structure normalisée en tableau

**Exemple :**
```php
$array = $struct->toArray();
// ['count' => 2, 'next' => null, 'results' => [...]]
```

---

### `getValue(): static`

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Retourne :** `static` - L'instance elle-même

**Exemple :**
```php
$value = $struct->getValue(); // $value === $struct
```

---

### `encode(ContentType $contentType = ContentType::JSON): string`

Encode la structure vers le format spécifié (JSON ou formulaire).

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$contentType` | `ContentType` | Type de contenu cible (défaut: JSON) |

**Retourne :** `string` - Contenu encodé

**Exceptions :** `InvalidArgumentException` si le type de contenu n'est pas supporté

**Exemple :**
```php
$struct = new PokemonListStruct(...);

// Encodage en JSON
$json = $struct->encode();
// '{"count":2,"next":null,"previous":null,"results":[...]}'

// Encodage en formulaire
$form = $struct->encode(ContentType::FORM);
// 'count=2&next=&previous=&results%5B0%5D%5Bname%5D=...'
```

---

### `from(mixed $source): static`

Crée une instance depuis une source avec conversion automatique des types.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$source` | `mixed` | Tableau ou objet contenant les données |

**Retourne :** `static` - Instance de la structure

**Exceptions :** 
- `InvalidArgumentException` si la source n'est pas un tableau ou objet
- `InvalidArgumentException` si des paramètres requis sont manquants

**Exemple :**
```php
$struct = PokemonListStruct::from([
    'count' => 2,
    'results' => [
        ['name' => 'bulbasaur', 'url' => '...']
    ]
]);
```

---

### `fromJson(string $json): static`

Crée une instance depuis une chaîne JSON.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$json` | `string` | Chaîne JSON valide |

**Retourne :** `static` - Instance de la structure

**Exceptions :** `InvalidArgumentException` si le JSON est invalide

**Exemple :**
```php
$json = '{"count":2,"results":[...]}';
$struct = PokemonListStruct::fromJson($json);
```

---

### `decode(string $content, string $class): self`

Décode un contenu JSON vers une structure. **Méthode générique** pour les cas dynamiques.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$content` | `string` | Contenu JSON à décoder |
| `$class` | `class-string<Struct>` | Classe de la structure cible |

**Retourne :** `self` - Instance de la structure

**Exceptions :** 
- `InvalidArgumentException` si la classe n'étend pas `Struct`
- `InvalidArgumentException` si le JSON est invalide

> **📌 Subtilité :** `decode()` est une méthode **STATIQUE** sur la classe parente `Struct`. Elle nécessite de passer la classe cible en paramètre car PHP ne peut pas déterminer dynamiquement le type de retour autrement.

**Exemple :**
```php
// Cas dynamique - la classe est déterminée à l'exécution
$class = match($endpoint) {
    '/pokemon' => PokemonListStruct::class,
    '/pokemon/{id}' => PokemonDetailStruct::class,
};

// ❌ Impossible d'utiliser $class::fromJson() car $class est une variable
$struct = Struct::decode($json, $class);

// ✅ Équivalent à PokemonListStruct::fromJson() si la classe est connue
$struct = PokemonListStruct::decode($json, PokemonListStruct::class);
```

---

## Cas d'utilisation

### Cas 1 : Utilisation directe (recommandée)

```php
// ✅ Recommandé - quand vous connaissez la classe
$json = file_get_contents('response.json');
$struct = PokemonListStruct::fromJson($json);

// Traitement
foreach ($struct->results as $pokemon) {
    echo $pokemon->name;
}
```

### Cas 2 : Cas dynamique

```php
// ⚠️ Cas particulier - quand la classe est dynamique
function handleEndpoint(string $endpoint, string $response): Struct
{
    $class = match($endpoint) {
        '/pokemon' => PokemonListStruct::class,
        '/pokemon/{id}' => PokemonDetailStruct::class,
        default => throw new \RuntimeException('Unknown endpoint')
    };
    
    // decode() est utile ici car on ne connaît pas la classe à l'avance
    return Struct::decode($response, $class);
}
```

### Cas 3 : Struct avec Graph imbriqué

```php
// Struct contenant un Graph
final class PokemonDetailStruct extends Struct
{
    public function __construct(
        public readonly PokemonDetailGraph $data,
    ) {}
}

// Hydratation automatique du Graph
$struct = PokemonDetailStruct::fromJson($json);
echo $struct->data->name->getValue(); // 'Pikachu'
```

---

## Flux d'exécution

```
Source (tableau/JSON)
    ↓
from() / fromJson() / decode()
    ↓
Analyse du constructeur
    ↓
Pour chaque paramètre :
    1. Vérifier la présence de la clé (CASSE SENSITIVE)
    2. Si présente → convertir la valeur vers le type attendu
    3. Si absente → utiliser la valeur par défaut ou null
    ↓
new static(...$parameters)
    ↓
Instance Struct (immutable)
    ↓
encode() → JSON / FORM
    ↓
toArray() → Tableau normalisé
```

## Subtilités importantes

### Subtilité 1 : `decode()` vs `fromJson()`

| Méthode | Signature | Cas d'usage |
|---------|-----------|-------------|
| `PokemonListStruct::fromJson($json)` | `static fromJson(string $json)` | ✅ Quand la classe est **connue** |
| `Struct::decode($json, PokemonListStruct::class)` | `static decode(string $json, string $class)` | ⚠️ Quand la classe est **dynamique** |

**Pourquoi deux méthodes ?**
- `fromJson()` est une méthode statique sur la classe cible → retourne directement le bon type
- `decode()` est une méthode statique sur la classe parente → doit recevoir la classe cible en paramètre car elle ne peut pas la deviner

### Subtilité 2 : Sensibilité à la casse

```php
// ❌ 'Next' != 'next' → ignoré
$data = [
    'count' => 1,
    'Next' => 'https://...',  // Wrong case
    'results' => []
];

$struct = PokemonListStruct::from($data);
echo $struct->next; // null (valeur par défaut)

// ✅ 'next' == 'next' → pris en compte
$data = [
    'count' => 1,
    'next' => 'https://...',  // Bonne casse
    'results' => []
];

$struct = PokemonListStruct::from($data);
echo $struct->next; // 'https://...'
```

### Subtilité 3 : Conversion automatique des types

```php
// Les strings sont automatiquement converties
$struct = PokemonDetailStruct::from([
    'data' => [
        'id' => 'pikachu-001',    // → PokemonId (Value Object)
        'name' => 'Pikachu',      // → PokemonName (Value Object)
        'height' => 4,            // → PokemonHeight (Value Object)
        'status' => 'active',     // → PokemonStatus (Enum)
        'types' => [...],         // → TypeCollection
    ]
]);

// Résultat : tout est typé !
echo $struct->data->id->getValue();    // 'pikachu-001'
echo $struct->data->status->value;     // 'active'
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Source n'est pas un tableau ou objet | `InvalidArgumentException` | `Source must be an array or object, X given` |
| Paramètre requis manquant | `InvalidArgumentException` | `Missing required parameters for X: $Y, $Z. Available keys: ...` |
| JSON invalide (decode/fromJson) | `InvalidArgumentException` | `Invalid JSON: X` |
| Type de contenu non supporté (encode) | `InvalidArgumentException` | `Unsupported content type for encoding: X` |
| Classe ne correspond pas à une Struct (decode) | `InvalidArgumentException` | `Class X must extend Y` |

## Intégration

### Avec les Graph

```php
// Struct contient des Graph
final class PokemonDetailStruct extends Struct
{
    public function __construct(
        public readonly PokemonDetailGraph $data,  // ← Graph
    ) {}
}
```

### Avec ResponseBodyVO

```php
// Hydratation automatique depuis une réponse HTTP
$body = new ResponseBodyVO(
    content: $responseBody,
    structClass: PokemonListStruct::class,
    contentType: ContentType::JSON
);

$struct = $body->getValue();  // PokemonListStruct
```

### Avec le Client

```php
// Envoi d'une Struct en JSON
$request = new InitiateDepositRequest();
$struct = $request->getBody()->getStruct();

$json = $struct->encode(ContentType::JSON);
$client->post('/v2/deposits', [
    'body' => $json,
    'headers' => ['Content-Type' => 'application/json']
]);
```

## Performance

- `toArray()` utilise `NormalizerChain` pour la normalisation récursive
- `encode()` utilise `json_encode()` natif (performant)
- `decode()` utilise `json_decode()` natif (performant)
- Conversion des types via `convertValue()` en O(1) par paramètre
- Collections hydratées en O(n)

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.2+ | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonListStruct;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Structures\PokemonDetailStruct;

// 1. Créer une Struct depuis un tableau
$struct = PokemonListStruct::from([
    'count' => 2,
    'next' => 'https://pokeapi.co/api/v2/pokemon?offset=2',
    'previous' => null,
    'results' => [
        ['name' => 'bulbasaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/1/'],
        ['name' => 'ivysaur', 'url' => 'https://pokeapi.co/api/v2/pokemon/2/'],
    ]
]);

// 2. Encoder en JSON
$json = $struct->encode(ContentType::JSON);
// {"count":2,"next":"https:\/\/pokeapi.co\/api\/v2\/pokemon?offset=2","previous":null,"results":[{"name":"bulbasaur","url":"..."},{"name":"ivysaur","url":"..."}]}

// 3. ✅ Recommandé : décoder avec fromJson()
$decoded = PokemonListStruct::fromJson($json);
echo $decoded->count; // 2

// 4. ⚠️ Cas dynamique : décoder avec decode()
$class = PokemonListStruct::class;
$decoded = Struct::decode($json, $class);
echo $decoded->count; // 2

// 5. Struct avec Graph imbriqué
$detailStruct = PokemonDetailStruct::fromJson('{
    "data": {
        "id": "pikachu-001",
        "name": "Pikachu",
        "height": 4,
        "weight": 60,
        "types": [
            {"name": "electric", "url": "https://pokeapi.co/api/v2/type/13/"}
        ],
        "abilities": [],
        "stats": []
    }
}');

// Accès via le Graph
echo $detailStruct->data->name->getValue(); // Pikachu
echo $detailStruct->data->height->getValue(); // 4

// 6. Normalisation
$array = $detailStruct->toArray();
print_r($array);
// [
//     'data' => [
//         'id' => 'pikachu-001',
//         'name' => 'Pikachu',
//         'height' => 4,
//         'weight' => 60,
//         'types' => [['name' => 'electric', 'url' => '...']],
//         'abilities' => [],
//         'stats' => []
//     ]
// ]
```

## Voir aussi

- `Graph` - Portion de structure de réponse API
- `HydratableStructure` - Classe parente avec logique de conversion
- `AbstractRecord` - Classe de base des structures de données
- `ResponseBodyVO` - Value Object pour le corps de réponse HTTP
- `ContentType` - Enum des types de contenu supportés
---