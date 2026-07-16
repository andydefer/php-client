<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Abstracts;

use AndyDefer\DomainStructures\Abstracts\AbstractRecord;
use AndyDefer\DomainStructures\Abstracts\AbstractTypedCollection;
use AndyDefer\DomainStructures\Abstracts\AbstractValueObject;
use AndyDefer\DomainStructures\Collections\Core\TypedCollection;
use AndyDefer\DomainStructures\Normalizers\NormalizerChain;
use AndyDefer\DomainStructures\Services\HydrationService;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use UnitEnum;

abstract class HydratableStructure extends AbstractRecord
{
    private static ?HydrationService $hydration = null;

    private static function getHydration(): HydrationService
    {
        if (self::$hydration === null) {
            self::$hydration = new HydrationService;
        }

        return self::$hydration;
    }

    public function toArray(): array
    {
        return NormalizerChain::get(true)->normalize($this);
    }

    public function getValue(): static
    {
        return $this;
    }

    /**
     * Normalise une clé en préservant la casse.
     */
    protected function normalizeKey(string $key): string
    {
        return $key;
    }

    /**
     * Crée une instance à partir d'une source.
     * Convertit automatiquement les strings en Value Objects et Enums.
     */
    public static function from(mixed $source): static
    {
        if (is_object($source)) {
            $source = (array) $source;
        }

        if (! is_array($source)) {
            throw new InvalidArgumentException(
                sprintf('Source must be an array or object, %s given', gettype($source))
            );
        }

        $reflection = new ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if (! $constructor || $constructor->getNumberOfParameters() === 0) {
            return new static;
        }

        $parameters = [];
        $missingRequired = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            if (array_key_exists($paramName, $source)) {
                $value = $source[$paramName];
                $convertedValue = self::convertValue($value, $paramType);
                $parameters[] = $convertedValue;

                continue;
            }

            if ($param->isDefaultValueAvailable()) {
                $parameters[] = $param->getDefaultValue();

                continue;
            }

            if ($param->allowsNull()) {
                $parameters[] = null;

                continue;
            }

            $missingRequired[] = '$'.$paramName;
        }

        if (! empty($missingRequired)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Missing required parameters for %s: %s. Available keys: %s',
                    static::class,
                    implode(', ', $missingRequired),
                    implode(', ', array_keys($source))
                )
            );
        }

        return new static(...$parameters);
    }

    /**
     * Convertit une valeur vers le type attendu.
     */
    private static function convertValue(mixed $value, ?ReflectionType $paramType): mixed
    {
        if ($paramType === null) {
            return $value;
        }

        if ($paramType instanceof ReflectionUnionType) {
            foreach ($paramType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    try {
                        return self::convertToNamedType($value, $type);
                    } catch (InvalidArgumentException) {
                        continue;
                    }
                }
            }

            return $value;
        }

        if ($paramType instanceof ReflectionNamedType) {
            return self::convertToNamedType($value, $paramType);
        }

        return $value;
    }

    /**
     * Convertit une valeur vers un type nommé.
     */
    private static function convertToNamedType(mixed $value, ReflectionNamedType $type): mixed
    {
        $typeName = $type->getName();

        // Si la valeur est déjà du bon type
        if ($value instanceof $typeName) {
            return $value;
        }

        // Si c'est une valeur null
        if ($value === null) {
            return null;
        }

        // Si c'est un enum (UnitEnum)
        if (enum_exists($typeName)) {
            return $typeName::from($value);
        }

        // Si c'est un Value Object
        if (is_subclass_of($typeName, AbstractValueObject::class)) {
            return $typeName::from($value);
        }

        // Si c'est un Graph ou Struct
        if (is_subclass_of($typeName, self::class)) {
            return $typeName::from($value);
        }

        // Si c'est une collection
        if (is_subclass_of($typeName, AbstractTypedCollection::class)) {
            if (! is_array($value)) {
                throw new InvalidArgumentException(
                    sprintf('Expected array for collection %s, got %s', $typeName, gettype($value))
                );
            }

            $collection = new $typeName;
            $itemType = $collection->getAllowedTypes()[0] ?? null;

            foreach ($value as $item) {
                if ($itemType && is_subclass_of($itemType, self::class)) {
                    $collection->add($itemType::from($item));
                } else {
                    $collection->add($item);
                }
            }

            return $collection;
        }

        // Si c'est un tableau et que le type attend un objet
        if (is_array($value) && class_exists($typeName)) {
            if (method_exists($typeName, 'from')) {
                return $typeName::from($value);
            }
        }

        // ==================== CONVERSION DES TYPES SCALAIRES ====================
        // Conversion string → int
        if ($typeName === 'int' && is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        // Conversion string → float
        if ($typeName === 'float' && is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        // Conversion string → bool
        if ($typeName === 'bool') {
            if (is_string($value)) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
            if (is_int($value)) {
                return (bool) $value;
            }
        }

        // Conversion int → string
        if ($typeName === 'string' && is_int($value)) {
            return (string) $value;
        }

        // Conversion float → string
        if ($typeName === 'string' && is_float($value)) {
            return (string) $value;
        }

        // Conversion bool → string
        if ($typeName === 'string' && is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Pour les scalaires, on laisse passer
        if (is_scalar($value)) {
            return $value;
        }

        throw new InvalidArgumentException(
            sprintf('Cannot convert value to %s', $typeName)
        );
    }

    /**
     * Crée une instance à partir d'une chaîne JSON.
     */
    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(
                sprintf('Invalid JSON: %s', json_last_error_msg())
            );
        }

        return static::from($data);
    }

    /**
     * Hydrates a collection of sources into a typed collection.
     *
     * @template TCollection of AbstractTypedCollection
     *
     * @param  iterable<mixed>  $sources
     * @param  class-string<TCollection>  $collectionClass
     * @return TCollection
     *
     * @throws InvalidArgumentException
     */
    public static function collect(iterable $sources, string $collectionClass = TypedCollection::class): AbstractTypedCollection
    {
        if (! is_subclass_of($collectionClass, AbstractTypedCollection::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Collection class "%s" must extend %s',
                    $collectionClass,
                    AbstractTypedCollection::class
                )
            );
        }

        $allowedTypes = [static::class];

        $collection = new $collectionClass(...$allowedTypes);

        foreach ($sources as $source) {
            $collection->add(static::from($source));
        }

        return $collection;
    }
}
