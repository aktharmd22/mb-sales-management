<?php

namespace App\Livewire;

use App\Models\FollowUp;
use App\Models\Target;
use App\Models\User;
use App\Support\ChartFactory;
use App\Support\Metrics;
use App\Support\Pipeline;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Dashboard')]
#[Layout('layouts.app')]
class Dashboard extends Component
{
    #[Url(history: true)]
    public string $period = 'monthly';

    /** Admin: focus a single salesperson (empty = whole team). */
    #[Url(history: true)]
    public string $focus = '';

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Scope: salespeople always see themselves; admins see team or a focused person.
        $userId = $isAdmin ? ($this->focus ? (int) $this->focus : null) : $user->id;

        $metrics = new Metrics($this->period, $userId);
        $kpis = $metrics->kpis();
        $deals = $metrics->dealTotals();
        $funnel = $metrics->funnel();
        $trends = $metrics->trends();

        // Charts
        $visitsChart = ChartFactory::area($trends['labels'], $trends['visits'], '#2563EB', 'Visits');
        $revenueChart = ChartFactory::area($trends['labels'], $trends['revenue'], Pipeline::hex('proposal'), 'Revenue potential');
        $funnelChart = ChartFactory::funnelBar($funnel['labels'], $funnel['data'], $funnel['colors']);

        $data = [
            'isAdmin' => $isAdmin,
            'kpis' => $kpis,
            'deals' => $deals,
            'funnel' => $funnel,
            'rangeLabel' => $metrics->rangeLabel(),
            'periods' => Metrics::PERIODS,
            'chartKeySuffix' => $this->period . '-' . ($userId ?? 'team'),
            'visitsChart' => $visitsChart,
            'revenueChart' => $revenueChart,
            'funnelChart' => $funnelChart,
            'totalRevenueClosed' => $deals['won_revenue'],
        ];

        if ($isAdmin) {
            $data['leaderboard'] = $metrics->leaderboard();
            $data['activity'] = $metrics->activity(10);
            $data['salespeople'] = User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get(['id', 'name']);
        } else {
            // Target rings always reflect the CURRENT MONTH (targets are monthly).
            $monthMetrics = new Metrics('monthly', $user->id);
            $monthKpis = $monthMetrics->kpis();
            $target = Target::where('user_id', $user->id)->where('period', now()->format('Y-m'))->first();

            $visitsTarget = $target?->visits_target ?? 0;
            $revenueTarget = (float) ($target?->revenue_target ?? 0);

            $data['target'] = [
                'visits_done' => $monthKpis['visits'],
                'visits_target' => $visitsTarget,
                'visits_pct' => pct($monthKpis['visits'], $visitsTarget),
                'revenue_done' => $monthKpis['revenue_potential'],
                'revenue_target' => $revenueTarget,
                'revenue_pct' => pct($monthKpis['revenue_potential'], $revenueTarget),
                'has_target' => $visitsTarget > 0 || $revenueTarget > 0,
            ];
            $data['visitsRing'] = ChartFactory::radial($data['target']['visits_pct'], '#2563EB', 'Visits');
            $data['revenueRing'] = ChartFactory::radial($data['target']['revenue_pct'], Pipeline::hex('proposal'), 'Revenue');

            $data['dueFollowUps'] = FollowUp::where('user_id', $user->id)
                ->where('status', 'pending')
                ->whereDate('due_date', '<=', today())
                ->with('client:id,business_name,pipeline_stage')
                ->orderBy('due_date')
                ->limit(6)
                ->get();
        }

        return view('livewire.dashboard', $data);
    }
}
