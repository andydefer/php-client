<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use InvalidArgumentException;

final class RequestBodyVO extends AbstractValueObject
{
    private Struct $struct;

    public function __construct(
        Struct $struct,
        private readonly ContentType $contentType = ContentType::JSON,
    ) {
        $this->struct = $struct;
    }

    public function getStruct(): Struct
    {
        return $this->struct;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function isJson(): bool
    {
        return $this->contentType->isJson();
    }

    public function isForm(): bool
    {
        return $this->contentType->isForm();
    }

    public function isEmpty(): bool
    {
        return empty($this->struct->toArray());
    }

    public function toString(): string
    {
        $data = $this->struct->toArray();

        if ($this->isForm()) {
            return http_build_query($data);
        }

        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function toArray(): array
    {
        return $this->struct->toArray();
    }

    public function toJson(): string
    {
        if (! $this->isJson()) {
            throw new InvalidArgumentException('Cannot convert non-JSON content to JSON');
        }

        return json_encode($this->struct->toArray(), JSON_THROW_ON_ERROR);
    }

    public function withStruct(Struct $struct): self
    {
        return new self($struct, $this->contentType);
    }

    public function withContentType(ContentType $contentType): self
    {
        return new self($this->struct, $contentType);
    }

    public function getValue(): Struct
    {
        return $this->struct;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
