<?php

namespace App\Imports;

use App\Models\AreaPenelitian;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AreaPenelitianImport implements ToModel, WithHeadingRow
{

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function model(array $row)
    {
        return new AreaPenelitian([
            'research_list_id' => $row['research_list_id'],
            'kode_area_penelitian' => $row['kode_area_penelitian'],
            'keterangan' => $row['keterangan'],
        ]);
    }

    public function headingRow(): int
    {
        return 14;
    }
}
