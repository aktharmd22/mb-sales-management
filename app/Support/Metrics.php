<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Central analytics for dashboards and reports. All KPI definitions live here
 * so the salesperson view, admin view and reports always agree — and match the
 * old Excel definitions exactly.
 *
 *   businesses visited = count of visits
 *   decision makers met / interested / follow-ups done = count where flag = Yes
 *   proposals sent     = visits at the "Proposal Stage"
 *   revenue potential  = sum of revenue_potential
 */
class Metrics
{
    public const PERIODS = [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
    ];

    public Carbon $start;
    public Carbon $end;

    public function __construct(
        public string $period = 'monthly',
        public ?int $userId = null, // null = whole team
    ) {
        if (! array_key_exists($this->period, self::PERIODS)) {
            $this->period = 'monthly';
        }

        [$this->start, $this->end] = $this->resolveRange($this->period);
    }

    public static function periodLabel(string $period): string
    {
        return self::PERIODS[$period] ?? 'Monthly';
    }

    public function rangeLabel(): string
    {
        return match ($this->period) {
            'daily' => $this->start->format('l, d M Y'),
            'weekly' => $this->start->format('d M') . ' – ' . $this->end->format('d M Y'),
            'monthly' => $this->start->format('F Y'),
            'yearly' => $this->start->format('Y'),
            default => '',
        };
    }

    private function resolveRange(string $period): array
    {
        return match ($period) {
            'daily' => [today()->startOfDay(), today()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function visits()
    {
        return Visit::query()
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->whereBetween('visit_date', [$this->start->toDateString(), $this->end->toDateString()]);
    }

    private function clients()
    {
        return Client::query()
            ->when($this->userId, fn ($q) => $q->where('assigned_to', $this->userId));
    }

    /* ---------------- KPI cards ---------------- */

    public function kpis(): array
    {
        $agg = $this->visits()
            ->selectRaw('COUNT(*) as visits')
            ->selectRaw('SUM(decision_maker_met) as decision_makers')
            ->selectRaw('SUM(interested) as interested')
            ->selectRaw('SUM(follow_up_done) as follow_ups_done')
            ->selectRaw("SUM(visit_level = 'proposal') as proposals")
            ->selectRaw('SUM(revenue_potential) as revenue_potential')
            ->first();

        return [
            'visits' => (int) ($agg->visits ?? 0),
            'decision_makers' => (int) ($agg->decision_makers ?? 0),
            'interested' => (int) ($agg->interested ?? 0),
            'follow_ups_done' => (int) ($agg->follow_ups_done ?? 0),
            'proposals' => (int) ($agg->proposals ?? 0),
            'revenue_potential' => (float) ($agg->revenue_potential ?? 0),
        ];
    }

    /** Won/lost actuals in the period (for potential-vs-actual). */
    public function dealTotals(): array
    {
        $deals = Deal::query()
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->whereBetween('closed_at', [$this->start->toDateString(), $this->end->toDateString()]);

        return [
            'won' => (int) (clone $deals)->where('outcome', 'won')->count(),
            'lost' => (int) (clone $deals)->where('outcome', 'lost')->count(),
            'won_revenue' => (float) (clone $deals)->where('outcome', 'won')->sum('actual_revenue'),
        ];
    }

    /* ---------------- Pipeline funnel (current state) ---------------- */

    public function funnel(): array
    {
        $counts = $this->clients()
            ->selectRaw('pipeline_stage, COUNT(*) as c')
            ->groupBy('pipeline_stage')
            ->pluck('c', 'pipeline_stage');

        $stages = Pipeline::STAGES; // open stages, ordered
        $labels = [];
        $data = [];
        $colors = [];

        foreach ($stages as $key => $label) {
            $labels[] = $label;
            $data[] = (int) ($counts[$key] ?? 0);
            $colors[] = Pipeline::hex($key);
        }

        // Stage-to-stage conversion (each stage vs the previous).
        $conversion = [];
        for ($i = 1; $i < count($data); $i++) {
            $prev = $data[$i - 1];
            $conversion[] = $prev > 0 ? min(100, (int) round(($data[$i] / $prev) * 100)) : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
            'conversion' => $conversion,
            'won' => (int) ($counts['won'] ?? 0),
            'lost' => (int) ($counts['lost'] ?? 0),
        ];
    }

    /* ---------------- Trends ---------------- */

    /** Buckets (label => [start,end]) for the trend chart, by period. */
    private function trendBuckets(): array
    {
        $buckets = [];

        switch ($this->period) {
            case 'daily':
                // last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $d = today()->subDays($i);
                    $buckets[$d->format('d M')] = [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
                }
                break;
            case 'weekly':
                // each day of the current week
                $cursor = now()->startOfWeek();
                for ($i = 0; $i < 7; $i++) {
                    $d = $cursor->copy()->addDays($i);
                    $buckets[$d->format('D')] = [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
                }
                break;
            case 'yearly':
                // each month of the year
                for ($m = 1; $m <= 12; $m++) {
                    $d = now()->startOfYear()->addMonths($m - 1);
                    $buckets[$d->format('M')] = [$d->copy()->startOfMonth(), $d->copy()->endOfMonth()];
                }
                break;
            default: // monthly — each day of the month
                $cursor = now()->startOfMonth();
                $days = now()->daysInMonth;
                for ($i = 0; $i < $days; $i++) {
                    $d = $cursor->copy()->addDays($i);
                    $buckets[$d->format('j')] = [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
                }
        }

        return $buckets;
    }

    /** Visits + revenue-potential series across the trend buckets. */
    public function trends(): array
    {
        $buckets = $this->trendBuckets();
        $rangeStart = collect($buckets)->first()[0];
        $rangeEnd = collect($buckets)->last()[1];

        $rows = Visit::query()
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->whereBetween('visit_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->get(['visit_date', 'revenue_potential']);

        $labels = array_keys($buckets);
        $visitsSeries = [];
        $revenueSeries = [];

        foreach ($buckets as [$bStart, $bEnd]) {
            $inBucket = $rows->filter(fn ($r) => $r->visit_date->betweenIncluded($bStart, $bEnd));
            $visitsSeries[] = $inBucket->count();
            $revenueSeries[] = (float) $inBucket->sum('revenue_potential');
        }

        return [
            'labels' => $labels,
            'visits' => $visitsSeries,
            'revenue' => $revenueSeries,
        ];
    }

    /* ---------------- Leaderboard (team) ---------------- */

    public function leaderboard(): Collection
    {
        return User::where('role', User::ROLE_SALESPERSON)
            ->withCount([
                'visits as period_visits' => fn ($q) => $q->whereBetween('visit_date', [$this->start->toDateString(), $this->end->toDateString()]),
                'visits as period_interested' => fn ($q) => $q->whereBetween('visit_date', [$this->start->toDateString(), $this->end->toDateString()])->where('interested', true),
            ])
            ->withSum(['visits as period_revenue' => fn ($q) => $q->whereBetween('visit_date', [$this->start->toDateString(), $this->end->toDateString()])], 'revenue_potential')
            ->get()
            ->map(function ($u) {
                $u->period_revenue = (float) ($u->period_revenue ?? 0);

                return $u;
            })
            ->sortByDesc('period_revenue')
            ->values();
    }

    /* ---------------- Activity feed ---------------- */

    public function activity(int $limit = 12): Collection
    {
        return Visit::query()
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->with(['client:id,business_name', 'salesperson:id,name'])
            ->latest('visit_date')->latest('id')
            ->limit($limit)
            ->get();
    }
}
