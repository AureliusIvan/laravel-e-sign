<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BimbinganController extends Controller
{
    public function mahasiswa()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $pembimbingPertama = DB::table('pembimbing_mahasiswa')
            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing1')
            ->select(
                'dosen.nama as pembimbing',
                'dosen.uuid as slug_pembimbing',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();
        $pembimbingKedua = DB::table('pembimbing_mahasiswa')
            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing2')
            ->select(
                'dosen.nama as pembimbing',
                'dosen.uuid as slug_pembimbing',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $pembimbing = $pembimbingPertama->merge($pembimbingKedua);

        $data = DB::table('bimbingan')
            ->join('dosen', 'dosen.id', '=', 'bimbingan.dosen_id')
            ->select(
                'bimbingan.uuid',
                'bimbingan.tanggal_bimbingan',
                'bimbingan.isi_bimbingan',
                'bimbingan.saran',
                'bimbingan.status',
                'bimbingan.note',
                'dosen.nama'
            )
            ->where('bimbingan.tahun_ajaran_id', $active->id)
            ->where('bimbingan.program_studi_id', $user->program_studi_id)
            ->where('bimbingan.mahasiswa_id', $user->id)
            ->orderBy('tanggal_bimbingan', 'asc')
            ->get()
            ->toArray();

        $pertama = DB::table('pembimbing_mahasiswa')
            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing1')
            ->select(
                'dosen.nama as pembimbing',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->first();
        $kedua = DB::table('pembimbing_mahasiswa')
            ->join('dosen', 'dosen.id', '=', 'pembimbing_mahasiswa.pembimbing2')
            ->select(
                'dosen.nama as pembimbing',
            )
            ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->first();
        $bimbinganPembimbingPertama = DB::table('bimbingan')
            ->join('dosen', 'dosen.id', '=', 'bimbingan.dosen_id')
            ->where('mahasiswa_id', $user->id)
            ->where('status', 1)
            ->where('is_expired', 0)
            ->get();
        $jumlahBimbinganPembimbingPertama = count($bimbinganPembimbingPertama);

        $bimbinganPembimbingKedua = DB::table('bimbingan')
            ->join('dosen', 'dosen.id', '=', 'bimbingan.dosen_id')
            ->where('mahasiswa_id', $user->id)
            ->where('is_expired', 0)
            ->get();
        $jumlahBimbinganPembimbingKedua = count($bimbinganPembimbingKedua);
        $pengaturan = Pengaturan::with('pengaturanDetail')
            ->where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->first();

        return view('pages.mahasiswa.bimbingan.bimbingan', [
            'title' => 'Bimbingan',
            'subtitle' => '',
            'pembimbing' => $pembimbing,
            'data' => $data,
            'pembimbingPertama' => $pertama,
            'jumlahBimbinganPembimbingPertama' => $jumlahBimbinganPembimbingPertama,
            'minimumBimbinganPertama' => $pengaturan->pengaturanDetail->minimum_jumlah_bimbingan,
            'pembimbingKedua' => $kedua,
            'jumlahBimbinganPembimbingKedua' => $jumlahBimbinganPembimbingKedua,
            'minimumBimbinganKedua' => $pengaturan->pengaturanDetail->minimum_jumlah_bimbingan_kedua,
        ]);
    }

    public function mahasiswaStore(Request $request)
    {
        $request->validate([
            'tanggal_bimbingan' => ['required'],
            'dosen' => ['required'],
            'isi_bimbingan' => ['required'],
            'saran' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('bimbingan')->lockForUpdate()->get();
                $active = TahunAjaran::where('status_aktif', 1)->first();
                $mhs = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $dosen = Dosen::where('uuid', $request->dosen)->firstOrFail();
                $pengaturan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->firstOrFail();

                Bimbingan::create([
                    'tahun_ajaran_id' => $active->id,
                    'program_studi_id' => $active->id,
                    'mahasiswa_id' => $mhs->id,
                    'dosen_id' => $dosen->id,
                    'tanggal_bimbingan' => $request->tanggal_bimbingan,
                    'isi_bimbingan' => $request->isi_bimbingan,
                    'saran' => $request->saran,
                    'status' => 2,
                    'note' => null,
                    'available_at_tahun' => $active->tahun,
                    'available_at_semester' => $active->semester,
                    'available_until_tahun' => $pengaturan->pengaturanDetail->tahun_proposal_tersedia_sampai,
                    'available_until_semester' => $pengaturan->pengaturanDetail->semester_proposal_tersedia_sampai,
                    'is_expired' => 0,
                ]);
            });
            return redirect()->back()->with('success', 'Berhasil menambahkan bimbingan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan bimbingan');
        }
    }

    public function mahasiswaEdit($uuid = '')
    {
        try {
            $active = TahunAjaran::where('status_aktif', 1)->first();
            $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
            $pembimbing = DB::table('pembimbing_mahasiswa')
                ->join('dosen as p1', 'p1.id', '=', 'pembimbing_mahasiswa.pembimbing1')
                ->leftJoin('dosen as p2', 'p2.id', '=', 'pembimbing_mahasiswa.pembimbing2')
                ->select(
                    'p1.nama as pembimbing1',
                    'p1.uuid as slug_pembimbing1',
                    'p2.nama as pembimbing2',
                    'p2.uuid as slug_pembimbing2'
                )
                ->where('pembimbing_mahasiswa.mahasiswa', $user->id)
                ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
                ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
                ->first();
            $bimbingan = DB::table('bimbingan')
                ->join('dosen', 'dosen.id', '=', 'bimbingan.dosen_id')
                ->select(
                    'bimbingan.uuid',
                    'bimbingan.tanggal_bimbingan',
                    'bimbingan.isi_bimbingan',
                    'bimbingan.saran',
                    'bimbingan.status',
                    'bimbingan.note',
                    'dosen.uuid as slug_pembimbing'
                )
                ->where('bimbingan.uuid', $uuid)
                ->where('bimbingan.tahun_ajaran_id', $active->id)
                ->where('bimbingan.program_studi_id', $user->program_studi_id)
                ->where('bimbingan.mahasiswa_id', $user->id)
                ->first();
            return view('pages.mahasiswa.bimbingan.edit-bimbingan', [
                'title' => 'Bimbingan',
                'subtitle' => 'Edit',
                'pembimbing' => $pembimbing,
                'data' => $bimbingan,
            ]);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function mahasiswaUpdate(Request $request, $uuid)
    {
        $request->validate([
            'edit_tanggal_bimbingan' => ['required'],
            'edit_dosen' => ['required'],
            'edit_isi_bimbingan' => ['required'],
            'edit_saran' => ['required'],
        ]);

        try {
            $bimbingan = Bimbingan::where('uuid', $uuid)->firstOrFail();
            $dosen = Dosen::where('uuid', $request->edit_dosen)->firstOrFail();

            $bimbingan->dosen_id = $dosen->id;
            $bimbingan->tanggal_bimbingan = $request->edit_tanggal_bimbingan;
            $bimbingan->isi_bimbingan = $request->edit_isi_bimbingan;
            $bimbingan->saran = $request->edit_saran;
            $bimbingan->status = 2;
            $bimbingan->save();

            return redirect()->route('bimbingan')->with('success', 'Berhasil mengubah bimbingan');
        } catch (\Exception $e) {
            return redirect()->route('bimbingan')->with('error', 'Gagal mengubah bimbingan');
        }
    }

    public function dosen()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();

        $pembimbingPertama = DB::table('pembimbing_mahasiswa')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'pembimbing_mahasiswa.mahasiswa')
            ->select(
                'mahasiswa.uuid',
                'mahasiswa.nama',
            )
            ->where('pembimbing_mahasiswa.pembimbing1', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformPertama = $pembimbingPertama->map(function ($row) {
            return [
                'uuid' => $row->uuid,
                'nama' => $row->nama,
                'status_pembimbing' => 1,
            ];
        });

        $pembimbingKedua = DB::table('pembimbing_mahasiswa')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'pembimbing_mahasiswa.mahasiswa')
            ->select(
                'mahasiswa.uuid',
                'mahasiswa.nama',
            )
            ->where('pembimbing_mahasiswa.pembimbing2', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformKedua = $pembimbingKedua->map(function ($row) {
            return [
                'uuid' => $row->uuid,
                'nama' => $row->nama,
                'status_pembimbing' => 2,
            ];
        });

        $data = $transformPertama->merge($transformKedua);

        return view('pages.dosen.bimbingan.bimbingan', [
            'title' => 'Bimbingan',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function dosenFetchAll($uuid)
    {
        try {
            $active = TahunAjaran::where('status_aktif', 1)->first();
            $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
            $mhs = Mahasiswa::where('uuid', $uuid)->firstOrFail();
            $data = null;
            $result = DB::table('pembimbing_mahasiswa')
                ->select(DB::raw("
                    CASE
                        WHEN pembimbing1 = ? THEN 'pembimbing1'
                        WHEN pembimbing2 = ? THEN 'pembimbing2'
                        ELSE 'Not Found'
                    END as column_name
                "))
                ->setBindings([$user->id, $user->id])
                ->where('mahasiswa', $mhs->id)
                ->first();

            if ($result->column_name == 'pembimbing1') {
                $data = DB::table('bimbingan')
                    ->join('mahasiswa', 'mahasiswa.id', '=', 'bimbingan.mahasiswa_id')
                    ->join('pembimbing_mahasiswa', 'pembimbing_mahasiswa.pembimbing1', '=', 'bimbingan.dosen_id')
                    ->select(
                        'bimbingan.uuid',
                        'bimbingan.tanggal_bimbingan',
                        'bimbingan.isi_bimbingan',
                        'bimbingan.saran',
                        'bimbingan.status',
                        'bimbingan.note',
                        'mahasiswa.uuid as mahasiswa',
                        'mahasiswa.nama',
                    )
                    ->where('bimbingan.tahun_ajaran_id', $active->id)
                    ->where('bimbingan.program_studi_id', $user->program_studi_id)
                    ->where('bimbingan.dosen_id', $user->id)
                    ->where('bimbingan.mahasiswa_id', $mhs->id)
                    ->orderBy('bimbingan.tanggal_bimbingan', 'asc')
                    ->get()
                    ->toArray();
            } else if ($result->column_name == 'pembimbing2') {
                $data = DB::table('bimbingan')
                    ->join('mahasiswa', 'mahasiswa.id', '=', 'bimbingan.mahasiswa_id')
                    ->join('pembimbing_mahasiswa', 'pembimbing_mahasiswa.pembimbing2', '=', 'bimbingan.dosen_id')
                    ->select(
                        'bimbingan.uuid',
                        'bimbingan.tanggal_bimbingan',
                        'bimbingan.isi_bimbingan',
                        'bimbingan.saran',
                        'bimbingan.status',
                        'bimbingan.note',
                        'mahasiswa.uuid as mahasiswa',
                        'mahasiswa.nama',
                    )
                    ->where('bimbingan.tahun_ajaran_id', $active->id)
                    ->where('bimbingan.program_studi_id', $user->program_studi_id)
                    ->where('bimbingan.dosen_id', $user->id)
                    ->where('bimbingan.mahasiswa_id', $mhs->id)
                    ->orderBy('bimbingan.tanggal_bimbingan', 'asc')
                    ->get()
                    ->toArray();
            } else {
                throw new Exception('Pembimbing not found');
            }

            return response()->json([
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data',
            ]);
        }
    }

    public function dosenShow($uuid)
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $mhs = Mahasiswa::where('uuid', $uuid)->firstOrFail();

        return view('pages.dosen.bimbingan.detail-bimbingan', [
            'title' => 'Bimbingan',
            'subtitle' => 'Details',
            'mahasiswa' => $mhs,
        ]);
    }

    public function dosenUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => ['required'],
            'status' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $slug = $request->input('slug');
        $status = $request->input('status');

        if ($status == 1) {
            $bimbingan = Bimbingan::where('uuid', $slug)->firstOrFail();
            $bimbingan->update([
                'status' => 1
            ]);
            $bimbingan->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Bimbingan berhasil diapprove',
            ]);
        } elseif ($status == 0) {
            $note = $request->input('note');
            $bimbingan = Bimbingan::where('uuid', $slug)->firstOrFail();
            $bimbingan->update([
                'status' => 0,
                'note' => $note,
            ]);
            $bimbingan->save();
            return response()->json([
                'status' => 'success',
                'message' => 'Bimbingan berhasil direject',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah status bimbingan. Silahkan coba kembali',
            ]);
        }
    }
}
