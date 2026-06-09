<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\Visit;
use App\Support\Pipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Imports the team's existing spreadsheet. Each row becomes a visit; the
 * Business column is promoted into a lasting client record (created once,
 * reused on later rows). Handles the old column names directly.
 */
class VisitsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public int $clientsCreated = 0;
    public int $visitsCreated = 0;
    public int $skipped = 0;
    public array $errors = [];

    /** Cache of business name => client (per import run). */
    private array $clientCache = [];

    public function __construct(
        private int $assignToUserId,
        private int $createdByUserId,
    ) {}

    public function collection(Collection $rows): void
    {
        foreach ($rows as $i => $row) {
            $business = trim((string) $this->val($row, ['business', 'business_name', 'company']));

            if ($business === '') {
                $this->skipped++;
                continue;
            }

            try {
                $client = $this->resolveClient($business, $row);

                $level = Pipeline::keyFromLabel($this->val($row, ['visit_level', 'level', 'stage']));

                Visit::create([
                    'client_id' => $client->id,
                    'user_id' => $this->assignToUserId,
                    'visit_date' => $this->parseDate($this->val($row, ['date', 'visit_date'])),
                    'person_met' => $this->val($row, ['person_met', 'contact', 'contact_person']) ?: null,
                    'contact_phone' => $this->val($row, ['client_phone', 'phone', 'contact_phone']) ?: null,
                    'visit_level' => $level,
                    'decision_maker_met' => $this->bool($this->val($row, ['decision_maker_met', 'decision_maker', 'dm_met'])),
                    'interested' => $this->bool($this->val($row, ['interested'])),
                    'follow_up_done' => $this->bool($this->val($row, ['follow_up_done', 'followup_done'])),
                    'revenue_potential' => $this->money($this->val($row, ['revenue_potential_rm', 'revenue_potential', 'revenue', 'potential'])),
                    'notes' => $this->val($row, ['notes', 'remarks']) ?: null,
                ]);

                $this->visitsCreated++;
            } catch (\Throwable $e) {
                $this->errors[] = 'Row ' . ($i + 2) . ': ' . $e->getMessage();
                $this->skipped++;
            }
        }

        // Recompute pipeline state on every touched client.
        foreach ($this->clientCache as $client) {
            $client->refreshPipeline();
        }
    }

    private function resolveClient(string $business, $row): Client
    {
        $key = mb_strtolower($business);

        if (isset($this->clientCache[$key])) {
            return $this->clientCache[$key];
        }

        $client = Client::where('assigned_to', $this->assignToUserId)
            ->whereRaw('LOWER(business_name) = ?', [$key])
            ->first();

        if (! $client) {
            $client = Client::create([
                'business_name' => $business,
                'contact_person' => $this->val($row, ['person_met', 'contact_person']) ?: null,
                'contact_phone' => $this->val($row, ['client_phone', 'phone']) ?: null,
                'category' => $this->val($row, ['category', 'industry']) ?: null,
                'assigned_to' => $this->assignToUserId,
                'created_by' => $this->createdByUserId,
                'pipeline_stage' => 'cold',
                'status' => 'active',
            ]);
            $this->clientsCreated++;
        }

        return $this->clientCache[$key] = $client;
    }

    /** Read the first matching header from a heading-row collection row. */
    private function val($row, array $keys): string
    {
        foreach ($keys as $k) {
            if (isset($row[$k]) && $row[$k] !== null && $row[$k] !== '') {
                return (string) $row[$k];
            }
        }

        return '';
    }

    private function bool(string $v): bool
    {
        return in_array(strtolower(trim($v)), ['yes', 'y', 'true', '1', 'done', 'x'], true);
    }

    private function money(string $v): float
    {
        return (float) preg_replace('/[^0-9.]/', '', $v);
    }

    private function parseDate(string $v): string
    {
        $v = trim($v);
        if ($v === '') {
            return today()->toDateString();
        }

        // Excel serial date?
        if (is_numeric($v)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $v)
                )->toDateString();
            } catch (\Throwable) {
                // fall through
            }
        }

        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable) {
            return today()->toDateString();
        }
    }
}
