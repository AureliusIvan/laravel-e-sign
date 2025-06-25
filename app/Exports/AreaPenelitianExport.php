<?php

namespace App\Exports;

use App\Models\AreaPenelitian;
use Maatwebsite\Excel\Concerns\FromCollection;

class AreaPenelitianExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return AreaPenelitian::all();
    }
}
