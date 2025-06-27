@extends('layouts.main')

@section('content')
@php
$now = date('Y-m-d H:i:s');
@endphp
@include('partials.mahasiswa-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Hasil Skripsi</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Container -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">

                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-check"></i>Success!</h5>
                        {{ session('success') }}
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h5><i class="icon fas fa-ban"></i>Failed!</h5>
                        {{ session('error') }}
                    </div>
                    @endif

                    <!-- Card -->
                    @foreach ($data as $d)
                    <div class="card card-outline card-info scroll">

                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    {{ $d->judul_form }}
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        @if (!$result)

                        @else
                        @foreach ($result as $row)
                        @if ($row->proposalSkripsiForm->id == $d->id)
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th style="width: 25%;">Judul Proposal (Indonesia)</th>
                                        <td>
                                            {{ $row->judul_proposal }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 25%;">Judul Proposal (English)</th>
                                        <td>
                                            {{ $row->judul_proposal_en ?? 'Not provided' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 25%;">Status Skripsi</th>
                                        <td>
                                            @if ($row->signed_proposal)
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-check-circle mr-1"></i>Lulus (3/3 Approval)
                                                </span>
                                            @elseif ($row->status_approval_penilai1 === 0 || $row->status_approval_penilai2 === 0 || $row->status_approval_penilai3 === 0)
                                                <span class="badge badge-danger p-2">
                                                    <i class="fas fa-redo mr-1"></i>Perlu Evaluasi Ulang
                                                </span>
                                                <br><small class="text-muted mt-1">
                                                    Ada penilai yang menolak. Semua evaluasi direset - proses dimulai dari awal.
                                                    @if ($row->rejection_comment_penilai1 || $row->rejection_comment_penilai2 || $row->rejection_comment_penilai3)
                                                    Lihat komentar penolakan di bawah.
                                                    @endif
                                                </small>
                                            @elseif ($row->status_akhir === 1)
                                                <span class="badge badge-success p-2">
                                                    <i class="fas fa-check-circle mr-1"></i>Lulus (3/3 Approval)
                                                </span>
                                            @elseif (!$row->penilai1 || !$row->penilai2 || !$row->penilai3)
                                                <span class="badge badge-info p-2">
                                                    <i class="fas fa-user-plus mr-1"></i>Menunggu Penugasan Penilai
                                                </span>
                                                <br><small class="text-muted mt-1">
                                                    Diperlukan 3 penilai berbeda: 
                                                    {{ $row->penilai1 ? '✓' : '○' }} Penilai 1, 
                                                    {{ $row->penilai2 ? '✓' : '○' }} Penilai 2, 
                                                    {{ $row->penilai3 ? '✓' : '○' }} Penilai 3
                                                </small>
                                            @else
                                                <span class="badge badge-warning p-2">
                                                    <i class="fas fa-hourglass-half mr-1"></i>Dalam Evaluasi
                                                </span>
                                                <br><small class="text-muted mt-1">
                                                    Status: 
                                                    {{ $row->status_approval_penilai1 === 1 ? '✓' : ($row->status_approval_penilai1 === 0 ? '✗' : '○') }} Penilai 1,
                                                    {{ $row->status_approval_penilai2 === 1 ? '✓' : ($row->status_approval_penilai2 === 0 ? '✗' : '○') }} Penilai 2,
                                                    {{ $row->status_approval_penilai3 === 1 ? '✓' : ($row->status_approval_penilai3 === 0 ? '✗' : '○') }} Penilai 3
                                                </small>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <!-- Always show the 3-evaluator status table -->
                                <div class="alert alert-light border">
                                    <h6><i class="fas fa-info-circle mr-1"></i>Status 3 Persetujuan Penilai:</h6>
                                    <p class="mb-1 text-muted">Proposal Anda memerlukan persetujuan dari ketiga penilai untuk dinyatakan lulus.</p>
                                    <div class="alert alert-warning mt-2 p-2">
                                        <small><strong><i class="fas fa-exclamation-triangle mr-1"></i>Perhatian:</strong> 
                                        Jika ada satu penilai yang menolak, semua persetujuan akan direset dan proses evaluasi dimulai dari awal.</small>
                                    </div>
                                    
                                    @php
                                        $assignedCount = 0;
                                        $approvedCount = 0;
                                        $hasRejection = false;
                                        $rejectedCount = 0;
                                        
                                        if ($row->penilai1) $assignedCount++;
                                        if ($row->penilai2) $assignedCount++;
                                        if ($row->penilai3) $assignedCount++;
                                        
                                        // Check for rejections first
                                        if ($row->status_approval_penilai1 === 0) { $hasRejection = true; $rejectedCount++; }
                                        if ($row->status_approval_penilai2 === 0) { $hasRejection = true; $rejectedCount++; }
                                        if ($row->status_approval_penilai3 === 0) { $hasRejection = true; $rejectedCount++; }
                                        
                                        // Count approvals only if no rejections (due to reset mechanism)
                                        if (!$hasRejection) {
                                            if ($row->status_approval_penilai1 === 1) $approvedCount++;
                                            if ($row->status_approval_penilai2 === 1) $approvedCount++;
                                            if ($row->status_approval_penilai3 === 1) $approvedCount++;
                                        }
                                    @endphp
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Penilai Ditugaskan:</small>
                                            <div class="progress mb-2" style="height: 20px;">
                                                <div class="progress-bar bg-info" role="progressbar" 
                                                     style="width: {{ ($assignedCount/3)*100 }}%">
                                                    {{ $assignedCount }}/3
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Persetujuan Diterima:</small>
                                            <div class="progress mb-2" style="height: 20px;">
                                                @if ($hasRejection)
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: 100%">
                                                    DIRESET - {{ $rejectedCount }} Penolakan
                                                </div>
                                                @else
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ ($approvedCount/3)*100 }}%">
                                                    {{ $approvedCount }}/3
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table class="table-data table table-bordered table-striped"
                                    style="font-size: 0.9em; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Penilai</th>
                                            <th>Status Penilai</th>
                                            <th>Status Approval</th>
                                            <th>File Hasil Periksa</th>
                                            <th>Komentar/Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                @if ($row->penilaiPertama)
                                                {{ $row->penilaiPertama->nama }}
                                                @else
                                                <span class="text-muted"><i>Belum ditentukan</i></span>
                                                @endif
                                            </td>
                                            <td>Penilai 1</td>
                                            <td>
                                                @if (!$row->penilai1)
                                                <span class="text-muted font-weight-bold">
                                                    <i class="fas fa-user-plus mr-1"></i>Menunggu Penugasan
                                                </span>
                                                @elseif ($row->status_approval_penilai1 === 0)
                                                <span class="text-danger font-weight-bold">
                                                    <i class="fas fa-times mr-1"></i>Ditolak - Reset Semua
                                                </span>
                                                @elseif ($row->status_approval_penilai2 === 0 || $row->status_approval_penilai3 === 0)
                                                <span class="text-secondary font-weight-bold">
                                                    <i class="fas fa-redo mr-1"></i>Direset (Penilai lain menolak)
                                                </span>
                                                @elseif ($row->status_approval_penilai1 === 1)
                                                <span class="text-success font-weight-bold">
                                                    <i class="fas fa-check mr-1"></i>Disetujui
                                                </span>
                                                @else
                                                <span class="text-warning font-weight-bold">
                                                    <i class="fas fa-clock mr-1"></i>Menunggu Evaluasi
                                                </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row->file_penilai1 !== null)
                                                <a href="{{ route('proposal.skripsi.hasil.download-file-periksa-penilai1', $row->uuid) }}"
                                                    class="btn btn-info btn-sm" target="_blank">
                                                    <i class="fas fa-download mr-1"></i>
                                                    Download
                                                </a>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row->status_approval_penilai1 === 0 && $row->rejection_comment_penilai1)
                                                <div class="alert alert-danger p-2 mb-0">
                                                    <strong><i class="fas fa-comment-alt mr-1"></i>Komentar Penolakan:</strong><br>
                                                    {{ $row->rejection_comment_penilai1 }}
                                                </div>
                                                @elseif ($row->status_approval_penilai1 === 1)
                                                <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                @if ($row->penilaiKedua)
                                                {{ $row->penilaiKedua->nama }}
                                                @else
                                                <span class="text-muted"><i>Belum ditentukan</i></span>
                                                @endif
                                            </td>
                                            <td>Penilai 2</td>
                                            <td>
                                                @if (!$row->penilai2)
                                                <span class="text-muted font-weight-bold">
                                                    <i class="fas fa-user-plus mr-1"></i>Menunggu Penugasan
                                                </span>
                                                @elseif ($row->status_approval_penilai2 === 0)
                                                <span class="text-danger font-weight-bold">
                                                    <i class="fas fa-times mr-1"></i>Ditolak - Reset Semua
                                                </span>
                                                @elseif ($row->status_approval_penilai1 === 0 || $row->status_approval_penilai3 === 0)
                                                <span class="text-secondary font-weight-bold">
                                                    <i class="fas fa-redo mr-1"></i>Direset (Penilai lain menolak)
                                                </span>
                                                @elseif ($row->status_approval_penilai2 === 1)
                                                <span class="text-success font-weight-bold">
                                                    <i class="fas fa-check mr-1"></i>Disetujui
                                                </span>
                                                @else
                                                <span class="text-warning font-weight-bold">
                                                    <i class="fas fa-clock mr-1"></i>Menunggu Evaluasi
                                                </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row->file_penilai2 !== null)
                                                <a href="{{ route('proposal.skripsi.hasil.download-file-periksa-penilai2', $row->uuid) }}"
                                                    class="btn btn-info btn-sm" target="_blank">
                                                    <i class="fas fa-download mr-1"></i>
                                                    Download
                                                </a>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row->status_approval_penilai2 === 0 && $row->rejection_comment_penilai2)
                                                <div class="alert alert-danger p-2 mb-0">
                                                    <strong><i class="fas fa-comment-alt mr-1"></i>Komentar Penolakan:</strong><br>
                                                    {{ $row->rejection_comment_penilai2 }}
                                                </div>
                                                @elseif ($row->status_approval_penilai2 === 1)
                                                <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                @if ($row->penilaiKetiga)
                                                {{ $row->penilaiKetiga->nama }}
                                                @else
                                                <span class="text-muted"><i>Belum ditentukan</i></span>
                                                @endif
                                            </td>
                                            <td>Penilai 3</td>
                                            <td>
                                                @if (!$row->penilai3)
                                                <span class="text-muted font-weight-bold">
                                                    <i class="fas fa-user-plus mr-1"></i>Menunggu Penugasan
                                                </span>
                                                @elseif ($row->status_approval_penilai3 === 0)
                                                <span class="text-danger font-weight-bold">
                                                    <i class="fas fa-times mr-1"></i>Ditolak - Reset Semua
                                                </span>
                                                @elseif ($row->status_approval_penilai1 === 0 || $row->status_approval_penilai2 === 0)
                                                <span class="text-secondary font-weight-bold">
                                                    <i class="fas fa-redo mr-1"></i>Direset (Penilai lain menolak)
                                                </span>
                                                @elseif ($row->status_approval_penilai3 === 1)
                                                <span class="text-success font-weight-bold">
                                                    <i class="fas fa-check mr-1"></i>Disetujui
                                                </span>
                                                @else
                                                <span class="text-warning font-weight-bold">
                                                    <i class="fas fa-clock mr-1"></i>Menunggu Evaluasi
                                                </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row->file_penilai3 !== null)
                                                <a href="{{ route('proposal.skripsi.hasil.download-file-periksa-penilai3', $row->uuid) }}"
                                                    class="btn btn-info btn-sm" target="_blank">
                                                    <i class="fas fa-download mr-1"></i>
                                                    Download
                                                </a>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($row->status_approval_penilai3 === 0 && $row->rejection_comment_penilai3)
                                                <div class="alert alert-danger p-2 mb-0">
                                                    <strong><i class="fas fa-comment-alt mr-1"></i>Komentar Penolakan:</strong><br>
                                                    {{ $row->rejection_comment_penilai3 }}
                                                </div>
                                                @elseif ($row->status_approval_penilai3 === 1)
                                                <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>
                                                @else
                                                <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        @endforeach
                        @endif

                    </div>
                    @endforeach
                    <!-- End of Card -->

                </div>
            </div>
        </div>
        <!-- End of Container -->
    </section>
</div>
@endsection
