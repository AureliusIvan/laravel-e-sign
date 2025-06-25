<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\ResearchDosen;
use App\Models\ResearchList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResearchDosenController extends Controller
{
    public function index()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = Dosen::whereHas('researchDosen', function ($query) use ($user) {
            $query->where('program_studi_id', $user->program_studi_id);
        })->with(['researchDosen.researchList' => function ($query) use ($user) {
            $query->where('program_studi_id', $user->program_studi_id);
        }])->get();
        return view('pages.prodi.research-dosen.research-dosen', [
            'title' => 'Research',
            'subtitle' => 'Research Dosen',
            'data' => $data,
        ]);
    }

    public function show()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = Dosen::whereHas('researchDosen', function ($query) use ($user) {
            $query->where('program_studi_id', $user->program_studi_id);
        })->with(['researchDosen.researchList' => function ($query) use ($user) {
            $query->where('program_studi_id', $user->program_studi_id);
        }])->find($user->id);

        return view('pages.dosen.topik-penelitian.topik-penelitian', [
            'title' => 'Topik Penelitian Saya',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function create()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $prodi = ProgramStudi::where('id', $user->program_studi_id)->first();
        $dosen = DB::table('dosen')
            ->select('*')
            ->where('program_studi_id', $user->program_studi_id)
            ->get()
            ->toArray();
        $available = DB::table('research_dosen')
            ->join('dosen', 'dosen.id', '=', 'research_dosen.dosen_id')
            ->select('dosen.uuid')
            ->distinct()
            ->get()
            ->toArray();
        $research = ResearchList::where('program_studi_id', $user->program_studi_id)->get();
        return view('pages.prodi.research-dosen.add-research-dosen', [
            'title' => 'Research',
            'subtitle' => 'Tambah Research Dosen',
            'prodi' => $prodi->uuid,
            'dosen' => $dosen,
            'available' => $available,
            'research' => $research,
        ]);
    }

    public function store(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'dosen' => ['required'],
            'topik_penelitian' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request) {
                DB::table('research_dosen')->lockForUpdate()->get();
                $program = ProgramStudi::where('uuid', $request->program_studi)->firstOrFail();
                $dosen = Dosen::where('uuid', $request->dosen)->firstOrFail();

                foreach ($request->topik_penelitian as $row) {
                    $research = ResearchList::where('uuid', $row)->firstOrFail();

                    ResearchDosen::create([
                        'program_studi_id' => $program->id,
                        'dosen_id' => $dosen->id,
                        'research_list_id'  => $research->id,
                    ]);
                }
            });

            if ($request->action === 'Save') {
                return redirect()->route('research.dosen')->with('success', 'Data berhasil ditambahkan');
            } elseif ($request->action === 'Save and Create Another') {
                return redirect()->back()->with('success', 'Data berhasil ditambahkan');
            } else {
                return redirect()->route('research.dosen');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan data. Silahkan coba kembali')->withInput();
        }
    }

    public function edit($uuid)
    {
        try {
            $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
            $dosen = Dosen::where('uuid', $uuid)->firstOrFail();
            $research = ResearchList::where('program_studi_id', $user->program_studi_id)->get();
            $prodi = ProgramStudi::where('id', $user->program_studi_id)->firstOrFail();
            $data = Dosen::whereHas('researchDosen', function ($query) use ($user) {
                $query->where('program_studi_id', $user->program_studi_id);
            })->with(['researchDosen.researchList' => function ($query) use ($user) {
                $query->where('program_studi_id', $user->program_studi_id);
            }])->find($dosen->id);
            return view('pages.prodi.research-dosen.edit-research-dosen', [
                'title' => 'Research',
                'subtitle' => 'Edit Research Dosen',
                'isLinked' => false,
                'data' => $data,
                'prodi' => $prodi->uuid,
                'research' => $research,
            ]);
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function update(Request $request, $uuid)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $request->validate([
            'prodi' => ['required'],
            'research_before' => ['required'],
            'topik_penelitian' => ['required'],
        ]);

        try {
            DB::transaction(function () use ($request, $uuid) {
                DB::table('research_dosen')->lockForUpdate();
                $dosen = Dosen::where('uuid', $uuid)->firstOrFail();
                $program = ProgramStudi::where('uuid', $request->prodi)->firstOrFail();
                DB::table('research_dosen')->where('dosen_id', $dosen->id)->delete();

                foreach ($request->topik_penelitian as $row) {
                    $research = ResearchList::where('uuid', $row)->firstOrFail();

                    ResearchDosen::create([
                        'program_studi_id' => $program->id,
                        'dosen_id' => $dosen->id,
                        'research_list_id'  => $research->id,
                    ]);
                }
            });

            // $data = [];
            // $researchDosen = [];
            // $new = [];
            // foreach ($request->research_before as $row) {
            //     $list = ResearchDosen::with('researchList')->where('uuid', $row)->firstOrFail();
            //     if ($list) {
            //         array_push($data, $list->researchList->uuid);
            //         $researchDosen[$row] = $list->researchList->uuid;
            //     }
            // }
            // $news = array_diff($request->topik_penelitian, $data);
            // $diff = array_diff($researchDosen, $request->topik_penelitian);
            // foreach ($diff as $key => $row) {
            //     array_push($new, $key);
            // }
            // dd($request->topik_penelitian);

            // DB::transaction(function () use ($request, $news, $uuid, $diff) {
            //     DB::table('research_dosen')->lockForUpdate()->get();

            //     foreach ($diff as $key => $row) {
            //         DB::table('research_dosen')->where('uuid', $key)->delete();
            //     }

            //     $dosen = Dosen::where('uuid', $uuid)->firstOrFail();
            //     foreach ($news as $new) {
            //         $research = ResearchList::where('uuid', $new)->firstOrFail();

            //         ResearchDosen::create([
            //             'program_studi_id' => $request->prodi,
            //             'dosen_id' => $dosen->id,
            //             'research_list_id' => $research->id,
            //         ]);
            //     }
            // });

            return redirect()->route('research.dosen')->with('success', 'Data berhasil diubah');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $uuid = $request->input('slug');
        try {
            $dosen = Dosen::where('uuid', $uuid)->firstOrFail();
            DB::transaction(function () use ($dosen) {
                DB::table('research_dosen')->where('dosen_id', $dosen->id)->delete();
            });
            return redirect()->route('research.dosen')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus karena terhubung dengan tabel lain');
        }
    }
}
