<?php

namespace App\Livewire\Reports;

use App\Exports\ReportExport;
use App\Models\User;
use App\Support\ChartFactory;
use App\Support\Metrics;
use App\Support\Pipeline;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Title('Reports')]
#[Layout('layouts.app')]
class Index extends Component
{
    #[Url(history: true)]
    public string $period = 'monthly';

    #[Url(history: true)]
    public string $focus = '';

    public function export()
    {
        $user = auth()->user();
        $userId = $user->isAdmin() ? ($this->focus ? (int) $this->focus : null) : $user->id;

        $filename = 'malayznbeat-report-' . $this->period . '-' . now()->format('Ymd') . '.xlsx';

        return Excel::download(new ReportExport($this->period, $userId), $filename);
    }

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $userId = $isAdmin ? ($this->focus ? (int) $this->focus : null) : $user->id;

        $metrics = new Metrics($this->period, $userId);
        $kpis = $metrics->kpis();
        $deals = $metrics->dealTotals();
        $funnel = $metrics->funnel();
        $trends = $metrics->trends();

        $breakdown = collect();
        if ($isAdmin && ! $this->focus) {
            // Per-salesperson breakdown for the team view.
            $breakdown = User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get()
                ->map(function ($sp) {
                    $m = (new Metrics($this->period, $sp->id))->kpis();
                    $d = (new Metrics($this->period, $sp->id))->dealTotals();

                    return [
                        'name' => $sp->name,
                        'kpis' => $m,
                        'won_revenue' => $d['won_revenue'],
                    ];
                });
        }

        return view('livewire.reports.index', [
            'isAdmin' => $isAdmin,
            'kpis' => $kpis,
            'deals' => $deals,
            'funnel' => $funnel,
            'rangeLabel' => $metrics->rangeLabel(),
            'periods' => Metrics::PERIODS,
            'salespeople' => $isAdmin ? User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get(['id', 'name']) : collect(),
            'breakdown' => $breakdown,
            'visitsChart' => ChartFactory::area($trends['labels'], $trends['visits'], '#2563EB', 'Visits'),
            'revenueChart' => ChartFactory::area($trends['labels'], $trends['revenue'], Pipeline::hex('proposal'), 'Revenue potential'),
            'chartKeySuffix' => $this->period . '-' . ($userId ?? 'team'),
        ]);
    }
}
