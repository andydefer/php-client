# Graph - Référence Technique

## Description

`Graph` est une classe abstraite qui représente une **portion** de structure de réponse API. Elle sert à documenter et structurer les fragments d'une réponse JSON. Contrairement aux `Record`, les `Graph` peuvent contenir des Value Objects et des Enums qui sont automatiquement convertis lors de l'hydratation.

## Hiérarchie

```
AbstractRecord
    └── HydratableStructure (conversion automatique)
            └── Graph
                    ├── PokemonGraph
                    ├── TypeGraph
                    ├── CommentGraph
                    └── ...
```

## Rôle principal

Les `Graph` servent à :

1. **Documenter** la structure des réponses API
2. **Fragmenter** une réponse complexe en sous-parties réutilisables
3. **Typer** les portions de données retournées par l'API
4. **Convertir automatiquement** les données brutes (strings) en Value Objects et Enums
5. **Assurer l'immutabilité** des données
6. **Conserver la casse originale** des clés (contrairement aux Record qui utilisent snake_case)

> ⚠️ **Différence avec Struct :** Un `Graph` est une **portion** de la structure d'une réponse, alors qu'une `Struct` est la structure **complète**. Une `Struct` peut contenir des `Graph`.

---

## API / Méthodes publiques

### `normalizeKey(string $key): string`

Préserve la casse originale des clés.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$key` | `string` | Clé à normaliser |

**Retourne :** `string` - La clé inchangée (casse préservée)

**Exemple :**
```php
// ⚠️ La méthode est protected, pas d'usage direct
// L'effet est visible lors de l'hydratation
$pokemon = PokemonGraph::from([
    'pokemonName' => 'Pikachu'  // ← Clé en camelCase
]);
echo $pokemon->pokemonName; // 'Pikachu' (préservé)
```

---

### `toArray(): array`

Normalise le Graph en tableau.

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Retourne :** `array<string, mixed>` - Le Graph normalisé en tableau

**Exemple :**
```php
$type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');
$array = $type->toArray();
// ['name' => 'grass', 'url' => 'https://pokeapi.co/api/v2/type/12/']
```

---

### `getValue(): static`

Retourne l'instance elle-même.

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Retourne :** `static` - L'instance elle-même

**Exemple :**
```php
$type = new TypeGraph('grass', 'https://...');
$value = $type->getValue(); // $value === $type
```

---

### `from(mixed $source): static`

Crée une instance à partir d'une source. **Convertit automatiquement** les strings en Value Objects, Enums et autres Graph.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$source` | `mixed` | Tableau ou objet contenant les données |

**Retourne :** `static` - Instance du Graph

**Exceptions :** 
- `InvalidArgumentException` si la source n'est pas un tableau ou objet
- `InvalidArgumentException` si des paramètres requis sont manquants

**Exemple :**
```php
// Conversion automatique string → Value Object
$pokemon = PokemonDetailGraph::from([
    'id' => 'pikachu-001',        // → PokemonId (Value Object)
    'name' => 'Pikachu',          // → PokemonName (Value Object)
    'height' => 4,                // → PokemonHeight (Value Object)
    'weight' => 60,               // → PokemonWeight (Value Object)
    'status' => 'active',         // → PokemonStatus (Enum)
]);
```

---

### `fromJson(string $json): static`

Crée une instance depuis une chaîne JSON.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$json` | `string` | Chaîne JSON valide |

**Retourne :** `static` - Instance du Graph

**Exceptions :** `InvalidArgumentException` si le JSON est invalide

**Exemple :**
```php
$json = '{"name":"grass","url":"https://pokeapi.co/api/v2/type/12/"}';
$type = TypeGraph::fromJson($json);
```

---

### `collect(iterable $sources, string $collectionClass = TypedCollection::class): AbstractTypedCollection`

Hydrate une collection de sources.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$sources` | `iterable<mixed>` | Sources à hydrater |
| `$collectionClass` | `class-string<AbstractTypedCollection>` | Classe de la collection |

**Retourne :** `AbstractTypedCollection` - Collection hydratée

**Exceptions :** `InvalidArgumentException` si la classe de collection est invalide

**Exemple :**
```php
$data = [
    ['name' => 'grass', 'url' => '...'],
    ['name' => 'poison', 'url' => '...'],
];
$collection = TypeGraph::collect($data, TypeCollection::class);
```

---

## Cas d'utilisation

### Cas 1 : Représenter une portion de réponse API

```php
// Graph représentant un Type Pokémon
final class TypeGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly ?string $full_name = 'Andy'
    ) {}
}

// Utilisation dans une Struct
final class PokemonListStruct extends Struct
{
    public function __construct(
        public readonly int $count,
        public readonly PokemonCollection $results,  // Collection de Graph
    ) {}
}
```

### Cas 2 : Graph avec Value Objects et Enums

```php
// Graph avec Value Objects et Enums
final class PokemonDetailGraph extends Graph
{
    public function __construct(
        public readonly PokemonId $id,          // Value Object
        public readonly PokemonName $name,      // Value Object
        public readonly PokemonHeight $height,  // Value Object
        public readonly PokemonWeight $weight,  // Value Object
        public readonly TypeCollection $types,  // Collection
        public readonly PokemonStatus $status,  // Enum
    ) {}
}

// Hydratation automatique
$pokemon = PokemonDetailGraph::from([
    'id' => 'pikachu-001',      // Converti en PokemonId
    'name' => 'Pikachu',        // Converti en PokemonName
    'height' => 4,              // Converti en PokemonHeight
    'weight' => 60,             // Converti en PokemonWeight
    'types' => [...],           // Converti en TypeCollection
    'status' => 'active',       // Converti en PokemonStatus
]);
```

### Cas 3 : Préservation de la casse

```php
final class WeatherGraph extends Graph
{
    public function __construct(
        public readonly string $cityName,      // camelCase
        public readonly float $temperatureC,   // camelCase
        public readonly float $humidityPct,    // camelCase
    ) {}
}

// Les clés en camelCase sont préservées
$weather = WeatherGraph::from([
    'cityName' => 'Paris',        // ✓ Préservé
    'temperatureC' => 22.5,       // ✓ Préservé
    'humidityPct' => 65.0,        // ✓ Préservé
    'CityName' => 'Lyon',         // ✗ Ignoré (casse différente)
]);

echo $weather->cityName; // 'Paris' (pas 'Lyon')
```

---

## Flux d'exécution

```
Source (API) → from() / fromJson()
    ↓
Analyse du constructeur (casse préservée)
    ↓
Pour chaque paramètre :
    1. Vérifier la présence de la clé (CASSE SENSITIVE)
    2. Si présente → convertir la valeur vers le type attendu
    3. Si absente → utiliser la valeur par défaut ou null
    ↓
Conversion automatique :
    ├── string → Value Object (via from())
    ├── string → Enum (via from())
    ├── array → Graph (via from())
    ├── array → Collection (hydratation récursive)
    └── scalar → type natif
    ↓
new static(...$parameters)
    ↓
Instance Graph (immutable)
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Source non tableau/objet | `InvalidArgumentException` | `Source must be an array or object, X given` |
| Paramètre requis manquant | `InvalidArgumentException` | `Missing required parameters for X: $Y, $Z. Available keys: ...` |
| JSON invalide | `InvalidArgumentException` | `Invalid JSON: X` |
| Conversion impossible | `InvalidArgumentException` | `Cannot convert value to X` |

---

## Intégration

### Avec les Structures

```php
// Struct contenant des Graphs
final class PokemonListStruct extends Struct
{
    public function __construct(
        public readonly int $count,
        public readonly PokemonCollection $results,  // Collection de Graph
    ) {}
}
```

### Avec ResponseBodyVO

```php
// Hydratation automatique vers une Struct contenant des Graphs
$body = new ResponseBodyVO(
    content: $responseBody,
    structClass: PokemonListStruct::class,
    contentType: ContentType::JSON
);

$struct = $body->getValue();  // PokemonListStruct avec Graphs
```

### Avec les Collections

```php
// Collection de Graphs
final class PokemonCollection extends AbstractTypedCollection
{
    public function __construct()
    {
        parent::__construct(PokemonGraph::class);
    }
}

// Utilisation
$collection = PokemonGraph::collect($data, PokemonCollection::class);
```

---

## Performance

- **Hydratation** : O(1) par paramètre
- **Collections** : O(n) où n est le nombre d'éléments
- **Normalisation** : Utilise `NormalizerChain` optimisé
- **Conversion** : Fonctions natives (`enum_exists`, `is_subclass_of`, etc.)

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

use AndyDefer\PhpClient\Abstracts\Graph;

// 1. Définir un Graph simple
final class TypeGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}

// 2. Définir un Graph avec Value Objects
final class PokemonDetailGraph extends Graph
{
    public function __construct(
        public readonly PokemonId $id,
        public readonly PokemonName $name,
        public readonly PokemonHeight $height,
        public readonly PokemonWeight $weight,
        public readonly TypeCollection $types,
        public readonly PokemonStatus $status,
    ) {}
}

// 3. Hydratation depuis un tableau
$type = TypeGraph::from([
    'name' => 'grass',
    'url' => 'https://pokeapi.co/api/v2/type/12/'
]);

// 4. Hydratation depuis JSON
$json = '{"name":"bulbasaur","url":"https://pokeapi.co/api/v2/pokemon/1/"}';
$pokemon = PokemonGraph::fromJson($json);

// 5. Hydratation complexe avec Value Objects
$pokemonDetail = PokemonDetailGraph::from([
    'id' => 'pikachu-001',
    'name' => 'Pikachu',
    'height' => 4,
    'weight' => 60,
    'types' => [
        ['name' => 'electric', 'url' => 'https://pokeapi.co/api/v2/type/13/']
    ],
    'status' => 'active'
]);

// 6. Normalisation
$normalized = $pokemonDetail->toArray();
// [
//     'id' => 'pikachu-001',
//     'name' => 'Pikachu',
//     'height' => 4,
//     'weight' => 60,
//     'types' => [
//         ['name' => 'electric', 'url' => '...']
//     ],
//     'status' => 'active'
// ]

// 7. Collection de Graphs
$data = [
    ['name' => 'grass', 'url' => '...'],
    ['name' => 'poison', 'url' => '...']
];
$collection = TypeGraph::collect($data, TypeCollection::class);

// 8. Accès aux données
foreach ($collection as $type) {
    echo $type->name;  // 'grass', 'poison'
}
```

---

## Voir aussi

- `Struct` - Structure complète de réponse API
- `HydratableStructure` - Classe parente avec logique de conversion
- `AbstractRecord` - Classe de base des structures de données
- `ResponseBodyVO` - Value Object pour le corps de réponse HTTP
- `ContentType` - Enum des types de contenu supportés