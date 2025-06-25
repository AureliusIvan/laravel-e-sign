<?php

namespace App\Exports;

use App\Models\AreaPenelitian;
use App\Models\ResearchList;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AreaPenelitianTemplateExport implements FromArray, WithHeadings, WithColumnWidths
{
    public function array(): array
    {
        $research = ResearchList::all(['id', 'kode_penelitian', 'topik_penelitian'])->toArray();
        return [
            ['ID Research List', 'Kode Penelitian', 'Topik Penelitian'],
            ...$research,
            [''],
            ['Research List ID', 'Kode Area Penelitian', 'Keterangan'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 25,
            'C' => 35,
        ];
    }

    public function headings(): array
    {
        return [];
    }
}
