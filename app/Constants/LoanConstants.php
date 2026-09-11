<?php

namespace App\Constants;

class LoanConstants
{
    const PREFIX_BMI        = 'TOM';
    const PREFIX_BFC        = 'TOB';
    const SEARCH_BY_AGREEMENT = 'Agreement Number';
    const MIN_PREFIX_LENGTH = 3;

    const NPA_DISALLOWED = ['REPO', 'SALE'];

    const COMPANIES = [
        'BMI' => [
            'prefix'   => 'TOM',
            'schemeid' => ['131', '135', '900'],
        ],
        'BFC' => [
            'prefix'   => 'TOB',
            'schemeid' => ['906', '132', '138'],
        ],
    ];

    public static function getCompanyByPrefix(string $prefix): ?string
    {
        foreach (self::COMPANIES as $company => $config) {
            if ($config['prefix'] === strtoupper($prefix)) {
                return $company;
            }
        }
        return null;
    }

    public static function getCompanyBySchemeId(string $schemeId): ?string
    {
        foreach (self::COMPANIES as $company => $config) {
            if (in_array($schemeId, $config['schemeid'])) {
                return $company;
            }
        }
        return null;
    }

    public static function companyCodeCase(string $column = 'agreementNumber'): string
    {
        return "CASE
            WHEN LEFT({$column}, 4) = 'MAUR' THEN '469'
            WHEN LEFT({$column}, 4) IN ('MBFC', 'MBF5') THEN '471'
            ELSE NULL
        END";
    }
}
