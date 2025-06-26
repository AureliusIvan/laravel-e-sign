<?php

namespace App\Exports;

use App\Models\TahunAjaran;
use App\Models\ProposalSkripsi;
use App\Models\ProposalSkripsiForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProposalSkripsiTemplateExport implements FromCollection, WithHeadings
{

    protected $proposalSkripsiForm;
    protected $data;

    public function __construct($proposalSkripsiForm)
    {
        $this->proposalSkripsiForm = $proposalSkripsiForm;
        $this->data = null;
    }

    public function collection()
    {
        $proposal = ProposalSkripsiForm::query()
            ->with([
                'proposalSkripsi.penilaiPertama',
                'proposalSkripsi.penilaiKedua',
                'proposalSkripsi.penilaiKetiga',
                'proposalSkripsi.mahasiswa',
                'proposalSkripsi.kodePenelitianProposal.areaPenelitian',
            ])
            ->where('uuid', $this->proposalSkripsiForm)
            ->get();
        dd($proposal);
    }

    // public function query()
    // {
    //     return ProposalSkripsiForm::query()
    //         ->with([
    //             'proposalSkripsi.penilaiPertama',
    //             'proposalSkripsi.penilaiKedua',
    //             'proposalSkripsi.penilaiKetiga',
    //             'proposalSkripsi.mahasiswa',
    //             'proposalSkripsi.kodePenelitianProposal.areaPenelitian',
    //         ])
    //         ->where('uuid', $this->proposalSkripsiForm);
    // }

    public function headings(): array
    {
        return [
            'ID Proposal',
            'NIM',
            'Nama',
            'Skripsi',
            'Kode Penelitian',
            'ID Penilai 1',
            'ID Penilai 2',
            'ID Penilai 3'
        ];
    }

    // public function map($row): array
    // {
    //     $kodePenelitian = $row->proposalSkripsi->kodePenelitianProposal->areaPenelitian->pluck('kode_area_penelitian')->implode(', ');
    //     return [
    //         $row->proposalSkripsi->id,
    //         $row->proposalSkripsi->mahasiswa->nim,
    //         $row->proposalSkripsi->mahasiswa->nama,
    //         $row->proposalSkripsi->judul_proposal,
    //         $kodePenelitian,
    //         $row->proposalSkripsi->penilaiPertama->id ?? '',
    //         $row->proposalSkripsi->penilaiKedua->id ?? '',
    //         $row->proposalSkripsi->penilaiKetiga->id ?? '',
    //     ];
    // }
}
