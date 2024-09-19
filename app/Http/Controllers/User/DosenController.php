<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bimbingan;
use Illuminate\Validation\Rules;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\ProposalSkripsi;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DosenController extends Controller
{
    public function index()
    {
        $data = DB::table('dosen')
            ->join('users', 'users.id', '=', 'dosen.user_id')
            ->join('program_studi', 'program_studi.id', '=', 'dosen.program_studi_id')
            ->select('dosen.*', 'dosen.status_aktif', 'users.role', 'program_studi.program_studi')
            ->get();
        return view('pages.admin.user-dosen.user-dosen', compact('data'), [
            'title' => 'Akun',
            'subtitle' => 'Dosen'
        ]);
    }

    public function create()
    {
        $program = ProgramStudi::all();
        return view('pages.admin.user-dosen.add-user-dosen', compact('program'), [
            'title' => 'Akun',
            'subtitle' => 'Tambah Dosen'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nid' => ['required', 'unique:' . Dosen::class],
            'nama' => ['required', 'string', 'max:255'],
            'gelar' => ['required'],
            'program_studi' => ['required'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class, 'ends_with:umn.ac.id'],
            'password' => ['required', 'confirmed', Rules\Password::default(), 'min:8'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('users')->lockForUpdate()->get();

                $user = User::create([
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'role' => 'dosen',
                ]);

                $lastUserId = $user->id;
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstorFail();

                Dosen::create([
                    'user_id' => $lastUserId,
                    'nid' => $request->nid,
                    'nama' => $request->nama,
                    'gelar' => $request->gelar,
                    'program_studi_id' => $program->id,
                    'status_aktif' => true,
                ]);
            });

            if ($request->action === 'Save') {
                return redirect()->route('dosen')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('dosen');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal melakukan registrasi. Silahkan coba kembali');
        }
    }

    public function edit($uuid)
    {
        try {
            $findData = Dosen::where('uuid', $uuid)->with('programStudi')->firstOrFail();
            $data = DB::table('dosen')
                ->join('users', 'users.id', '=', 'dosen.user_id')
                ->join('program_studi', 'program_studi.id', '=', 'dosen.program_studi_id')
                ->select('dosen.*', 'users.role', 'program_studi.program_studi')
                ->where('dosen.uuid', $uuid)
                ->first();
            $program = ProgramStudi::all();
            $isLinked = false;
            if (Bimbingan::where('dosen_id', $findData->id)->exists()) {
                $isLinked = true;
            }
            if (ProposalSkripsi::where('penilai1', $findData->id)->orWhere('penilai2')->orWhere('penilai3')->exists()) {
                $isLinked = true;
            }

            return view('pages.admin.user-dosen.edit-user-dosen', compact('data'), [
                'title' => 'Akun',
                'subtitle' => 'Edit Dosen',
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
            'gelar' => ['required'],
            'program_studi' => ['required'],
        ]);

        try {
            $dosen = Dosen::where('uuid', $uuid)->firstOrFail();

            if ($request->roleBefore === 'dosen' && $request->role === 'kaprodi') {
                DB::transaction(function () use ($request) {
                    DB::table('users')->lockForUpdate()->get();
                    $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();

                    $kaprodi = DB::table('users')
                        ->join('dosen', 'dosen.user_id', '=', 'users.id')
                        ->select('users.role', 'dosen.*')
                        ->where('users.role', 'kaprodi')
                        ->where('dosen.program_studi_id', $program->id)
                        ->first();
                    if ($kaprodi) {
                        DB::table('users')
                            ->where('users.id', $kaprodi->user_id)
                            ->update(['role' => 'dosen']);
                    }
                });
            } elseif ($request->roleBefore === 'dosen' && $request->role === 'sekprodi') {
                DB::transaction(function () use ($request) {
                    DB::table('users')->lockForUpdate()->get();
                    $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();

                    $sekprodi = DB::table('users')
                        ->join('dosen', 'dosen.user_id', '=', 'users.id')
                        ->select('users.role', 'dosen.*')
                        ->where('users.role', 'sekprodi')
                        ->where('dosen.program_studi_id', $program->id)
                        ->first();
                    if ($sekprodi) {
                        DB::table('users')
                            ->where('users.id', $sekprodi->user_id)
                            ->update(['role' => 'dosen']);
                    }
                });
            }

            User::where('id', $dosen->user_id)->update(['role' => $request->role]);
            $dosen->update($validated);
            return redirect()->route('dosen')->with('success', 'Data berhasil diubah');
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Gagal mengubah data. Silahkan coba kembali');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            $dosen = Dosen::where('uuid', $uuid)->firstOrFail();
            $dosen->delete();
            return redirect()->route('dosen')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }

    public function updateStatus($uuid)
    {
        try {
            $dosen = Dosen::where('uuid', $uuid)->firstOrFail();
            $dosen->status_aktif = !$dosen->status_aktif;
            $dosen->save();

            $message = null;
            if ($dosen->status_aktif === false) {
                $message = 'Status dosen ' . $dosen->nama . ' berhasil dinonaktifkan';
                return redirect()->back()->with('success', $message);
            } else if ($dosen->status_aktif === true) {
                $message = 'Status dosen ' . $dosen->nama . ' berhasil diaktifkan';
                return redirect()->back()->with('success', $message);
            }
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }
}
