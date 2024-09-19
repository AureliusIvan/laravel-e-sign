<?php

namespace App\Http\Controllers\Pengumpulan;

use Exception;
use App\Models\Mahasiswa;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use App\Models\ProposalSkripsi;
use Illuminate\Support\Facades\DB;
use App\Models\ProposalSkripsiForm;
use App\Http\Controllers\Controller;
use App\Models\AreaPenelitian;
use App\Models\KodePenelitianProposal;
use App\Models\PembimbingMahasiswa;
use App\Models\ResearchList;
use App\Models\TopikPenelitianProposal;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;
use function Symfony\Component\VarDumper\Dumper\esc;

class ProposalSkripsiController extends Controller
{
    public function index()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();
        $result = ProposalSkripsi::with('proposalSkripsiForm')
            ->where('mahasiswa_id', $user->id)
            ->orderBy('proposal_skripsi_form_id', 'desc')
            ->get();
        $research = AreaPenelitian::with('researchList')->get();
        $topik = ResearchList::all();
        if (count($result) <= 0) {
            $result = [];
        }
        return view('pages.mahasiswa.proposal-skripsi.proposal-skripsi', [
            'title' => 'Proposal Skripsi',
            'subtitle' => 'Pengumpulan',
            'data' => $data,
            'result' => $result,
            'research' => $research,
            'topik' => $topik,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul_proposal' => ['required', 'string'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:30720'],
            'topik_penelitian' => ['required'],
            'id_form' => ['required'],
        ]);

        // dd($request->all());

        try {
            DB::transaction(function () use ($request) {
                DB::table('proposal_skripsi')->lockForUpdate()->get();
                $active = TahunAjaran::where('status_aktif', 1)->first();
                $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
                $form = ProposalSkripsiForm::where('uuid', $request->id_form)->firstOrFail();
                $pembimbing = PembimbingMahasiswa::where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->where('mahasiswa', $user->id)
                    ->first();
                $pembimbingPertama = null;
                $pembimbingKedua = null;
                if ($pembimbing !== null) {
                    $pembimbingPertama = $pembimbing->pembimbing1;
                    $pembimbingKedua = $pembimbing->pembimbing2;
                }

                $isPenamaan = Pengaturan::with('pengaturanDetail')
                    ->where('tahun_ajaran_id', $active->id)
                    ->where('program_studi_id', $user->program_studi_id)
                    ->first();

                if ($isPenamaan->penamaan_proposal == 1) {
                    $format = explode("_", $isPenamaan->pengaturanDetail->penamaan_proposal);
                    $countFormat = count($format);

                    if ($countFormat != 3) {
                        throw new Exception('Format invalid');
                    }

                    $escJudul = esc($request->judul_proposal);
                    $countWordJudul = str_word_count($escJudul);

                    if ($countWordJudul < 4) {
                        throw new Exception('Jumlah kata kurang');
                    } else {
                        $file = $request->file('file');
                        $clientName = $file->getClientOriginalName();
                        $mimeType = $file->getClientMimeType();
                        $fileNameRandom = date('YmdHis') . '_' . $file->hashName();
                        $nim = $user->nim;
                        $nama = $user->nama;

                        if ($nama == trim($nama) && str_contains($nama, ' ')) {
                            $nama = str_replace(' ', '', $nama);
                        }
                        // $judul = explode(' ', $escJudul);
                        $firstFormat = $format[0];
                        $secondFormat = $format[1];
                        $thridFormat = $format[2];
                        $fileName = '';

                        if ($firstFormat == 'nim') {
                            if ($secondFormat == 'nama') {
                                $fileName = $nim . '_' . $nama . '_' . 'ProposalSkripsi' . '.pdf';
                            } elseif ($secondFormat == 'judul') {
                                $fileName = $nim . '_' . 'ProposalSkripsi' . '_' . $nama . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } elseif ($firstFormat == 'nama') {
                            if ($secondFormat == 'nim') {
                                $fileName = $nama . '_' . $nim . '_' . 'ProposalSkripsi' . '.pdf';
                            } elseif ($secondFormat == 'judul') {
                                $fileName = $nama . '_' . 'ProposalSkripsi' . '_' . $nim . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } elseif ($firstFormat == 'judul') {
                            if ($secondFormat == 'nim') {
                                $fileName = 'ProposalSkripsi' . '_' . $nim . '_' . $nama . '.pdf';
                            } elseif ($secondFormat == 'nama') {
                                $fileName = 'ProposalSkripsi' . '_' . $nama . '_' . $nim . '.pdf';
                            } else {
                                throw new  Exception("Error Processing Request", 1);
                            }
                        } else {
                            throw new  Exception("Error Processing Request", 1);
                        }

                        $pengaturan = Pengaturan::with('pengaturanDetail')
                            ->where('tahun_ajaran_id', $active->id)
                            ->where('program_studi_id', $user->program_studi_id)
                            ->firstOrFail();

                        $proposal = ProposalSkripsi::create([
                            'proposal_skripsi_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'judul_proposal' => $request->judul_proposal,
                            'file_proposal' => $fileName,
                            'file_proposal_random' => $fileNameRandom,
                            'file_proposal_mime' => $mimeType,
                            'status' => 1,
                            'penilai1' => $pembimbingPertama,
                            'penilai2' => $pembimbingKedua,
                            'available_at_tahun' => $active->tahun,
                            'available_at_semester' => $active->semester,
                            'available_until_tahun' => $pengaturan->pengaturanDetail->tahun_proposal_tersedia_sampai,
                            'available_until_semester' => $pengaturan->pengaturanDetail->semester_proposal_tersedia_sampai,
                            'is_expired' => false,
                        ]);

                        $lastId = $proposal->id;

                        foreach ($request->topik_penelitian as $row) {
                            $kode = ResearchList::where('uuid', $row)->firstOrFail();
                            TopikPenelitianProposal::create([
                                'proposal_skripsi_id' => $lastId,
                                'research_list_id' => $kode->id,
                            ]);
                        }

                        $file->storeAs('uploads/proposal', $fileNameRandom);
                    }
                } else {
                    $escJudul = esc($request->judul_proposal);
                    $countWordJudul = str_word_count($escJudul);

                    if ($countWordJudul < 4) {
                        throw new Exception('Jumlah kata kurang');
                    } else {
                        $file = $request->file('file');
                        $clientName = $file->getClientOriginalName();
                        $mimeType = $file->getClientMimeType();
                        $fileNameRandom = date('YmdHis') . '_' . $file->hashName();

                        $pengaturan = Pengaturan::with('pengaturanDetail')
                            ->where('tahun_ajaran_id', $active->id)
                            ->where('program_studi_id', $user->program_studi_id)
                            ->firstOrFail();

                        $proposal = ProposalSkripsi::create([
                            'proposal_skripsi_form_id' => $form->id,
                            'mahasiswa_id' => $user->id,
                            'judul_proposal' => $request->judul_proposal,
                            'file_proposal' => $clientName,
                            'file_proposal_random' => $fileNameRandom,
                            'file_proposal_mime' => $mimeType,
                            'status' => 1,
                            'penilai1' => $pembimbingPertama,
                            'penilai2' => $pembimbingKedua,
                            'available_at_tahun' => $active->tahun,
                            'available_at_semester' => $active->semester,
                            'available_until_tahun' => $pengaturan->pengaturanDetail->tahun_proposal_tersedia_sampai,
                            'available_until_semester' => $pengaturan->pengaturanDetail->semester_proposal_tersedia_sampai,
                            'is_expired' => false,
                        ]);
                        $lastId = $proposal->id;

                        foreach ($request->topik_penelitian as $row) {
                            $kode = ResearchList::where('uuid', $row)->firstOrFail();
                            TopikPenelitianProposal::create([
                                'proposal_skripsi_id' => $lastId,
                                'research_list_id' => $kode->id,
                            ]);
                        }

                        $file->storeAs('uploads/proposal', $fileNameRandom);
                    }
                }
            });
            return redirect()->route('proposal.skripsi.pengumpulan')->with('success', 'Berhasil mengupload file');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengupload file');
        }
    }

    public function destroy(Request $request)
    {
        $uuid = $request->input('slug');
        try {
            DB::transaction(function () use ($uuid) {
                $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
                $kode = TopikPenelitianProposal::where('proposal_skripsi_id', $data->id)->get();
                $path = 'uploads/proposal/' . $data->file_proposal_random;

                if (Storage::exists($path)) {
                    Storage::delete($path);
                    foreach ($kode as $row) {
                        $row->delete();
                    }
                    $data->delete();
                }
            });
            return redirect()->route('proposal.skripsi.pengumpulan')->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus file');
        }
    }

    public function hasil()
    {
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Mahasiswa::where('user_id', Auth::user()->id)->firstOrFail();
        $now = date('Y-m-d H:i:s');
        $data = ProposalSkripsiForm::where('tahun_ajaran_id', $active->id)
            ->where('program_studi_id', $user->program_studi_id)
            ->where('dibuka', '<', $now)
            ->orderBy('created_at', 'desc')
            ->get();
        $result = ProposalSkripsi::with('proposalSkripsiForm')
            ->with('penilaiPertama', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKedua', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKetiga', function ($query) {
                $query->select('id', 'nama');
            })
            ->where('mahasiswa_id', $user->id)
            ->where(function ($query) {
                $query->where('penilai1', '!=', null)
                    ->where('penilai2', '!=', null)
                    ->where('penilai3', '!=', null);
            })
            ->get();
        if (count($result) <= 0) {
            $result = [];
        }
        return view('pages.mahasiswa.hasil-proposal.hasil-proposal', [
            'title' => 'Hasil Proposal Skripsi',
            'subtitle' => '',
            'data' => $data,
            'result' => $result,
        ]);
    }

    public function downloadFilePeriksaPenilai1($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/periksa-proposal/' . $data->file_random_penilai1;
        $fileName = $data->file_penilai1;
        $mime = $data->file_penilai1_mime;

        if (Storage::exists($path)) {
            if ($mime == 'application/pdf') {
                return Storage::download($path, $fileName);
            }
        } else {
            abort(404);
        }
    }

    public function downloadFilePeriksaPenilai2($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();

        $path = 'uploads/periksa-proposal/' . $data->file_random_penilai2;
        $fileName = $data->file_penilai2;
        $mime = $data->file_penilai2_mime;

        if (Storage::exists($path)) {
            if ($mime == 'application/pdf') {
                return Storage::download($path, $fileName);
            }
        } else {
            abort(404);
        }
    }

    public function downloadFilePeriksaPenilai3($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();

        $path = 'uploads/periksa-proposal/' . $data->file_random_penilai3;
        $fileName = $data->file_penilai3;
        $mime = $data->file_penilai3_mime;

        if (Storage::exists($path)) {
            if ($mime == 'application/pdf') {
                return Storage::download($path, $fileName);
            }
        } else {
            abort(404);
        }
    }

    public function getFile($uuid)
    {
        $data = ProposalSkripsi::where('uuid', $uuid)->firstOrFail();
        $path = 'uploads/proposal/' . $data->file_proposal_random;
        if (Storage::exists($path)) {
            return Storage::download($path, $data->file_proposal);
            // $fileContents = Storage::get($path);
            // return response($fileContents, 200)
            //     ->header('Content-Type', $data->file_proposal_mime);
        } else {
            abort(404);
        }
    }
}
