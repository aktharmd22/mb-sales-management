<?php

use Illuminate\Support\Number;

if (! function_exists('rm')) {
    /**
     * Format a value as Malaysian Ringgit with thousands separators.
     * rm(12500)      => "RM 12,500"
     * rm(12500, true) => "RM 12,500.00"
     */
    function rm(float|int|string|null $value, bool $decimals = false): string
    {
        $value = (float) ($value ?? 0);

        return 'RM ' . number_format($value, $decimals ? 2 : 0);
    }
}

if (! function_exists('rm_compact')) {
    /**
     * Compact RM for tight KPI spaces: rm_compact(1250000) => "RM 1.25M".
     */
    function rm_compact(float|int|string|null $value): string
    {
        $value = (float) ($value ?? 0);

        if (abs($value) >= 1_000_000) {
            return 'RM ' . rtrim(rtrim(number_format($value / 1_000_000, 2), '0'), '.') . 'M';
        }
        if (abs($value) >= 1_000) {
            return 'RM ' . rtrim(rtrim(number_format($value / 1_000, 1), '0'), '.') . 'k';
        }

        return 'RM ' . number_format($value, 0);
    }
}

if (! function_exists('pct')) {
    function pct(float|int $part, float|int $whole): int
    {
        if ($whole <= 0) {
            return 0;
        }

        return (int) round(min(100, ($part / $whole) * 100));
    }
}
