@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">{{ $title }}</h1>
                    <p class="text-muted">{{ $subtitle }}</p>
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

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $data->count() }}</h3>
                                    <p>Total Skripsi</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $data->whereNotNull('signed_proposal')->count() }}</h3>
                                    <p>Sudah Ditandatangani</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-signature"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $data->where('status_akhir', 1)->whereNull('signed_proposal')->count() }}</h3>
                                    <p>Disetujui - Belum Ditandatangani</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $data->where('status_akhir', 0)->count() }}</h3>
                                    <p>Ditolak</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Card -->
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    <i class="fas fa-list mr-2"></i>Daftar Status Skripsi & Tanda Tangan Digital
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="thesis-status-table" class="table table-bordered table-striped" style="font-size: 0.85em; width: 100%;">
                                    <thead>
                                    <tr>
                                        <th style="width: 3%;">#</th>
                                        <th style="width: 8%;">NIM</th>
                                        <th style="width: 12%;">Mahasiswa</th>
                                        <th style="width: 20%;">Judul Skripsi</th>
                                        <th style="width: 10%;">Status Evaluasi</th>
                                        <th style="width: 12%;">Evaluator Status</th>
                                        <th style="width: 10%;">Status Tanda Tangan</th>
                                        <th style="width: 8%;">Hash</th>
                                        <th style="width: 8%;">Tanggal Submit</th>
                                        <th style="width: 9%;">Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data as $d)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $d->mahasiswa->nim }}</td>
                                            <td>{{ $d->mahasiswa->nama }}</td>
                                            <td>
                                                <div class="text-wrap" style="max-width: 250px;">
                                                    <strong>ID:</strong> {{ Str::limit($d->judul_proposal, 50) }}<br>
                                                    @if($d->judul_proposal_en)
                                                        <strong>EN:</strong> {{ Str::limit($d->judul_proposal_en, 50) }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if ($d->signed_proposal)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle mr-1"></i>Selesai & Ditandatangani
                                                    </span>
                                                @elseif ($d->status_akhir === 1)
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-hourglass-half mr-1"></i>Disetujui - Menunggu TT
                                                    </span>
                                                @elseif ($d->status_akhir === 0 || $d->status_approval_penilai1 === 0 || $d->status_approval_penilai2 === 0 || $d->status_approval_penilai3 === 0)
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times-circle mr-1"></i>Ditolak
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-clock mr-1"></i>Dalam Evaluasi
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center">
                                                    <div class="mr-2 text-center">
                                                        @if ($d->status_approval_penilai1 === 1)
                                                            <i class="fas fa-check text-success" title="Penilai 1: Disetujui"></i>
                                                        @elseif ($d->status_approval_penilai1 === 0)
                                                            <i class="fas fa-times text-danger" title="Penilai 1: Ditolak"></i>
                                                        @else
                                                            <i class="fas fa-circle text-muted" title="Penilai 1: Pending"></i>
                                                        @endif
                                                        <br><span class="text-muted" style="font-size: 0.7em;">P1</span>
                                                    </div>
                                                    <div class="mr-2 text-center">
                                                        @if ($d->status_approval_penilai2 === 1)
                                                            <i class="fas fa-check text-success" title="Penilai 2: Disetujui"></i>
                                                        @elseif ($d->status_approval_penilai2 === 0)
                                                            <i class="fas fa-times text-danger" title="Penilai 2: Ditolak"></i>
                                                        @else
                                                            <i class="fas fa-circle text-muted" title="Penilai 2: Pending"></i>
                                                        @endif
                                                        <br><span class="text-muted" style="font-size: 0.7em;">P2</span>
                                                    </div>
                                                    <div class="text-center">
                                                        @if ($d->status_approval_penilai3 === 1)
                                                            <i class="fas fa-check text-success" title="Penilai 3: Disetujui"></i>
                                                        @elseif ($d->status_approval_penilai3 === 0)
                                                            <i class="fas fa-times text-danger" title="Penilai 3: Ditolak"></i>
                                                        @else
                                                            <i class="fas fa-circle text-muted" title="Penilai 3: Pending"></i>
                                                        @endif
                                                        <br><span class="text-muted" style="font-size: 0.7em;">P3</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($d->signed_proposal)
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-signature mr-1"></i>Ditandatangani
                                                    </span>
                                                    @if($d->hash_value)
                                                        <br><small class="text-muted">Terverifikasi</small>
                                                    @endif
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-pen mr-1"></i>Belum Ditandatangani
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($d->hash_value)
                                                    <small class="text-success" title="{{ $d->hash_value }}">
                                                        <i class="fas fa-shield-alt mr-1"></i>{{ Str::limit($d->hash_value, 8) }}...
                                                    </small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($d->created_at)->format('d/m/Y') }}
                                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($d->created_at)->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group-vertical btn-group-sm">
                                                    @if($d->file_proposal_random)
                                                        <a href="{{ route('proposal.serve', basename($d->file_proposal_random)) }}" 
                                                           class="btn btn-info btn-xs mb-1" target="_blank">
                                                            <i class="fas fa-eye mr-1"></i>Preview
                                                        </a>
                                                    @endif
                                                    
                                                    @if($d->signed_proposal)
                                                        <a href="{{ route('proposal.signed.download', basename($d->signed_proposal)) }}" 
                                                           class="btn btn-success btn-xs">
                                                            <i class="fas fa-download mr-1"></i>Download
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('script')
<script>
    $(function () {
        $("#thesis-status-table").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            "pageLength": 25,
            "order": [[ 8, "desc" ]], // Order by submit date descending
            "columnDefs": [
                { "orderable": false, "targets": [3, 5, 9] }, // Disable ordering for title, evaluator status, and action columns
                { "searchable": true, "targets": [1, 2, 3] } // Enable search for NIM, name, and title
            ],
            "language": {
                "lengthMenu": "Tampilkan _MENU_ entri per halaman",
                "zeroRecords": "Tidak ada data yang ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada entri yang tersedia",
                "infoFiltered": "(difilter dari _MAX_ total entri)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        }).buttons().container().appendTo('#thesis-status-table_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection 