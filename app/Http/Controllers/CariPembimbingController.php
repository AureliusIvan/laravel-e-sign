<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PembimbingMahasiswa;
use App\Models\PermintaanMahasiswa;
use App\Models\PermintaanMahasiswaForm;
use App\Models\ResearchList;
use App\Models\TahunAjaran;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CariPembimbingController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = PermintaanMahasiswaForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();
        $research = ResearchList::where('program_studi_id', $user->program_studi_id)->get();
        $result = DB::table('permintaan_mahasiswa')
            ->join('permintaan_mahasiswa_form', 'permintaan_mahasiswa_form.id', '=', 'permintaan_mahasiswa.permintaan_mahasiswa_form_id')
            ->join('dosen', 'dosen.id', '=', 'permintaan_mahasiswa.dosen_id')
            ->join('research_list', 'research_list.id', '=', 'permintaan_mahasiswa.research_list_id')
            ->select('dosen.nama', 'research_list.topik_penelitian', 'permintaan_mahasiswa.uuid', 'permintaan_mahasiswa.status', 'permintaan_mahasiswa.uuid', 'permintaan_mahasiswa.created_at')
            ->where('permintaan_mahasiswa.mahasiswa_id', $user->id)
            ->where('permintaan_mahasiswa_form.tahun_ajaran_id', $active->id)
            ->where('permintaan_mahasiswa_form.program_studi_id', $user->program_studi_id)
            ->orderBy('permintaan_mahasiswa.status', 'desc')
            ->get()
            ->toArray();

        // $pembimbing = PembimbingMahasiswa::where('mahasiswa', $user->id)
        //     ->where('tahun_ajaran_id', $active->id)
        //     ->where('program_studi_id', $user->program_studi_id)
        //     ->first();

        $status = false;
        $count = 0;
        foreach ($result as $row) {
            if ($row->status === 2) {
                $status = true;
                break;
            }

            if ($row->status === 1) {
                $count++;
            }
        }
        $permintaan = DB::table('permintaan_mahasiswa')
            ->where('mahasiswa_id', $user->id)
            ->where('status', 1)
            ->get()
            ->toArray();
        $total = count($permintaan);

        return view('pages.mahasiswa.pembimbing.cari-pembimbing', [
            'title' => 'Pembimbing',
            'subtitle' => 'Cari Pembimbing',
            'data' => $data,
            'research' => $research,
            'result' => $result,
            'status' => $status,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_form' => ['required'],
            'research_interest' => ['required'],
            'dosen' => ['required'],
            'upload_file' => ['required'],
            'note_mahasiswa' => ['required'],
            'file' => ['sometimes', 'required_if:upload_file,is_rti,is_uploaded', 'file', 'mimes:pdf', 'max:30720'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                // New
                $decryptUuid = Crypt::decrypt($request->dosen);
                $dosen = Dosen::where('uuid', $decryptUuid)->firstOrFail();

                // $dosen = Dosen::where('uuid', $request->dosen)->firstOrFail();
                $research = ResearchList::where('uuid', $request->research_interest)->firstOrFail();
                $form = PermintaanMahasiswaForm::where('uuid', $request->id_form)->firstOrFail();
                $permintaan = DB::table('permintaan_mahasiswa')
                    ->where('status', 1)
                    ->where('mahasiswa_id', $user->id)
                    ->get()
                    ->toArray();

                foreach ($permintaan as $row) {
                    if ($row->dosen_id == $dosen->id) {
                        throw new Exception('Pembimbing sudah ada');
                    }
                }

                if (count($permintaan) >= 2) {
                    throw new Exception('Pembimbing sudah penuh');
                }

                if ($request->upload_file === 'is_rti') {
                    $file = $request->file('file');
                    $clientName = $file->getClientOriginalName();
                    $fileName = $clientName;
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();

                    if (count($permintaan) === 0) {
                        PermintaanMahasiswa::create([
                            'permintaan_mahasiswa_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'dosen_id' => $dosen->id,
                            'research_list_id' => $research->id,
                            'is_rti' => true,
                            'is_uploaded' => false,
                            'file_pendukung' => $fileName,
                            'file_pendukung_random' => $fileNameRandom,
                            'note_mahasiswa' => $request->note_mahasiswa,
                            'status_pembimbing' => 1,
                            'status' => 2,
                            'note_dosen' => null,
                        ]);
                    } else {
                        PermintaanMahasiswa::create([
                            'permintaan_mahasiswa_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'dosen_id' => $dosen->id,
                            'research_list_id' => $research->id,
                            'is_rti' => true,
                            'is_uploaded' => false,
                            'file_pendukung' => $fileName,
                            'file_pendukung_random' => $fileNameRandom,
                            'note_mahasiswa' => $request->note_mahasiswa,
                            'status_pembimbing' => 2,
                            'status' => 2,
                            'note_dosen' => null,
                        ]);
                    }

                    $file->storeAs('uploads/pendukung', $fileNameRandom);
                } elseif ($request->upload_file === 'is_uploaded') {
                    $file = $request->file('file');
                    $clientName = $file->getClientOriginalName();
                    $fileName = $clientName;
                    $fileNameRandom = date('YmdHis') . '_' . $file->hashName();

                    if (count($permintaan) === 0) {
                        PermintaanMahasiswa::create([
                            'permintaan_mahasiswa_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'dosen_id' => $dosen->id,
                            'research_list_id' => $research->id,
                            'is_rti' => false,
                            'is_uploaded' => true,
                            'file_pendukung' => $fileName,
                            'file_pendukung_random' => $fileNameRandom,
                            'note_mahasiswa' => $request->note_mahasiswa,
                            'status_pembimbing' => 1,
                            'status' => 2,
                            'note_dosen' => null,
                        ]);
                    } else {
                        PermintaanMahasiswa::create([
                            'permintaan_mahasiswa_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'dosen_id' => $dosen->id,
                            'research_list_id' => $research->id,
                            'is_rti' => false,
                            'is_uploaded' => true,
                            'file_pendukung' => $fileName,
                            'file_pendukung_random' => $fileNameRandom,
                            'note_mahasiswa' => $request->note_mahasiswa,
                            'status_pembimbing' => 2,
                            'status' => 2,
                            'note_dosen' => null,
                        ]);
                    }

                    $file->storeAs('uploads/pendukung', $fileNameRandom);
                } else {
                    if (count($permintaan) === 0) {
                        PermintaanMahasiswa::create([
                            'permintaan_mahasiswa_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'dosen_id' => $dosen->id,
                            'research_list_id' => $research->id,
                            'is_rti' => false,
                            'is_uploaded' => false,
                            'file_pendukung' => null,
                            'file_pendukung_random' => null,
                            'note_mahasiswa' => $request->note_mahasiswa,
                            'status_pembimbing' => 1,
                            'status' => 2,
                            'note_dosen' => null,
                        ]);
                    } else {
                        PermintaanMahasiswa::create([
                            'permintaan_mahasiswa_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'dosen_id' => $dosen->id,
                            'research_list_id' => $research->id,
                            'is_rti' => false,
                            'is_uploaded' => false,
                            'file_pendukung' => null,
                            'file_pendukung_random' => null,
                            'note_mahasiswa' => $request->note_mahasiswa,
                            'status_pembimbing' => 2,
                            'status' => 2,
                            'note_dosen' => null,
                        ]);
                    }
                }
            });
            return redirect()->back()->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal')->withInput();
        }
    }

    public function show($uuid)
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $data = DB::table('permintaan_mahasiswa')
            ->join('permintaan_mahasiswa_form', 'permintaan_mahasiswa_form.id', '=', 'permintaan_mahasiswa.permintaan_mahasiswa_form_id')
            ->join('dosen', 'dosen.id', '=', 'permintaan_mahasiswa.dosen_id')
            ->join('research_list', 'research_list.id', '=', 'permintaan_mahasiswa.research_list_id')
            ->select('dosen.nama', 'research_list.topik_penelitian', 'permintaan_mahasiswa.uuid', 'permintaan_mahasiswa.status_pembimbing', 'permintaan_mahasiswa.status', 'permintaan_mahasiswa.note_mahasiswa', 'permintaan_mahasiswa.note_dosen', 'permintaan_mahasiswa.is_rti', 'permintaan_mahasiswa.is_uploaded', 'permintaan_mahasiswa.file_pendukung', 'permintaan_mahasiswa.file_pendukung_random', 'permintaan_mahasiswa.created_at')
            ->where('permintaan_mahasiswa.uuid', $uuid)
            ->where('permintaan_mahasiswa.mahasiswa_id', $user->id)
            ->where('permintaan_mahasiswa_form.tahun_ajaran_id', $active->id)
            ->where('permintaan_mahasiswa_form.program_studi_id', $user->program_studi_id)
            ->orderBy('permintaan_mahasiswa.status', 'desc')
            ->first();

        return view('pages.mahasiswa.pembimbing.detail-cari-pembimbing', [
            'title' => 'Pembimbing',
            'subtitle' => 'Detail Cari Pembimbing',
            'data' => $data,
        ]);
    }

    public function getFile($uuid)
    {
        $data = PermintaanMahasiswa::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/pendukung/' . $data->file_pendukung_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_pendukung);
        } else {
            abort(404);
        }
    }

    public function listDosen()
    {
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $data =
            Dosen::whereHas('researchDosen', function ($query) use ($user) {
                $query->where('program_studi_id', $user->program_studi_id);
            })->with(['researchDosen.researchList' => function ($query) use ($user) {
                $query->where('program_studi_id', $user->program_studi_id);
            }])
            ->where('status_aktif', 1)
            ->get(['id', 'uuid', 'program_studi_id', 'nama']);
        // $data =
        //     Dosen::whereHas('researchDosen', function ($query) use ($user) {
        //         $query->where('program_studi_id', $user->program_studi_id);
        //     })->with(['researchDosen.researchList' => function ($query) use ($user) {
        //         $query->where('program_studi_id', $user->program_studi_id);
        //     }])->get(['id', 'uuid', 'program_studi_id', 'nama']);
        return view('pages.mahasiswa.pembimbing.list-dosen-pembimbing', [
            'title' => 'Pembimbing',
            'subtitle' => 'List Dosen Pembimbing',
            'data' => $data,
        ]);
    }

    public function fetchDosen($slug = '')
    {
        if (!$slug || $slug === '' || empty($slug)) {
            return response()->json([
                'dosen' => [],
            ], 404);
        } else {
            try {
                $uuid = $slug;
                $research = DB::table('research_list')->where('uuid', $uuid)->first();
                $user = DB::table('mahasiswa')->where('user_id', Auth::user()->id)->first();
                $dosen = DB::table('dosen')
                    ->join('research_dosen', 'research_dosen.dosen_id', '=', 'dosen.id')
                    ->select('dosen.uuid', 'dosen.nama')
                    ->where('dosen.program_studi_id', $user->program_studi_id)
                    ->where('dosen.status_aktif', 1)
                    ->where('research_dosen.research_list_id', $research->id)
                    ->get();

                // $dosen = DB::table('dosen')
                //     ->join('research_dosen', 'research_dosen.dosen_id', '=', 'dosen.id')
                //     ->select('dosen.uuid', 'dosen.nama')
                //     ->where('dosen.program_studi_id', $user->program_studi_id)
                //     ->where('dosen.status_aktif', 1)
                //     ->where('research_dosen.research_list_id', $research->id)
                //     ->get()
                //     ->toArray();

                // New
                $encryptedData = $dosen->map(function ($item) {
                    $item->uuid = Crypt::encrypt($item->uuid);
                    return $item;
                });

                return response()->json([
                    'dosen' => $encryptedData,
                ], 200);

                // return response()->json([
                //     'dosen' => $dosen,
                // ], 200);

            } catch (\Exception $e) {
                return response()->json(['message' => 'Dosen tidak ditemukan'], 404);
            }
        }
    }
}
