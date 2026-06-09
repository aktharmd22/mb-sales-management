<?php

namespace App\Livewire\Admin\Targets;

use App\Models\Target;
use App\Models\User;
use App\Support\Metrics;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Targets')]
#[Layout('layouts.app')]
class Index extends Component
{
    public string $period; // YYYY-MM

    /** rows keyed by user id: ['visits' => x, 'revenue' => y] */
    public array $rows = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
        $this->period = now()->format('Y-m');
        $this->loadRows();
    }

    public function updatedPeriod(): void
    {
        $this->loadRows();
    }

    private function loadRows(): void
    {
        $targets = Target::where('period', $this->period)->get()->keyBy('user_id');

        $this->rows = User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get()
            ->mapWithKeys(fn ($u) => [$u->id => [
                'visits' => $targets[$u->id]->visits_target ?? null,
                'revenue' => $targets[$u->id]->revenue_target ?? null,
            ]])->toArray();
    }

    public function save(): void
    {
        foreach ($this->rows as $userId => $vals) {
            $visits = $vals['visits'] !== null && $vals['visits'] !== '' ? (int) $vals['visits'] : null;
            $revenue = $vals['revenue'] !== null && $vals['revenue'] !== '' ? (float) $vals['revenue'] : null;

            if ($visits === null && $revenue === null) {
                Target::where('user_id', $userId)->where('period', $this->period)->delete();
                continue;
            }

            Target::updateOrCreate(
                ['user_id' => $userId, 'period' => $this->period],
                ['visits_target' => $visits, 'revenue_target' => $revenue],
            );
        }

        $this->dispatch('toast', message: 'Targets saved.', type: 'success');
    }

    /** Months to choose from: last 3 + next 2. */
    public function periodOptions(): array
    {
        $opts = [];
        for ($i = -3; $i <= 2; $i++) {
            $d = now()->copy()->addMonths($i)->startOfMonth();
            $opts[$d->format('Y-m')] = $d->format('F Y');
        }

        return $opts;
    }

    public function render()
    {
        // Show progress: actual visits/revenue this period vs target.
        $salespeople = User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get();

        $progress = [];
        foreach ($salespeople as $sp) {
            $m = new Metrics('monthly', $sp->id);
            // override range to the selected period
            $start = \Illuminate\Support\Carbon::createFromFormat('Y-m', $this->period)->startOfMonth();
            $m->start = $start->copy()->startOfMonth();
            $m->end = $start->copy()->endOfMonth();
            $k = $m->kpis();
            $progress[$sp->id] = ['visits' => $k['visits'], 'revenue' => $k['revenue_potential']];
        }

        return view('livewire.admin.targets.index', [
            'salespeople' => $salespeople,
            'periodOptions' => $this->periodOptions(),
            'progress' => $progress,
        ]);
    }
}
