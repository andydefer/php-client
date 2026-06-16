<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Tests\Fixtures\Pokemon\Requests;

use AndyDefer\PhpClient\Abstracts\Request;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\HttpMethod;
use AndyDefer\PhpClient\ValueObjects\RequestBodyVO;
use AndyDefer\PhpClient\ValueObjects\UrlVO;

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

    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    protected function setMethod(): HttpMethod
    {
        return HttpMethod::GET;
    }

    protected function setUrl(): UrlVO
    {
        $baseUrl = 'https://pokeapi.co/api/v2';

        if ($this->pokemonName !== null) {
            return new UrlVO($baseUrl.'/pokemon/'.$this->pokemonName);
        }

        $url = $baseUrl.'/pokemon';
        $query = [];

        if ($this->limit !== null) {
            $query['limit'] = $this->limit;
        }

        if ($this->offset !== null) {
            $query['offset'] = $this->offset;
        }

        if (! empty($query)) {
            $url .= '?'.http_build_query($query);
        }

        return new UrlVO($url);
    }

    protected function setBody(): RequestBodyVO
    {
        return new RequestBodyVO(
            struct: new class extends Struct
            {
                public function toArray(): array
                {
                    return [];
                }
            },
            contentType: ContentType::JSON
        );
    }
}
