# OptionsVO - Référence Technique

## Description

`OptionsVO` est un Value Object qui gère les options de configuration pour les requêtes HTTP. Il fournit des méthodes type-safe pour définir les options les plus courantes (timeout, proxy, authentification, SSL, etc.) ainsi qu'une méthode générique pour les options personnalisées.

## Hiérarchie

```
AbstractValueObject
    └── OptionsVO
```

**Étend :** `AbstractValueObject`

## Rôle principal

`OptionsVO` assure :

1. **Configuration centralisée** des options HTTP
2. **Méthodes type-safe** pour chaque option
3. **Support** des timeouts, proxy, SSL, authentification, redirections
4. **Conversion** en tableau pour l'intégration avec Guzzle
5. **Flexibilité** via les options personnalisées

---

## API / Méthodes publiques

### Options générales

#### `setTimeout(int $seconds): self`

Définit le délai d'attente global.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$seconds` | `int` | Délai en secondes |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$options->setTimeout(30);
```

---

#### `setConnectTimeout(int $seconds): self`

Définit le délai de connexion.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$seconds` | `int` | Délai en secondes |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setVerify(bool|string $verify): self`

Définit la vérification SSL.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$verify` | `bool|string` | `true` (vérifier), `false` (ignorer), ou chemin vers un certificat |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$options->setVerify(true);           // Vérifier SSL
$options->setVerify(false);          // Ignorer SSL
$options->setVerify('/path/to/cert.pem'); // Certificat personnalisé
```

---

#### `setDebug(bool $debug): self`

Active ou désactive le mode debug.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$debug` | `bool` | `true` pour activer le debug |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setHttpErrors(bool $httpErrors): self`

Définit si les erreurs HTTP doivent lancer des exceptions.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$httpErrors` | `bool` | `false` pour ne pas lancer d'exception |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$options->setHttpErrors(false); // Ne pas lancer d'exception sur 4xx/5xx
```

---

#### `setAllowRedirects(bool|array $allowRedirects): self`

Définit le comportement des redirections.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$allowRedirects` | `bool|array` | `false` pour désactiver, `true` pour activer, ou tableau de configuration |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$options->setAllowRedirects(false);
$options->setAllowRedirects(['max' => 5, 'strict' => true]);
```

---

#### `setMaxRedirects(int $maxRedirects): self`

Définit le nombre maximum de redirections.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$maxRedirects` | `int` | Nombre maximum |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setCookies(bool|array $cookies): self`

Définit la gestion des cookies.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$cookies` | `bool|array` | `true` pour activer, ou tableau de cookies |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setIdnConversion(bool $idnConversion): self`

Active ou désactive la conversion IDN.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$idnConversion` | `bool` | `true` pour activer |

**Retourne :** `self` - L'instance pour le chaînage

---

### Options de transfert

#### `setBody(string $body): self`

Définit le corps de la requête (string brute).

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$body` | `string` | Corps de la requête |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setJson(array $json): self`

Définit le corps de la requête en JSON.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$json` | `array` | Données JSON |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setMultipart(array $multipart): self`

Définit le corps en multipart/form-data.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$multipart` | `array` | Données multipart |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setFormParams(array $formParams): self`

Définit le corps en application/x-www-form-urlencoded.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$formParams` | `array` | Données du formulaire |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setStream(bool $stream): self`

Active ou désactive le mode stream.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$stream` | `bool` | `true` pour activer |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setSink(string $sink): self`

Définit le fichier de destination pour le stream.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$sink` | `string` | Chemin du fichier |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setReadTimeout(int $seconds): self`

Définit le délai de lecture.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$seconds` | `int` | Délai en secondes |

**Retourne :** `self` - L'instance pour le chaînage

---

### Options de proxy

#### `setProxy(string|array $proxy): self`

Définit le proxy.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$proxy` | `string|array` | URL du proxy ou tableau par protocole |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$options->setProxy('tcp://localhost:8080');
$options->setProxy(['http' => 'tcp://localhost:8080', 'https' => 'tcp://localhost:8443']);
```

---

#### `setNoProxy(array $noProxy): self`

Définit les domaines à exclure du proxy.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$noProxy` | `array` | Liste des domaines |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$options->setNoProxy(['.example.com', '.test.com']);
```

---

### Options d'authentification

#### `setAuth(array $auth): self`

Définit l'authentification HTTP.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$auth` | `array` | `[username, password]` ou `[username, password, 'digest']` |

**Retourne :** `self` - L'instance pour le chaînage

**Exemple :**
```php
$options->setAuth(['user', 'pass']);
$options->setAuth(['user', 'pass', 'digest']);
```

---

#### `setCert(string|array $cert): self`

Définit le certificat SSL.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$cert` | `string|array` | Chemin du certificat ou `[chemin, mot_de_passe]` |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setSslKey(string|array $sslKey): self`

Définit la clé SSL.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$sslKey` | `string|array` | Chemin de la clé ou `[chemin, mot_de_passe]` |

**Retourne :** `self` - L'instance pour le chaînage

---

### Options de version HTTP

#### `setVersion(string $version): self`

Définit la version HTTP.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$version` | `string` | Version HTTP (ex: '1.1', '2.0') |

**Retourne :** `self` - L'instance pour le chaînage

---

### Options d'environnement

#### `setBaseUri(string $baseUri): self`

Définit l'URI de base.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$baseUri` | `string` | URI de base |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setHeaders(array $headers): self`

Définit les en-têtes (alternative à HeadersVO).

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$headers` | `array` | Tableau associatif des en-têtes |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setQuery(array $query): self`

Définit les paramètres de la query.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$query` | `array` | Paramètres de la query |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setDecodeContent(bool $decodeContent): self`

Active ou désactive le décodage du contenu.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$decodeContent` | `bool` | `true` pour activer |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `setForceIpResolve(string $forceIpResolve): self`

Force la résolution IPv4 ou IPv6.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$forceIpResolve` | `string` | `'v4'` ou `'v6'` |

**Retourne :** `self` - L'instance pour le chaînage

---

### Options de logging

#### `setOnStats(callable $onStats): self`

Définit une fonction de callback pour les statistiques.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$onStats` | `callable` | Fonction de callback |

**Retourne :** `self` - L'instance pour le chaînage

---

### Méthodes génériques

#### `setCustom(string $key, mixed $value): self`

Définit une option personnalisée.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$key` | `string` | Clé de l'option |
| `$value` | `mixed` | Valeur de l'option |

**Retourne :** `self` - L'instance pour le chaînage

---

#### `has(OptionType $option): bool`

Vérifie si une option existe.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$option` | `OptionType` | Type d'option |

**Retourne :** `bool` - True si l'option existe

---

#### `get(OptionType $option): mixed`

Récupère la valeur d'une option.

| Paramètre | Type | Description |
|-----------|------|-------------|
| `$option` | `OptionType` | Type d'option |

**Retourne :** `mixed` - Valeur de l'option ou null si absente

---

#### `getValue(): StrictDataObject`

Retourne les options sous forme de `StrictDataObject`.

**Retourne :** `StrictDataObject` - Objet contenant toutes les options

---

#### `toArray(): array`

Retourne les options sous forme de tableau.

**Retourne :** `array` - Tableau associatif des options

---

## Cas d'utilisation

### Cas 1 : Configuration des timeouts

```php
$options = new OptionsVO();
$options
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setReadTimeout(60);
```

### Cas 2 : Configuration SSL et proxy

```php
$options = new OptionsVO();
$options
    ->setVerify(true)
    ->setCert('/path/to/cert.pem')
    ->setSslKey('/path/to/key.pem')
    ->setProxy('tcp://proxy.example.com:8080')
    ->setNoProxy(['.example.com']);
```

### Cas 3 : Configuration des redirections

```php
$options = new OptionsVO();
$options
    ->setAllowRedirects(false)
    ->setMaxRedirects(5);
```

### Cas 4 : Intégration avec Request

```php
$request = new GetPostsRequest();
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setHttpErrors(false)
    ->setVerify(true);
```

---

## Flux d'exécution

```
new OptionsVO()
    ↓
setTimeout(30)
    ↓
options['timeout'] = 30
    ↓
setConnectTimeout(10)
    ↓
options['connect_timeout'] = 10
    ↓
toArray() → ['timeout' => 30, 'connect_timeout' => 10]
    ↓
ClientService::buildOptions() → array_merge($options, $request->getOptions()->toArray())
```

---

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| Option invalide | Aucune | - |
| Valeur invalide | Aucune | - |

---

## Intégration

### Avec Guzzle

```php
$options = new OptionsVO();
$options->setTimeout(30);

$guzzleOptions = $options->toArray();
$guzzle->request('POST', $uri, $guzzleOptions);
```

### Avec ClientService

```php
$request = new CreatePostRequest();
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10);

$response = $client->post($uri, $request, ResponseClass::class);
```

### Avec HeadersVO

```php
$request = new CreatePostRequest();
$request->getHeaders()->setContentType(ContentType::JSON);
$request->getOptions()->setTimeout(30);
```

---

## Performance

- **Stockage** : Tableau associatif simple
- **Accès** : O(1)
- **Conversion** : `toArray()` en O(n) où n est le nombre d'options

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

use AndyDefer\PhpClient\ValueObjects\OptionsVO;
use AndyDefer\PhpClient\Enums\OptionType;

// 1. Création
$options = new OptionsVO();

// 2. Configuration
$options
    // Timeouts
    ->setTimeout(30)
    ->setConnectTimeout(10)
    ->setReadTimeout(60)

    // SSL
    ->setVerify(true)
    ->setCert('/path/to/cert.pem')
    ->setSslKey('/path/to/key.pem')

    // Redirections
    ->setAllowRedirects(false)
    ->setMaxRedirects(5)

    // Proxy
    ->setProxy('tcp://proxy.example.com:8080')
    ->setNoProxy(['.example.com'])

    // Authentification
    ->setAuth(['username', 'password'])

    // Environnement
    ->setBaseUri('https://api.example.com')
    ->setQuery(['page' => 1, 'limit' => 10])
    ->setDecodeContent(true)
    ->setHttpErrors(false);

// 3. Vérification
if ($options->has(OptionType::TIMEOUT)) {
    echo "Timeout: " . $options->get(OptionType::TIMEOUT) . "\n";
}

// 4. Export
$array = $options->toArray();
print_r($array);
// [
//     'timeout' => 30,
//     'connect_timeout' => 10,
//     'read_timeout' => 60,
//     'verify' => true,
//     'cert' => '/path/to/cert.pem',
//     'ssl_key' => '/path/to/key.pem',
//     'allow_redirects' => false,
//     'max_redirects' => 5,
//     'proxy' => 'tcp://proxy.example.com:8080',
//     'no_proxy' => ['.example.com'],
//     'auth' => ['username', 'password'],
//     'base_uri' => 'https://api.example.com',
//     'query' => ['page' => 1, 'limit' => 10],
//     'decode_content' => true,
//     'http_errors' => false
// ]

// 5. Option personnalisée
$options->setCustom('custom_option', 'custom_value');
echo $options->toArray()['custom_option']; // 'custom_value'
```

---

## Voir aussi

- `HeadersVO` - En-têtes HTTP
- `OptionType` - Enum des types d'options
- `Request` - Requête HTTP utilisant OptionsVO
- `ClientService` - Client HTTP utilisant OptionsVO