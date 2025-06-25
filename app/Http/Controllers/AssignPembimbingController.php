<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PembimbingMahasiswa;
use App\Models\PermintaanMahasiswaForm;
use App\Models\TahunAjaran;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssignPembimbingController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = Dosen::where('program_studi_id', $user->program_studi_id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->get()
            ->toArray();
        $form = PermintaanMahasiswaForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->orderBy('ditutup', 'desc')
            ->first();
        return view('pages.prodi.pilihkan-pembimbing.pilihkan-pembimbing', [
            'title' => 'Pilihkan Pembimbing',
            'subtitle' => '',
            'dosen' => $data,
            'form' => $form,
        ]);
    }

    public function fetchPembimbing($id = '')
    {
        if (!$id || $id === '' || empty($id)) {
            return response()->json([
                'data' => [],
            ]);
        } else {
            try {
                $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
                $user = DB::table('dosen')->where('user_id', Auth::user()->id)->first();
                $fetchData = PembimbingMahasiswa::with(['mahasiswaData', 'pembimbing1', 'pembimbing2'])
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->where('mahasiswa', $id)
                    ->first();

                if ($fetchData) {
                    $data = $fetchData->toArray();
                    return response()->json([
                        'status' => 1,
                        'data' => $data,
                    ]);
                } else {
                    $fetchData = Mahasiswa::where('id', $id)
                        ->select('id', 'nama')
                        ->first();
                    return response()->json([
                        'status' => 0,
                        'data' => $fetchData,
                    ]);
                }
                // if ($fetchData) {
                //     $data = $fetchData->toArray();
                //     return response()->json([
                //         'status' => 1,
                //         'data' => $data,
                //     ]);
                // } else {
                //     $fetchData = Mahasiswa::where('id', $id)
                //         ->select('id', 'nama')
                //         ->first();
                //     return response()->json([
                //         'status' => 0,
                //         'data' => $fetchData,
                //     ]);
                // }
            } catch (\Exception $e) {
                return response()->json(['message' => 'Data tidak ditemukan']);
            }
        }
    }

    public function mahasiswaTanpaPembimbing()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $user = DB::table('dosen')->where('user_id', Auth::user()->id)->first();
        // Old
        // $allMahasiswa = Mahasiswa::where('status_aktif_skripsi', 1)->where('tahun_ajaran_id', $active->id)->pluck('id');
        // Old
        $allMahasiswa = Mahasiswa::where('status_aktif_skripsi', 1)->pluck('id');
        $pembimbingMahasiswa =
            PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)->pluck('mahasiswa');
        $differences = $allMahasiswa->diff($pembimbingMahasiswa);

        $mahasiswaArray = [];

        if (count($differences) > 0) {
            foreach ($differences as $row) {
                $fetchData = Mahasiswa::select('id', 'uuid', 'nim', 'nama', 'angkatan')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->id)
                    ->where('id', $row)->firstOrFail()->toArray();
                $mahasiswaArray[] = $fetchData;
            }
        }

        return response()->json([
            'data' => $mahasiswaArray
        ]);
    }

    public function mahasiswaPembimbing()
    {
        $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
        $pembimbingMahasiswa =
            PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
            ->whereNotNull('pembimbing1')
            ->get();

        $mahasiswaArray = [];
        if (count($pembimbingMahasiswa) > 0) {
            foreach ($pembimbingMahasiswa as $row) {
                $fetchData = PembimbingMahasiswa::with(['mahasiswaData', 'pembimbing1', 'pembimbing2'])->where('id', $row->id)->firstOrFail()->toArray();
                $mahasiswaArray[] = $fetchData;
            }
        }

        return response()->json([
            'data' => $mahasiswaArray
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mahasiswa_id' => ['required'],
            'pembimbing1' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        try {
            DB::transaction(function () use ($request) {
                $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
                $id = $request->input('mahasiswa_id');
                $prodi = Mahasiswa::where('id', $id)->firstOrFail();
                $pembimbingPertama = $request->input('pembimbing1');
                $pembimbingKedua = $request->input('pembimbing2');
                $pembimbingMahasiswa = PembimbingMahasiswa::where('mahasiswa', $id)
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $prodi->program_studi_id)
                    ->first();

                if ($pembimbingMahasiswa) {
                    if ($pembimbingPertama != null) {
                        if ($pembimbingKedua != null) {
                            $pembimbingMahasiswa->pembimbing1 = $pembimbingPertama;
                            $pembimbingMahasiswa->pembimbing2 = $pembimbingKedua;
                            $pembimbingMahasiswa->save();
                        } else {
                            $pembimbingMahasiswa->pembimbing1 = $pembimbingPertama;
                            $pembimbingMahasiswa->pembimbing2 = null;
                            $pembimbingMahasiswa->save();
                        }
                    } else {
                        throw new Exception('Pembimbing pertama is null');
                    }
                } else {
                    if ($pembimbingPertama != null) {
                        if ($pembimbingKedua != null) {
                            PembimbingMahasiswa::create([
                                'tahun_ajaran_id' => $active->id,
                                'program_studi_id' => $prodi->program_studi_id,
                                'mahasiswa' => $id,
                                'pembimbing1' => $pembimbingPertama,
                                'pembimbing2' => $pembimbingKedua,
                            ]);
                        } else {
                            PembimbingMahasiswa::create([
                                'tahun_ajaran_id' => $active->id,
                                'program_studi_id' => $prodi->program_studi_id,
                                'mahasiswa' => $id,
                                'pembimbing1' => $pembimbingPertama,
                                'pembimbing2' => null,
                            ]);
                        }
                    } else {
                        throw new Exception('Pembimbing pertama is null');
                    }
                }
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Pembimbing berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan pembimbing',
            ]);
        }
    }
}
