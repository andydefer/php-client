<?php

declare(strict_types=1);

namespace AndyDefer\PhpClient\Enums;

enum AcceptLanguage: string
{
    case FR = 'fr';
    case FR_FR = 'fr-FR';
    case EN = 'en';
    case EN_US = 'en-US';
    case EN_GB = 'en-GB';
    case ES = 'es';
    case PT = 'pt';
    case AR = 'ar';
    case SW = 'sw';
    case ALL = '*';

    public function withQuality(float $q): string
    {
        return $this->value.';q='.number_format($q, 1);
    }
}
