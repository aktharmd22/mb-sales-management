<?php

namespace App\Exports;

use App\Models\Visit;
use App\Support\Pipeline;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Exports the visits for a period/scope using the team's familiar Excel columns.
 */
class ReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    private \Illuminate\Support\Carbon $start;
    private \Illuminate\Support\Carbon $end;

    public function __construct(
        private string $period = 'monthly',
        private ?int $userId = null,
    ) {
        [$this->start, $this->end] = match ($period) {
            'daily' => [today()->startOfDay(), today()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function title(): string
    {
        return ucfirst($this->period) . ' report';
    }

    public function query()
    {
        return Visit::query()
            ->with(['client:id,business_name', 'salesperson:id,name'])
            ->when($this->userId, fn ($q) => $q->where('user_id', $this->userId))
            ->whereBetween('visit_date', [$this->start->toDateString(), $this->end->toDateString()])
            ->orderBy('visit_date');
    }

    public function headings(): array
    {
        return [
            'Date', 'Business', 'Salesperson', 'Person Met', 'Client Phone',
            'Visit Level', 'Decision Maker Met?', 'Interested?', 'Follow-up Done?',
            'Revenue Potential (RM)', 'Notes',
        ];
    }

    public function map($visit): array
    {
        return [
            $visit->visit_date?->format('Y-m-d'),
            $visit->client?->business_name,
            $visit->salesperson?->name,
            $visit->person_met,
            $visit->contact_phone,
            Pipeline::label($visit->visit_level),
            $visit->decision_maker_met ? 'Yes' : 'No',
            $visit->interested ? 'Yes' : 'No',
            $visit->follow_up_done ? 'Yes' : 'No',
            number_format((float) $visit->revenue_potential, 2),
            $visit->notes,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
