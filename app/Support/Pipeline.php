<?php

namespace App\Support;

/**
 * Single source of truth for the Malayznbeat sales pipeline.
 *
 * Stage keys are stored in the DB; labels/colors/order live here so the
 * funnel, kanban, badges and dashboards all read from one place.
 */
class Pipeline
{
    /** Ordered open stages (excludes terminal won/lost). */
    public const STAGES = [
        'cold'        => 'Cold Visit',
        'warm'        => 'Warm Visit',
        'qualified'   => 'Qualified Meeting',
        'opportunity' => 'Opportunity',
        'proposal'    => 'Proposal Stage',
    ];

    /** Terminal outcomes. */
    public const OUTCOMES = [
        'won'  => 'Won',
        'lost' => 'Lost',
    ];

    /** Tailwind token suffix (text-stage-*, bg-stage-*) per stage. */
    public const COLORS = [
        'cold'        => 'cold',
        'warm'        => 'warm',
        'qualified'   => 'qualified',
        'opportunity' => 'opportunity',
        'proposal'    => 'proposal',
        'won'         => 'won',
        'lost'        => 'lost',
    ];

    /** Hex values (for ApexCharts series). */
    public const HEX = [
        'cold'        => '#60A5FA',
        'warm'        => '#3B82F6',
        'qualified'   => '#2563EB',
        'opportunity' => '#4F46E5',
        'proposal'    => '#4338CA',
        'won'         => '#16A34A',
        'lost'        => '#E5484D',
    ];

    /** All selectable stages for a visit (open stages + won/lost). */
    public static function all(): array
    {
        return self::STAGES + self::OUTCOMES;
    }

    public static function label(?string $key): string
    {
        return self::all()[$key] ?? ucfirst((string) $key);
    }

    public static function color(?string $key): string
    {
        return self::COLORS[$key] ?? 'cold';
    }

    public static function hex(?string $key): string
    {
        return self::HEX[$key] ?? '#94A3B8';
    }

    /** Numeric rank used to decide a client's "highest stage reached". */
    public static function rank(?string $key): int
    {
        $order = array_keys(self::STAGES);
        $idx = array_search($key, $order, true);
        if ($idx !== false) {
            return $idx; // 0..4
        }

        return match ($key) {
            'won'  => 100,
            'lost' => -1,
            default => 0,
        };
    }

    public static function isOpen(?string $key): bool
    {
        return array_key_exists($key, self::STAGES);
    }

    /** Resolve a stage key from free text (import / spreadsheet values). */
    public static function keyFromLabel(?string $text): string
    {
        $text = strtolower(trim((string) $text));

        if ($text === '') {
            return 'cold';
        }

        // Exact key match first.
        if (array_key_exists($text, self::all())) {
            return $text;
        }

        // Match against labels and common keywords.
        foreach (self::all() as $key => $label) {
            if (str_contains(strtolower($label), $text) || str_contains($text, $key)) {
                return $key;
            }
        }

        return match (true) {
            str_contains($text, 'cold') => 'cold',
            str_contains($text, 'warm') => 'warm',
            str_contains($text, 'qualif') => 'qualified',
            str_contains($text, 'oppor') => 'opportunity',
            str_contains($text, 'propos') => 'proposal',
            str_contains($text, 'won') => 'won',
            str_contains($text, 'lost') => 'lost',
            default => 'cold',
        };
    }

    public static function isTerminal(?string $key): bool
    {
        return array_key_exists($key, self::OUTCOMES);
    }
}
