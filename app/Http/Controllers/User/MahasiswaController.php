<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\ProposalSkripsi;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class MahasiswaController extends Controller
{
    public function index()
    {
        $data = Mahasiswa::with('programStudi')->get();
        return view('pages.admin.user-mahasiswa.user-mahasiswa', compact('data'), [
            'title' => 'Akun',
            'subtitle' => 'Mahasiswa',
        ]);
    }

    public function create()
    {
        $program = ProgramStudi::all();
        return view('pages.admin.user-mahasiswa.add-user-mahasiswa', compact('program'), [
            'title' => 'Akun',
            'subtitle' => 'Tambah Mahasiswa'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim' => ['required', 'string', 'max:20', 'unique:' . Mahasiswa::class],
            'nama' => ['required', 'string', 'max:255'],
            'program_studi' => ['required'],
            'angkatan' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 5)],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class, 'ends_with:umn.ac.id'],
            'password' => ['required', 'confirmed', Rules\Password::default(), 'min:8'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('users')->lockForUpdate()->get();

                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'mahasiswa',
                ]);

                $lastUserId = $user->id;
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();

                Mahasiswa::create([
                    'user_id' => $lastUserId,
                    'nim' => $request->nim,
                    'nama' => $request->nama,
                    'program_studi_id' => $program->id,
                    'angkatan' => $request->angkatan,
                    'status_aktif_skripsi' => false,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('mahasiswa')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('mahasiswa');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan registrasi. Silahkan coba kembali');
        }
    }

    public function filter(Request $request)
    {
        $query = Mahasiswa::query();

        if ($request->has('filter')) {
            $query->where('program_studi', $request->input('filter'));
        }

        $data = $query->get();
        return view('pages.admin.user-mahasiswa.user-mahasiswa', compact('data'), [
            'title' => 'Akun',
            'subtitle' => 'Mahasiswa',
        ]);
    }

    public function edit($uuid)
    {
        try {
            $data = Mahasiswa::where('uuid', $uuid)->with('programStudi')->firstOrFail();
            $program = ProgramStudi::all();
            $isLinked = false;
            if (Bimbingan::where('mahasiswa_id', $data->id)->exists()) {
                $isLinked = true;
            }
            if (ProposalSkripsi::where('mahasiswa_id', $data->id)->exists()) {
                $isLinked = true;
            }

            return view('pages.admin.user-mahasiswa.edit-user-mahasiswa', compact('data'), [
                'title' => 'Akun',
                'subtitle' => 'Edit Mahasiswa',
                'isLinked' => $isLinked,
                'program' => $program,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'program_studi' => ['required'],
            'angkatan' => ['required'],
        ]);

        try {
            $mahasiswa = Mahasiswa::where('uuid', $uuid)->firstOrFail();
            $mahasiswa->update($validated);
            return redirect()->route('mahasiswa')->with('success', 'Data berhasil diubah');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $mahasiswa = Mahasiswa::where('uuid', $uuid)->firstOrFail();
            $mahasiswa->delete();
            return redirect()->route('mahasiswa')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }

    public function createTahunSkripsi()
    {
        $tahun = DB::table('tahun_ajaran')
            ->select('uuid', 'tahun', 'semester')
            ->get()
            ->toArray();
        $angkatan = DB::table('mahasiswa')
            ->distinct()
            ->orderBy('angkatan', 'asc')
            ->pluck('angkatan');
        return view('pages.admin.user-mahasiswa.add-tahun-skripsi', [
            'title' => 'Akun',
            'subtitle' => 'Tahun Skripsi Mahasiswa',
            'tahun' => $tahun,
            'angkatan' => $angkatan
        ]);
    }

    public function storeTahunSkripsi(Request $request)
    {
        $request->validate([
            'angkatan' => ['required'],
            'tahun_ajaran' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('mahasiswa')->lockForUpdate()->get();

                $tahun = TahunAjaran::where('uuid', $request->tahun_ajaran)->firstOrFail();

                Mahasiswa::query()->update(['status_aktif_skripsi' => false]);
                // Updating this
                if ($tahun->status_aktif === 1) {
                    Mahasiswa::where('angkatan', $request->angkatan)
                        ->update([
                            'status_aktif_skripsi' => true,
                            'tahun_ajaran_id' => $tahun->id,
                        ]);
                } else {
                    Mahasiswa::where('angkatan', $request->angkatan)
                        ->update([
                            'status_aktif_skripsi' => false,
                            'tahun_ajaran_id' => $tahun->id,
                        ]);
                }
            });
            $message = 'Mahasiswa angkatan ' . $request->angkatan . ' dapat melakukan skripsi';
            return redirect()->route('mahasiswa')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengaktifkan status mahasiswa. Silahkan dicoba kembali');
        }
    }

    public function updateStatus($uuid)
    {
        try {
            // New
            $active = TahunAjaran::where('status_aktif', 1)->firstOrFail();
            // 
            $mahasiswa = Mahasiswa::where('uuid', $uuid)->firstOrFail();

            // New
            $mahasiswa->tahun_ajaran_id = $active->id;
            // 
            $mahasiswa->status_aktif_skripsi = !$mahasiswa->status_aktif_skripsi;
            $mahasiswa->save();

            $message = null;
            if ($mahasiswa->status_aktif_skripsi === false) {
                $message = 'Status mahasiswa ' . $mahasiswa->nama . ' berhasil dinonaktifkan';
                return redirect()->back()->with('success', $message);
            } else if ($mahasiswa->status_aktif_skripsi === true) {
                $message = 'Status mahasiswa ' . $mahasiswa->nama . ' berhasil diaktifkan';
                return redirect()->back()->with('success', $message);
            }
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }
}
