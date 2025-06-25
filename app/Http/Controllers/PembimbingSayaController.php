<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PembimbingMahasiswa;
use App\Models\Pengaturan;
use App\Models\ProposalSkripsi;
use App\Models\ProposalSkripsiForm;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use function Symfony\Component\VarDumper\Dumper\esc;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;


class PembimbingSayaController extends Controller
{
    public function mahasiswa()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $pembimbingPertama = DB::table('pembimbing_mahasiswa')
            ->leftJoin('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing1')
            ->leftJoin('users', 'users.id', '=', 'dosen.user_id')
            ->select(
                'dosen.nama as pembimbing',
                'email',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformPertama = $pembimbingPertama->map(function ($row) {
            return [
                'pembimbing' => $row->pembimbing,
                'email' => $row->email,
                'status_pembimbing' => 1,
            ];
        });

        $pembimbingKedua = DB::table('pembimbing_mahasiswa')
            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing2')
            ->leftJoin('users', 'users.id', '=', 'dosen.user_id')
            ->select(
                'dosen.nama as pembimbing',
                'email',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformKedua = $pembimbingKedua->map(function ($row) {
            return [
                'pembimbing' => $row->pembimbing,
                'email' => $row->email,
                'status_pembimbing' => 2,
            ];
        });

        $data = $transformPertama->merge($transformKedua);

        return view('pages.mahasiswa.pembimbing.pembimbing-saya', [
            'title' => 'Pembimbing',
            'subtitle' => 'Pembimbing Saya',
            'data' => $data,
        ]);
    }

    public function dosen()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $pembimbingPertama = DB::table('pembimbing_mahasiswa')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'pembimbing_mahasiswa.mahasiswa')
            ->select(
                'mahasiswa.nim',
                'mahasiswa.nama',
            )
            ->where('pembimbing_mahasiswa.pembimbing1', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformPertama = $pembimbingPertama->map(function ($row) {
            return [
                'nim' => $row->nim,
                'nama' => $row->nama,
                'status_pembimbing' => 1,
            ];
        });

        $pembimbingKedua = DB::table('pembimbing_mahasiswa')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'pembimbing_mahasiswa.mahasiswa')
            ->select(
                'mahasiswa.nim',
                'mahasiswa.nama',
            )
            ->where('pembimbing_mahasiswa.pembimbing2', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformKedua = $pembimbingKedua->map(function ($row) {
            return [
                'nim' => $row->nim,
                'nama' => $row->nama,
                'status_pembimbing' => 2,
            ];
        });

        $data = $transformPertama->merge($transformKedua);

        return view('pages.dosen.permintaan.list-mahasiswa-bimbingan', [
            'title' => 'Mahasiswa',
            'subtitle' => 'List Mahasiswa Bimbingan',
            'data' => $data,
        ]);
    }
}
