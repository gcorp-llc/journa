<?php

namespace App\Enums;

/**
 * Defines all configured sites that can be crawled.
 * Using an Enum prevents "magic string" errors in commands and jobs.
 */
enum CrawlableSite: string
{
    case DARMANKADE = 'darmankade';
    case DOCTORTO = 'doctorto';
    case CLEVELAND_CLINIC = 'clevelandclinic';

    /**
     * Converts a string site key back to the Enum instance, if valid.
     */
    public static function tryFromKey(string $key): ?self
    {
        return self::tryFrom($key);
    }
}
