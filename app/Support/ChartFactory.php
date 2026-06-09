<?php

namespace App\Support;

/**
 * Builds ApexCharts option arrays (JSON-serialisable) so Blade/Livewire views
 * stay clean. Keep these free of JS closures — they're passed through @js().
 */
class ChartFactory
{
    /** Smooth area chart — used for visits / revenue trends. */
    public static function area(array $labels, array $data, string $hex, string $name = 'Series', int $height = 240): array
    {
        return [
            'chart' => [
                'type' => 'area',
                'height' => $height,
                'fontFamily' => 'DM Sans, sans-serif',
                'toolbar' => ['show' => false],
                'zoom' => ['enabled' => false],
                'sparkline' => ['enabled' => false],
            ],
            'series' => [['name' => $name, 'data' => $data]],
            'xaxis' => [
                'categories' => $labels,
                'labels' => ['style' => ['colors' => '#94A3B8', 'fontSize' => '11px']],
                'axisBorder' => ['show' => false],
                'axisTicks' => ['show' => false],
                'tickAmount' => min(count($labels), 8),
            ],
            'yaxis' => [
                'labels' => ['style' => ['colors' => '#94A3B8', 'fontSize' => '11px']],
            ],
            'colors' => [$hex],
            'fill' => [
                'type' => 'gradient',
                'gradient' => ['shadeIntensity' => 1, 'opacityFrom' => 0.35, 'opacityTo' => 0.02, 'stops' => [0, 90]],
            ],
            'stroke' => ['curve' => 'straight', 'width' => 2.5],
            'dataLabels' => ['enabled' => false],
            'grid' => ['borderColor' => '#EEF1F5', 'strokeDashArray' => 4, 'xaxis' => ['lines' => ['show' => false]]],
            'tooltip' => ['theme' => 'light'],
        ];
    }

    /** Horizontal bar — used for the pipeline funnel. */
    public static function funnelBar(array $labels, array $data, array $colors, int $height = 260): array
    {
        return [
            'chart' => [
                'type' => 'bar',
                'height' => $height,
                'fontFamily' => 'DM Sans, sans-serif',
                'toolbar' => ['show' => false],
            ],
            'series' => [['name' => 'Clients', 'data' => $data]],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 6,
                    'barHeight' => '62%',
                    'distributed' => true,
                ],
            ],
            'colors' => $colors,
            'xaxis' => [
                'categories' => $labels,
                'labels' => ['style' => ['colors' => '#94A3B8', 'fontSize' => '11px']],
                'axisBorder' => ['show' => false],
                'axisTicks' => ['show' => false],
            ],
            'yaxis' => ['labels' => ['style' => ['colors' => '#475569', 'fontSize' => '12px']]],
            'dataLabels' => [
                'enabled' => true,
                'style' => ['colors' => ['#0F1E2E'], 'fontWeight' => 700, 'fontSize' => '12px'],
                'offsetX' => 16,
            ],
            'legend' => ['show' => false],
            'grid' => ['borderColor' => '#EEF1F5', 'strokeDashArray' => 4],
            'tooltip' => ['theme' => 'light'],
        ];
    }

    /** Radial progress ring — used for target progress. */
    public static function radial(int $percent, string $hex, string $label, int $height = 220): array
    {
        return [
            'chart' => [
                'type' => 'radialBar',
                'height' => $height,
                'fontFamily' => 'DM Sans, sans-serif',
                'sparkline' => ['enabled' => true],
            ],
            'series' => [min(100, $percent)],
            'colors' => [$hex],
            'plotOptions' => [
                'radialBar' => [
                    'hollow' => ['size' => '62%'],
                    'track' => ['background' => '#EEF1F5', 'strokeWidth' => '100%'],
                    'dataLabels' => [
                        'name' => ['offsetY' => 22, 'color' => '#94A3B8', 'fontSize' => '12px'],
                        'value' => ['offsetY' => -14, 'color' => '#0F1E2E', 'fontSize' => '28px', 'fontWeight' => 700],
                    ],
                ],
            ],
            'labels' => [$label],
            'stroke' => ['lineCap' => 'round'],
        ];
    }
}
