<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Enums;

/**
 * Encodages de caractères supportés
 */
enum Encoding: string
{
    case UTF_8 = 'utf-8';
    case UTF_16 = 'utf-16';
    case UTF_32 = 'utf-32';
    case ISO_8859_1 = 'iso-8859-1';
    case ISO_8859_15 = 'iso-8859-15';
    case WINDOWS_1252 = 'windows-1252';
    case ASCII = 'ascii';
    case BASE64 = 'base64';

    /**
     * Vérifie si l'encodage est basé sur UTF
     */
    public function isUtf(): bool
    {
        return in_array($this, [
            self::UTF_8,
            self::UTF_16,
            self::UTF_32,
        ]);
    }

    /**
     * Vérifie si l'encodage est basé sur ISO
     */
    public function isIso(): bool
    {
        return in_array($this, [
            self::ISO_8859_1,
            self::ISO_8859_15,
        ]);
    }

    /**
     * Vérifie si l'encodage est Windows
     */
    public function isWindows(): bool
    {
        return $this === self::WINDOWS_1252;
    }

    /**
     * Retourne l'encodage par défaut
     */
    public static function default(): self
    {
        return self::UTF_8;
    }

    /**
     * Retourne l'encodage à partir d'une chaîne
     *
     * @throws \InvalidArgumentException Si l'encodage n'est pas supporté
     */
    public static function fromString(string $encoding): self
    {
        $normalized = strtolower(trim($encoding));

        // Nettoyer les caractères spéciaux (ex: "utf-8" -> "utf-8")
        $normalized = preg_replace('/[^a-z0-9\-]/', '', $normalized);

        return match ($normalized) {
            'utf-8', 'utf8', 'utf8mb4' => self::UTF_8,
            'utf-16', 'utf16' => self::UTF_16,
            'utf-32', 'utf32' => self::UTF_32,
            'iso-8859-1', 'iso8859-1', 'latin1' => self::ISO_8859_1,
            'iso-8859-15', 'iso8859-15', 'latin9' => self::ISO_8859_15,
            'windows-1252', 'cp1252' => self::WINDOWS_1252,
            'ascii' => self::ASCII,
            'base64' => self::BASE64,
            default => throw new \InvalidArgumentException(
                sprintf('Unsupported encoding: "%s"', $encoding)
            ),
        };
    }

    /**
     * Retourne l'encodage pour le header HTTP Content-Type
     */
    public function toHeaderValue(): string
    {
        return $this->value;
    }

    /**
     * Retourne l'encodage pour la fonction mb_internal_encoding()
     */
    public function toMbInternalEncoding(): string
    {
        return match ($this) {
            self::UTF_8 => 'UTF-8',
            self::UTF_16 => 'UTF-16',
            self::UTF_32 => 'UTF-32',
            self::ISO_8859_1 => 'ISO-8859-1',
            self::ISO_8859_15 => 'ISO-8859-15',
            self::WINDOWS_1252 => 'Windows-1252',
            self::ASCII => 'ASCII',
            self::BASE64 => 'BASE64',
        };
    }

    /**
     * Retourne l'encodage pour la fonction iconv()
     */
    public function toIconvEncoding(): string
    {
        return match ($this) {
            self::UTF_8 => 'UTF-8',
            self::UTF_16 => 'UTF-16',
            self::UTF_32 => 'UTF-32',
            self::ISO_8859_1 => 'ISO-8859-1',
            self::ISO_8859_15 => 'ISO-8859-15',
            self::WINDOWS_1252 => 'Windows-1252',
            self::ASCII => 'ASCII',
            self::BASE64 => 'BASE64',
        };
    }

    /**
     * Vérifie si l'encodage est supporté par le système
     */
    public function isSupported(): bool
    {
        $supported = mb_list_encodings();
        $encoding = $this->toMbInternalEncoding();

        return in_array($encoding, $supported, true);
    }

    /**
     * Liste tous les encodages supportés
     */
    public static function getSupportedEncodings(): array
    {
        return array_map(
            fn (Encoding $encoding) => $encoding->toMbInternalEncoding(),
            self::cases()
        );
    }
}
