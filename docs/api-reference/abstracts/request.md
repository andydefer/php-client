# Request - Référence Technique

## Description

`Request` est une classe abstraite qui représente une requête HTTP. Elle encapsule tous les composants d'une requête : méthode HTTP, URL, corps, en-têtes et options. Elle sert de base pour toutes les requêtes spécifiques à une API.

## Hiérarchie

```
RequestInterface
    └── Request (abstract)
            ├── GetPokemonRequest
            ├── InitiateDepositRequest
            └── ...
```

**Implémente :** `RequestInterface`

## Rôle principal

`Request` assure :

1. **Encapsulation** de tous les composants d'une requête HTTP
2. **Configuration** via des méthodes abstraites
3. **Accès immuable** aux composants
4. **Mutabilité** des en-têtes et options (via les Value Objects)
5. **Base** pour toutes les requêtes spécifiques

## API / Méthodes publiques

### `__construct()`

Initialise les composants de la requête.

| Paramètre | Type | Description |
|-----------|------|-------------|
| - | - | - |

**Exceptions :** Aucune

**Exemple :**
```php
$request = new GetPokemonRequest();
```

---

### `getMethod(): HttpMethod`

Retourne la méthode HTTP.

**Retourne :** `HttpMethod` - Méthode HTTP (GET, POST, PUT, etc.)

**Exemple :**
```php
$method = $request->getMethod(); // HttpMethod::GET
```

---

### `getUrl(): UrlVO`

Retourne l'URL de la requête.

**Retourne :** `UrlVO` - URL de la requête

**Exemple :**
```php
$url = $request->getUrl(); // UrlVO
echo $url->getValue(); // 'https://api.example.com/v2/pokemon'
```

---

### `getBody(): RequestBodyVO`

Retourne le corps de la requête.

**Retourne :** `RequestBodyVO` - Corps de la requête

**Exemple :**
```php
$body = $request->getBody();
$json = $body->toString(); // '{"depositId":"123"}'
```

---

### `getHeaders(): HeadersVO`

Retourne les en-têtes de la requête.

**Retourne :** `HeadersVO` - En-têtes HTTP

**Exemple :**
```php
$headers = $request->getHeaders();
$headers->setAuthorization('token');
```

---

### `getOptions(): OptionsVO`

Retourne les options de la requête.

**Retourne :** `OptionsVO` - Options de configuration

**Exemple :**
```php
$options = $request->getOptions();
$options->setTimeout(30);
```

---

## Méthodes abstraites

### `setMethod(): HttpMethod`

Définit la méthode HTTP.

**Retourne :** `HttpMethod` - Méthode HTTP à utiliser

**Exemple :**
```php
protected function setMethod(): HttpMethod
{
    return HttpMethod::POST;
}
```

---

### `setUrl(): UrlVO`

Définit l'URL de la requête.

**Retourne :** `UrlVO` - URL de la requête

**Exemple :**
```php
protected function setUrl(): UrlVO
{
    return new UrlVO('https://api.example.com/v2/deposits');
}
```

---

### `setBody(): RequestBodyVO`

Définit le corps de la requête.

**Retourne :** `RequestBodyVO` - Corps de la requête

**Exemple :**
```php
protected function setBody(): RequestBodyVO
{
    $struct = new InitiateDepositStruct(...);
    return new RequestBodyVO($struct, ContentType::JSON);
}
```

---

## Cas d'utilisation

### Cas 1 : Requête GET vers l'API Pokémon

```php
final class GetPokemonRequest extends Request
{
    private ?string $pokemonName = null;
    private ?int $limit = null;
    private ?int $offset = null;

    public function setPokemonName(string $name): self
    {
        $this->pokemonName = $name;
        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        $baseUrl = 'https://pokeapi.co/api/v2/pokemon';
        
        if ($this->pokemonName !== null) {
            return new UrlVO($baseUrl . '/' . $this->pokemonName);
        }

        $url = $baseUrl;
        $query = [];
        if ($this->limit !== null) {
            $query['limit'] = $this->limit;
        }
        if ($this->offset !== null) {
            $query['offset'] = $this->offset;
        }
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }
        
        return new UrlVO($url);
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new class extends Struct {};
        return new RequestBodyVO($struct, ContentType::JSON);
    }
}
```

### Cas 2 : Requête POST avec corps JSON

```php
final class InitiateDepositRequest extends Request
{
    private UuidVO $depositId;
    private AmountVO $amount;
    private Currency $currency;

    public function setDepositId(UuidVO $depositId): self
    {
        $this->depositId = $depositId;
        return $this;
    }

    public function setAmount(AmountVO $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    protected function setUrl(): UrlVO
    {
        return new UrlVO('https://api.sandbox.pawapay.io/v2/deposits');
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new InitiateDepositStruct(
            depositId: $this->depositId->getValue(),
            amount: $this->amount->getValue(),
            currency: $this->currency->value
        );
        
        return new RequestBodyVO($struct, ContentType::JSON);
    }
}
```

### Cas 3 : Ajout d'en-têtes et options

```php
$request = new InitiateDepositRequest();
$request
    ->setDepositId(new UuidVO('...'))
    ->setAmount(new AmountVO(15.00))
    ->setCurrency(Currency::ZMW);

// Ajout des en-têtes
$request->getHeaders()
    ->setAuthorization('eyJhbGciOiJIUzI1NiIs...')
    ->setContentType(ContentType::JSON)
    ->setAccept(ContentType::JSON);

// Ajout des options
$request->getOptions()
    ->setTimeout(30)
    ->setConnectTimeout(10);
```

---

## Flux d'exécution

```
new Request()
    ↓
__construct()
    ├── new HeadersVO()
    ├── new OptionsVO()
    ├── $this->setMethod() → HttpMethod
    ├── $this->setUrl() → UrlVO
    └── $this->setBody() → RequestBodyVO
    ↓
getMethod() → HttpMethod
getUrl() → UrlVO
getBody() → RequestBodyVO
getHeaders() → HeadersVO (modifiable)
getOptions() → OptionsVO (modifiable)
```

## Gestion des erreurs

| Situation | Exception | Message |
|-----------|-----------|---------|
| URL invalide | `InvalidArgumentException` | `Invalid URL: X` |
| Body invalide | `InvalidArgumentException` | Varie selon le contexte |

## Intégration

### Avec le Client

```php
$client = new ClientService();
$response = $client->post(
    $request->getUrl()->getValue(),
    $request,
    ResponseClass::class
);
```

### Avec Guzzle

```php
$options = $request->getOptions()->toArray();
$options['headers'] = $request->getHeaders()->toArray();
$options['body'] = $request->getBody()->toString();

$response = $guzzleClient->request(
    $request->getMethod()->value,
    $request->getUrl()->getValue(),
    $options
);
```

### Avec les Structures

```php
$request->getBody()->getStruct(); // Struct
$request->getBody()->toString(); // JSON string
```

## Performance

- Construction en O(1)
- Les en-têtes et options sont modifiables
- `getHeaders()` et `getOptions()` retournent des références modifiables
- Pas de cache

## Compatibilité

| Version | Support |
|---------|---------|
| PHP 8.1+ | ✅ Complet |
| PHP 8.2+ | ✅ Complet |

## Exemple complet

```php
<?php

declare(strict_types=1);

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

// 1. Créer une requête
final class CreateUserRequest extends Request
{
    private string $name;
    private string $email;

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::POST;
    }

    protected function setUrl(): UrlVO
    {
        return new UrlVO('https://api.example.com/v2/users');
    }

    protected function setBody(): RequestBodyVO
    {
        $struct = new class($this->name, $this->email) extends Struct {
            public function __construct(
                public readonly string $name,
                public readonly string $email,
            ) {}
        };

        return new RequestBodyVO(new $struct($this->name, $this->email), ContentType::JSON);
    }
}

// 2. Utilisation
$request = new CreateUserRequest();
$request
    ->setName('John Doe')
    ->setEmail('john@example.com');

// Ajout des en-têtes
$request->getHeaders()
    ->setAuthorization('token')
    ->setContentType(ContentType::JSON);

// Ajout des options
$request->getOptions()->setTimeout(30);

// Accès aux composants
$method = $request->getMethod(); // HttpMethod::POST
$url = $request->getUrl(); // UrlVO
$body = $request->getBody(); // RequestBodyVO
$headers = $request->getHeaders(); // HeadersVO
$options = $request->getOptions(); // OptionsVO

// Envoi
$client = new ClientService();
$response = $client->post(
    $url->getValue(),
    $request,
    CreateUserResponse::class
);
```

## Voir aussi

- `Response` - Réponse HTTP
- `RequestBodyVO` - Corps de requête
- `HeadersVO` - En-têtes HTTP
- `OptionsVO` - Options HTTP
- `UrlVO` - URL
- `HttpMethod` - Enum des méthodes HTTP
---