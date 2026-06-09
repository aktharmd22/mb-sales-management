<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\User;
use App\Support\Pipeline;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private User $user) {}

    public function title(): string
    {
        return 'Clients';
    }

    public function query()
    {
        return Client::query()
            ->forUser($this->user)
            ->with('salesperson:id,name')
            ->withCount('visits')
            ->withSum('visits as revenue_potential_sum', 'revenue_potential')
            ->orderBy('business_name');
    }

    public function headings(): array
    {
        return [
            'Business', 'Contact Person', 'Phone', 'Address', 'Category',
            'Pipeline Stage', 'Status', 'Salesperson', 'Visits', 'Revenue Potential (RM)', 'Last Visit',
        ];
    }

    public function map($client): array
    {
        return [
            $client->business_name,
            $client->contact_person,
            $client->contact_phone,
            $client->address,
            $client->category,
            Pipeline::label($client->pipeline_stage),
            ucfirst($client->status),
            $client->salesperson?->name,
            $client->visits_count,
            number_format((float) $client->revenue_potential_sum, 2),
            $client->last_visit_at?->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
