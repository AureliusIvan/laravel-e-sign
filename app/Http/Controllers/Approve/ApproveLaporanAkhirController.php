<?php

namespace App\Http\Controllers\Approve;

use App\Models\Dosen;
use App\Models\TahunAjaran;
use App\Models\LaporanAkhir;
use Illuminate\Http\Request;
use App\Models\LaporanAkhirForm;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApproveLaporanAkhirController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = LaporanAkhirForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.prodi.approve-laporan.approve-laporan', [
            'title' => 'Approve',
            'subtitle' => 'Laporan Skripsi',
            'data' => $data
        ]);
    }

    public function show($segment)
    {
        $form = LaporanAkhirForm::where('uuid', $segment)->first();
        $user = Dosen::where('user_id', Auth::user()->id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->firstOrFail();
        $data = LaporanAkhir::with('laporanAkhirForm')
            ->with('pembimbingPertama', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('pembimbingKedua', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
            ->where('laporan_akhir_form_id', $form->id)
            ->get();
        return view('pages.prodi.approve-laporan.detail-approve-laporan', [
            'title' => 'Approve',
            'subtitle' => 'Laporan Skripsi',
            'data' => $data,
            'dosen' => $user,
            'form' => $form,
        ]);
    }

    public function filePembimbing1($uuid)
    {
        $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-laporan-akhir/' . $data->file_random_pembimbing1;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_pembimbing1);
        } else {
            abort(404);
        }
    }

    public function filePembimbing2($uuid)
    {
        $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-laporan-akhir/' . $data->file_random_pembimbing2;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_pembimbing2);
        } else {
            abort(404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'pembimbing' => ['required', 'integer'],
            'laporan_id' => ['required'],
            'status' => ['required', 'integer'],
            'action' => ['required'],
            'note' => ['sometimes', 'required_if:status,0,3,4'],
            'file_upload' => ['sometimes', 'required_if:status,1', 'file', 'mimes:pdf', 'max:30720']
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('laporan_akhir')->lockForUpdate()->get();
                if ($request->hasFile('file_upload')) {
                    $file = $request->file('file_upload');
                    $pembimbing = $request->pembimbing;
                    $fileName = $file->getClientOriginalName();
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                    $now = date('Y-m-d');
                    $laporan = LaporanAkhir::where('uuid', $request->laporan_id)->firstOrFail();

                    if ($pembimbing == 3) {
                        $path = 'uploads/approve-laporan-akhir/' . $laporan->file_random_kaprodi;
                        if ($laporan->file_random_kaprodi != null) {
                            if (Storage::exists($path)) {
                                Storage::delete($path);
                            }
                        }
                        $laporan->file_kaprodi = $fileName;
                        $laporan->file_random_kaprodi = $fileNameRandom;
                        $laporan->status_approval_kaprodi = $request->status;
                        $laporan->tanggal_approval_kaprodi = $now;
                        if ($request->status == 1) {
                            $laporan->status_akhir = 1;
                        }
                        $laporan->save();
                        $file->storeAs('uploads/approve-laporan-akhir', $fileNameRandom);
                    } else {
                        throw new Exception('Pembimbing Not Found');
                    }
                } elseif (!$request->hasFile('file_upload')) {
                    $pembimbing = $request->pembimbing;
                    $laporan = LaporanAkhir::where('uuid', $request->laporan_id)->firstOrFail();

                    if ($request->status == 1) {
                        throw new Exception('Unknown error');
                    }

                    if ($pembimbing == 3) {
                        $path = 'uploads/approve-laporan-akhir/' . $laporan->file_random_kaprodi;
                        if ($laporan->file_random_kaprodi != null) {
                            if (Storage::exists($path)) {
                                Storage::delete($path);
                            }
                        }
                        $laporan->status_approval_kaprodi = $request->status;
                        $laporan->save();
                    } else {
                        throw new Exception('Pembimbing not found');
                    }
                } else {
                    throw new Exception('Error Processing Request');
                }
            });
            return redirect()->back()->with('success', 'Berhasil');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal. Silahkan coba kembali')->withInput();
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'slug' => ['required'],
            'pembimbing' => ['required', 'integer'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $data = LaporanAkhir::where('uuid', $request->slug)->firstOrFail();
                $pembimbing = (int)$request->pembimbing;

                if ($pembimbing == 3) {
                    $path = 'uploads/periksa-laporan-akhir/' . $data->file_random_kaprodi;
                    if (Storage::exists($path)) {
                        if (Storage::delete($path)) {
                            $data->file_kaprodi = null;
                            $data->file_random_kaprodi = null;
                            $data->status_approval_kaprodi = 2;
                            $data->tanggal_approval_kaprodi = null;
                            $data->save();
                        }
                    }
                } else {
                    throw new Exception('Pembimbing not found');
                }
            });
            return redirect()->back()->with('success', 'File berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal. Silahkan coba kembali')->withInput();
        }
    }

    public function fileKaprodi($uuid)
    {
        $data = LaporanAkhir::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/approve-laporan-akhir/' . $data->file_random_kaprodi;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_kaprodi);
        } else {
            abort(404);
        }
    }
}
