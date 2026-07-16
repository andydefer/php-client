<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\ValueObjects;

use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Normalizers\NormalizerChain;
use AndyDefer\PhpClient\Abstracts\Struct;
use AndyDefer\PhpClient\Enums\ContentType;
use AndyDefer\PhpClient\Enums\Encoding;
use InvalidArgumentException;
use JsonException;

/**
 * Response Body Value Object
 *
 * Représente le corps d'une réponse HTTP de manière immutable et typée.
 *
 * @template T of Struct
 */
final class ResponseBodyVO extends AbstractValueObject
{
    private readonly mixed $content;

    private readonly ContentType $contentType;

    private readonly Encoding $encoding;

    private readonly ?Struct $struct;

    /**
     * @param  class-string<T>  $structClass
     */
    public function __construct(
        mixed $content,
        string $structClass,
        ContentType $contentType = ContentType::JSON,
        Encoding $encoding = Encoding::UTF_8,
    ) {
        $this->content = $content;
        $this->contentType = $contentType;
        $this->encoding = $encoding;

        $this->validateContent($content, $contentType);
        $this->struct = $this->hydrateStruct($structClass);
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

    /**
     * @return T|null
     */
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
    public function format(): array
    {
        if ($this->content === null) {
            return [];
        }

        if ($this->contentType->isJson()) {
            try {
                $result = $this->formatJson();
                if (is_object($result)) {
                    $result = (array) $result;
                }
                if (is_array($result)) {
                    return NormalizerChain::get(true)->normalize($result);
                }

                return [];
            } catch (InvalidArgumentException $e) {
                return ['content' => $this->content];
            }
        }

        if ($this->contentType->isForm()) {
            return $this->formatForm();
        }

        if (is_array($this->content)) {
            return NormalizerChain::get(true)->normalize($this->content);
        }

        if (is_object($this->content)) {
            return NormalizerChain::get(true)->normalize((array) $this->content);
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
            $decoded = json_decode($this->content, false, 512, JSON_THROW_ON_ERROR);

            if ($decoded === null) {
                return [];
            }

            return $decoded;
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

    /**
     * @param  class-string<T>  $structClass
     * @return T|null
     */
    private function hydrateStruct(string $structClass): ?Struct
    {
        if (! is_subclass_of($structClass, Struct::class)) {
            throw new InvalidArgumentException(
                sprintf('Class %s must extend %s', $structClass, Struct::class)
            );
        }

        try {
            $data = $this->format();

            if (is_object($data)) {
                $data = (array) $data;
            }

            if (! is_array($data)) {
                $data = [];
            }

            return $structClass::from($data);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }
}
