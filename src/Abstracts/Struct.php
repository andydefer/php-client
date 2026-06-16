<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Abstracts;

use AndyDefer\PhpClient\Enums\ContentType;
use InvalidArgumentException;
use JsonException;

abstract class Struct extends HydratableStructure
{
    /**
     * Encode la structure vers le format spécifié
     */
    public function encode(ContentType $contentType = ContentType::JSON): string
    {
        $data = $this->toArray();

        if ($contentType->isJson()) {
            return json_encode($data, JSON_THROW_ON_ERROR);
        }

        if ($contentType->isForm()) {
            return http_build_query($data);
        }

        throw new InvalidArgumentException(
            sprintf('Unsupported content type for encoding: %s', $contentType->value)
        );
    }

    /**
     * Décode un contenu vers une structure
     *
     * @template T of Struct
     *
     * @param  class-string<T>  $class
     * @return T
     */
    public static function decode(string $content, string $class): self
    {
        if (! is_subclass_of($class, self::class)) {
            throw new InvalidArgumentException(
                sprintf('Class %s must extend %s', $class, self::class)
            );
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new InvalidArgumentException(
                sprintf('Invalid JSON: %s', $e->getMessage())
            );
        }

        return $class::from($data);
    }
}
