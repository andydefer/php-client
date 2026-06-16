<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\Encoding;
use InvalidArgumentException;
use JsonException;

/**
 * Response Body Value Object
 *
 * Représente le corps d'une réponse HTTP de manière immutable et typée.
 */
final class ResponseBodyVO extends AbstractValueObject
{
    private readonly mixed $content;

    private readonly ContentType $contentType;

    private readonly Encoding $encoding;

    private readonly ?Struct $struct;

    public function __construct(
        mixed $content,
        ContentType $contentType = ContentType::JSON,
        Encoding $encoding = Encoding::UTF_8,
        ?string $structClass = null
    ) {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->encoding = $encoding;

        $this->validateContent($content, $contentType);

        $this->struct = $structClass !== null
            ? $this->hydrateStruct($structClass)
            : null;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getContentAsString(): string
    {
        if (is_string($this->content)) {
            return $this->content;
        }

        if (is_scalar($this->content)) {
            return (string) $this->content;
        }

        return $this->content !== null
            ? json_encode($this->content, JSON_THROW_ON_ERROR)
            : '';
    }

    public function getValue(): ?Struct
    {
        return $this->struct;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getEncoding(): Encoding
    {
        return $this->encoding;
    }

    public function isValidJson(): bool
    {
        if (! $this->contentType->isJson() || ! is_string($this->content)) {
            return false;
        }

        try {
            json_decode($this->content, true, 512, JSON_THROW_ON_ERROR);

            return true;
        } catch (JsonException) {
            return false;
        }
    }

    public function isEmpty(): bool
    {
        return $this->content === null
            || $this->content === ''
            || $this->content === []
            || $this->content === 'null'
            || $this->content === '[]';
    }

    public function isProblemJson(): bool
    {
        return in_array($this->contentType, [
            ContentType::PROBLEM_JSON,
            ContentType::PROBLEM_JSON_UTF8,
        ]);
    }

    /**
     * Formate le contenu selon le Content-Type
     */
    public function format(): array|object
    {
        if ($this->content === null) {
            return [];
        }

        if ($this->contentType->isJson()) {
            return $this->formatJson();
        }

        if ($this->contentType->isForm()) {
            return $this->formatForm();
        }

        // Content-Type non supporté pour le formatage
        if (is_array($this->content)) {
            return $this->content;
        }

        if (is_object($this->content)) {
            return $this->content;
        }

        return ['content' => $this->content];
    }

    private function formatJson(): array|object
    {
        if (! is_string($this->content)) {
            throw new InvalidArgumentException(
                sprintf('Cannot decode JSON from non-string content: %s', gettype($this->content))
            );
        }

        try {
            return json_decode($this->content, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid JSON: %s', $e->getMessage())
            );
        }
    }

    private function formatForm(): array
    {
        if (is_array($this->content)) {
            return $this->content;
        }

        if (is_string($this->content)) {
            parse_str($this->content, $parsed);

            return $parsed;
        }

        throw new InvalidArgumentException(
            sprintf('Cannot decode form data from type: %s', gettype($this->content))
        );
    }

    /**
     * @deprecated Utilisez format() à la place
     */
    public function toArray(): array
    {
        return $this->format();
    }

    /**
     * @deprecated Utilisez format() à la place
     */
    public function toJson(): array|object
    {
        if (! $this->contentType->isJson()) {
            throw new InvalidArgumentException(
                sprintf('Content type is %s, not JSON', $this->contentType->value)
            );
        }

        return $this->formatJson();
    }

    /**
     * @deprecated Utilisez format() à la place
     */
    public function toForm(): array
    {
        if (! $this->contentType->isForm()) {
            throw new InvalidArgumentException(
                sprintf('Content type is %s, not FORM', $this->contentType->value)
            );
        }

        return $this->formatForm();
    }

    /**
     * @deprecated Utilisez format() à la place
     */
    public function toProblem(): array
    {
        if (! $this->isProblemJson()) {
            throw new InvalidArgumentException(
                sprintf('Content is not a Problem JSON, type: %s', $this->contentType->value)
            );
        }

        $data = $this->format();

        if (! isset($data['type']) || ! isset($data['title'])) {
            throw new InvalidArgumentException('Invalid Problem JSON: missing "type" or "title" fields');
        }

        return (array) $data;
    }

    private function validateContent(mixed $content, ContentType $contentType): void
    {
        if ($contentType->isJson()) {
            $this->validateJson($content);
        }

        if ($contentType->isForm()) {
            $this->validateForm($content);
        }
    }

    private function validateJson(mixed $content): void
    {
        if (! is_string($content)) {
            throw new InvalidArgumentException(
                sprintf('JSON content must be a string, %s given', gettype($content))
            );
        }

        try {
            json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid JSON: %s', $e->getMessage())
            );
        }
    }

    private function validateForm(mixed $content): void
    {
        if (! is_array($content) && ! is_string($content)) {
            throw new InvalidArgumentException(
                sprintf('Form content must be an array or string, %s given', gettype($content))
            );
        }

        if (is_string($content) && ! empty($content)) {
            parse_str($content, $parsed);
            if (empty($parsed) && ! str_contains($content, '=')) {
                throw new InvalidArgumentException('Invalid form data format');
            }
        }
    }

    private function hydrateStruct(string $structClass): Struct
    {
        if (! is_subclass_of($structClass, Struct::class)) {
            throw new InvalidArgumentException(
                sprintf('Class %s must extend %s', $structClass, Struct::class)
            );
        }

        return $structClass::from($this->format());
    }
}
