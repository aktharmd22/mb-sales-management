<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Visits';
    }

    public function headings(): array
    {
        return [
            'Date', 'Business', 'Person Met', 'Client Phone', 'Visit Level',
            'Decision Maker Met?', 'Interested?', 'Follow-up Done?', 'Revenue Potential (RM)', 'Notes',
        ];
    }

    public function array(): array
    {
        return [
            ['2026-06-01', 'Sunrise Mart Sdn Bhd', 'Mr Lim', '+6012-345-6789', 'Warm Visit', 'Yes', 'Yes', 'No', '5000', 'Keen on bulk order'],
            ['2026-06-03', 'Harbour Cafe', 'Aisha', '+6019-876-5432', 'Cold Visit', 'No', 'No', 'No', '0', 'Dropped a brochure'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
