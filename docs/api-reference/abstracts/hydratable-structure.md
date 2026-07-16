# HydratableStructure - Référence Technique

## Description

`HydratableStructure` est une classe abstraite qui fournit un mécanisme d'**hydratation automatique** des données. Elle convertit des tableaux ou objets bruts en objets PHP typés, avec conversion automatique des types (string → int, string → Enum, array → Collection, etc.).

## Hiérarchie

```
AbstractRecord
    └── HydratableStructure
            ├── Struct
            └── Graph
```

**Implémente :** `Transformable`

## Rôle principal

`HydratableStructure` sert à :

1. **Hydrater** des données brutes en objets PHP typés
2. **Convertir automatiquement** les types (string → int, string → Enum, array → Collection)
3. **Normaliser** les données en tableau (`toArray()`)
4. **Valider** les paramètres requis
5. **Garantir l'immutabilité** des données

---

## API / Méthodes publiques

### `toArray(): array`

Normalise la structure en tableau.

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Retourne :** `array<string, mixed>` - La structure normalisée en tableau

**Exemple :**
```php
$array = $pokemon->toArray();
// ['id' => 1, 'name' => 'Bulbasaur', 'height' => 7]
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
$value = $struct->getValue(); // $value === $struct
```

---

### `from(mixed $source): static`

Crée une instance à partir d'une source. **Convertit automatiquement** les types.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$source` | `mixed` | Tableau ou objet contenant les données |

**Retourne :** `static` - Instance de la structure

**Exceptions :** 
- `InvalidArgumentException` si la source n'est pas un tableau ou objet
- `InvalidArgumentException` si des paramètres requis sont manquants

**Exemple :**
```php
$pokemon = PokemonGraph::from([
    'name' => 'Pikachu',
    'url' => 'https://pokeapi.co/api/v2/pokemon/25/'
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
$json = '{"name":"Pikachu","url":"https://..."}';
$pokemon = PokemonGraph::fromJson($json);
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

### Cas 1 : Hydratation simple

```php
// Données brutes de l'API
$data = [
    'name' => 'Pikachu',
    'height' => 4,
    'weight' => 60,
];

// Hydratation automatique
$pokemon = PokemonDetailGraph::from($data);

// Accès typé
echo $pokemon->name;    // 'Pikachu'
echo $pokemon->height;  // 4 (int)
```

### Cas 2 : Conversion automatique des types

```php
// Les strings sont automatiquement converties
$data = [
    'id' => '25',                    // string → int
    'name' => 'Pikachu',              // string
    'height' => '4',                  // string → int
    'weight' => '60',                 // string → int
    'status' => 'active',             // string → Enum
    'types' => [                      // array → Collection
        ['name' => 'electric', 'url' => '...']
    ]
];

$pokemon = PokemonDetailGraph::from($data);

// Résultat : tout est typé !
echo $pokemon->id;        // 25 (int)
echo $pokemon->height;    // 4 (int)
echo $pokemon->status->value; // 'active' (Enum)
echo count($pokemon->types); // 1 (Collection)
```

### Cas 3 : Hydratation d'une collection

```php
// Liste de données
$data = [
    ['name' => 'bulbasaur', 'url' => '...'],
    ['name' => 'ivysaur', 'url' => '...'],
    ['name' => 'venusaur', 'url' => '...'],
];

// Hydratation en collection
$collection = PokemonGraph::collect($data, PokemonCollection::class);

// Résultat : collection typée
foreach ($collection as $pokemon) {
    echo $pokemon->name;  // Typé : string
}
```

---

## Flux d'exécution

```
Source (tableau/JSON)
    ↓
from() / fromJson() / collect()
    ↓
Analyse du constructeur
    ↓
Pour chaque paramètre :
    1. Vérifier la présence de la clé (CASSE SENSITIVE)
    2. Si présente → convertir la valeur vers le type attendu
    3. Si absente → utiliser la valeur par défaut ou null
    ↓
Conversion automatique des types :
    ├── string → int (si numérique)
    ├── string → float (si numérique)
    ├── string → bool (via filter_var)
    ├── string → Enum (via from())
    ├── array → Value Object (via from())
    ├── array → HydratableStructure (via from())
    └── array → Collection (hydratation récursive)
    ↓
new static(...$parameters)
    ↓
Instance immuable
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

### Avec Struct

```php
final class PokemonListStruct extends Struct
{
    public function __construct(
        public readonly int $count,
        public readonly PokemonCollection $results,
    ) {}
}

// Hydratation automatique
$struct = PokemonListStruct::fromJson($json);
```

### Avec Graph

```php
final class PokemonGraph extends Graph
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}

// Hydratation automatique
$pokemon = PokemonGraph::from($data);
```

---

## Performance

- **Hydratation** : O(1) par paramètre
- **Collections** : O(n) où n est le nombre d'éléments
- **Normalisation** : Utilise `NormalizerChain` optimisé
- **Conversion** : Fonctions natives (`filter_var`, `enum_exists`, etc.)

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
use AndyDefer\PhpClient\Abstracts\Struct;

// 1. Définir un Graph
final class PokemonGraph extends Graph
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $url,
        public readonly ?string $type = null,
    ) {}
}

// 2. Définir un Struct
final class PokemonListStruct extends Struct
{
    public function __construct(
        public readonly int $count,
        public readonly array $results,
    ) {}
}

// 3. Hydratation depuis un tableau
$data = [
    'count' => 2,
    'results' => [
        ['id' => 1, 'name' => 'bulbasaur', 'url' => '...', 'type' => 'grass'],
        ['id' => 2, 'name' => 'ivysaur', 'url' => '...', 'type' => 'grass'],
    ]
];

$struct = PokemonListStruct::from($data);

// 4. Accès aux données
echo $struct->count; // 2
echo $struct->results[0]->name; // 'bulbasaur'

// 5. Hydratation depuis JSON
$json = '{"name":"Pikachu","url":"https://..."}';
$pokemon = PokemonGraph::fromJson($json);

// 6. Normalisation
$array = $pokemon->toArray();
print_r($array);
// ['id' => 0, 'name' => 'Pikachu', 'url' => 'https://...', 'type' => null]

// 7. Collection
$sources = [
    ['name' => 'grass', 'url' => '...'],
    ['name' => 'poison', 'url' => '...'],
];
$collection = TypeGraph::collect($sources, TypeCollection::class);
```

---

## Voir aussi

- `Struct` - Structure complète de réponse API
- `Graph` - Portion de structure de réponse API
- `AbstractRecord` - Classe de base des structures de données
- `HydrationService` - Service d'hydratation
- `NormalizerChain` - Système de normalisation