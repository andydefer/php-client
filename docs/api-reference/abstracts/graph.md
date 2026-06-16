# Graph - Référence Technique

## Description

Un `Graph` est une portion de structure de réponse API. Il représente un sous-ensemble de données retournées par une API, servant à documenter et structurer les fragments d'une réponse JSON. Contrairement aux `Record`, les `Graph` peuvent contenir des Value Objects et des Enums qui sont automatiquement convertis lors de l'hydratation.

## Hiérarchie

```
AbstractRecord
    └── Graph
```

**Implémente :** `Transformable`

## Rôle principal

Les `Graph` servent à :

1. **Documenter** la structure des réponses API
2. **Fragmenter** une réponse complexe en sous-parties réutilisables
3. **Typer** les portions de données retournées par l'API
4. **Convertir automatiquement** les données brutes (strings) en Value Objects et Enums
5. **Assurer l'immutabilité** des données

> ⚠️ **Un `Graph` est une portion de la structure d'une réponse.** Il représente un sous-ensemble de données, pas la réponse complète, les value objects ne doivent pas y servir pour la validation mais pour le formatage.

## API / Méthodes publiques

### `toArray(): array`

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

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Retourne :** `static` - L'instance elle-même (retourne `$this`)

**Exemple :**
```php
$type = new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/');
$value = $type->getValue(); // $value === $type
```

---

### `from(mixed $source): static`

Crée une instance à partir d'une source. **Convertit automatiquement** les strings en Value Objects, Enums et autres Graph.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$source` | `mixed` | Tableau ou objet contenant les données |

**Retourne :** `static` - Instance du Graph

**Exceptions :** `InvalidArgumentException` si la source est invalide ou si des paramètres requis sont manquants

**Exemple :**
```php
// Conversion automatique string → Value Object
$pokemon = PokemonDetailGraph::from([
    'id' => 'pikachu-001',        // → PokemonId
    'name' => 'Pikachu',          // → PokemonName
    'height' => 4,                // → PokemonHeight
    'weight' => 60,               // → PokemonWeight
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

### Cas 1 : Représenter une partie de réponse API

```php
// Graph représentant un Type Pokémon
final class TypeGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}

// Utilisation dans une réponse
$types = new TypeCollection();
$types->add(
    new TypeGraph('grass', 'https://pokeapi.co/api/v2/type/12/'),
    new TypeGraph('poison', 'https://pokeapi.co/api/v2/type/4/')
);
```

### Cas 2 : Fragmenter une réponse complexe avec Value Objects

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

### Cas 3 : Surcharge de `from()` pour formater

```php
final class StatGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly int $base_stat,
    ) {}

    // Surcharge pour transformer les données de l'API
    public static function from(mixed $source): static
    {
        $data = is_array($source) ? $source : (array) $source;
        
        // Formatage : conversion de clé
        if (isset($data['base_stat'])) {
            $data['base_stat'] = (int) $data['base_stat'];
        }
        
        return parent::from($data);
    }
}
```

---

## Flux d'exécution

```
Source (API) → from() / fromJson()
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
Instance Graph (immutable)
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Source n'est pas un tableau ou objet | `InvalidArgumentException` | `Source must be an array or object, X given` |
| Paramètre requis manquant | `InvalidArgumentException` | `Missing required parameters for X: $Y, $Z. Available keys: ...` |
| JSON invalide | `InvalidArgumentException` | `Invalid JSON: X` |
| Conversion impossible | `InvalidArgumentException` | `Cannot convert value to X` |

## Intégration

### Avec les Structures

```php
// Structure complète utilisant des Graphs
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
// Hydratation automatique vers une Structure contenant des Graphs
$body = new ResponseBodyVO(
    content: $responseBody,
    structClass: PokemonListStruct::class,
    contentType: ContentType::JSON
);
```

## Performance

- La normalisation utilise le `NormalizerChain` avec des normaliseurs optimisés
- Conversion automatique des Value Objects et Enums en O(1)
- Pas de validation lourde (contrairement aux Value Objects)
- Conversion O(n) pour les collections

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.2+ | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpClient\Abstracts\Graph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\TypeGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Graphs\PokemonDetailGraph;
use AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Collections\TypeCollection;

// 1. Créer un Graph simple
$type = TypeGraph::from([
    'name' => 'grass',
    'url' => 'https://pokeapi.co/api/v2/type/12/'
]);

// 2. Créer un Graph depuis JSON
$json = '{"name":"bulbasaur","url":"https://pokeapi.co/api/v2/pokemon/1/"}';
$pokemon = PokemonGraph::fromJson($json);

// 3. Créer un Graph complexe avec Value Objects
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

// 4. Normalisation
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

// 5. Collection de Graphs
$data = [
    ['name' => 'grass', 'url' => '...'],
    ['name' => 'poison', 'url' => '...']
];
$collection = TypeGraph::collect($data, TypeCollection::class);
```

## Voir aussi

- `Struct` - Structure complète de réponse API
- `AbstractRecord` - Classe parente des Graph
- `AbstractValueObject` - Value Objects pour les données métier
- `NormalizerChain` - Système de normalisation
---