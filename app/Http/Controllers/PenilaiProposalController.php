<?php

namespace App\Http\Controllers;

use App\Exports\ProposalSkripsiTemplateExport;
use App\Models\Dosen;
use App\Models\ProposalSkripsi;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\ProposalSkripsiForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class PenilaiProposalController extends Controller
{
    public function index()
    {
        // If user is not kaprodi or sekprodi
        if (Auth::user()->role !== 'kaprodi' && Auth::user()->role !== 'sekprodi') {
            abort(404);
        }

        $now = date('Y-m-d H:i:s');
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('ditutup', '<', $now)
            ->orderBy('dibuka', 'asc')
            ->get();
        return view('pages.prodi.penilai-proposal.penilai-proposal', [
            'title' => 'Penilai Proposal',
            'subtitle' => '',
            'data' => $data,
        ]);
    }

    public function show($uuid)
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = ProposalSkripsiForm::with('proposalSkripsi')
            ->with('proposalSkripsi.penilaiPertama')
            ->with('proposalSkripsi.penilaiKedua')
            ->with('proposalSkripsi.penilaiKetiga')
            ->with('proposalSkripsi.mahasiswa')
            ->with('proposalSkripsi.kodePenelitianProposal.areaPenelitian')
            ->where('uuid', $uuid)
            ->get();
        $penilai = Dosen::where('program_studi_id', $user->program_studi_id)
            ->where('status_aktif', 1)
            ->get();
        return view('pages.prodi.penilai-proposal.detail-penilai-proposal', [
            'title' => 'Penilai Proposal',
            'subtitle' => '',
            // 'data' => $data,
            // 'penilai' => $penilai,
        ]);
    }

    public function fetchData($segment = '')
    {
        $form = ProposalSkripsiForm::where('uuid', $segment)->first();
        $data = ProposalSkripsi::with([
            'proposalSkripsiForm',
            'penilaiPertama',
            'penilaiKedua',
            'penilaiKetiga',
            'mahasiswa',
            // 'kodePenelitianProposal.areaPenelitian'
            'topikPenelitianProposal.researchList'
        ])
            ->where('proposal_skripsi_form_id', $form->id)
            ->get();

        if (!$data) {
            return response()->json([
                'data' => null
            ]);
        } else {
            return response()->json([
                'data' => $data
            ]);
        }
    }

    public function fetchDosen()
    {
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $data = Dosen::where('program_studi_id', $user->program_studi_id)
            ->where('status_aktif', 1)
            ->select('id', 'nama')
            ->get()
            ->toArray();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required'],
            'penilai1' => ['required'],
            'penilai2' => ['required'],
            'penilai3' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        try {
            DB::transaction(function () use ($request) {
                DB::table('proposal_skripsi')->lockForUpdate()->get();
                $proposal = ProposalSkripsi::where('id', $request->id)->firstOrFail();

                $proposal->penilai1 = $request->penilai1;
                $proposal->penilai2 = $request->penilai2;
                $proposal->penilai3 = $request->penilai3;
                $proposal->save();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Penilai berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan penilai',
            ]);
        }
    }
}
