@extends('layouts.main')

@section('content')
@php
$params = Request::segment(3);
$now = date('Y-m-d H:i:s');
@endphp
<div class="overlay" id="overlay">
    <i class="fas fa-sync fa-spin"></i>
</div>
@if (auth()->user()->role === 'dosen')
@include('partials.dosen-nav')
@elseif (auth()->user()->role === 'kaprodi' || auth()->user()->role === 'sekprodi')
@include('partials.prodi-nav')
@endif

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Approve Laporan Skripsi</h1>
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

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Card -->
                    <div class="card card-outline card-info scroll">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Approve Laporan Skripsi
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('laporan.skripsi.approve') }}" id="btn-download"
                                        class="btn btn-sm btn-dark">
                                        <i class="fas fa-chevron-left mr-1"></i>
                                        Back
                                    </a>
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.80em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 3%;">#</th>
                                        <th style="width: 5%;">Nim</th>
                                        <th style="width: 12%;">Nama Mahasiswa</th>
                                        <th style="width: 15%;">File Laporan</th>
                                        <th style="width: 15%;">File Hasil Koreksi</th>
                                        <th style="width: 10%;">Status Dari Saya</th>
                                        <th style="width: 10%;">Status Lainnya</th>
                                        <th style="width: 15%;">File Pembimbing Lain</th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $row)
                                    @if ($row->file_laporan !== null)

                                    <!-- Pembimbing 2 Ada -->
                                    @if ($row->pembimbing2 !== null)

                                    @if ($row->status_approval_pembimbing2 === 1)
                                    <tr>
                                        <td>
                                            @if ($row->status_approval_pembimbing2 === 1)
                                            <span class="right badge badge-danger">New</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->mahasiswa->nim }}</td>
                                        <td>{{ $row->mahasiswa->nama }}</td>
                                        <td>
                                            @if ($row->file_pembimbing2 !== null)
                                            <a href="{{ route('laporan.skripsi.approve.file-pembimbing1', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->file_kaprodi === null)
                                            @if ($row->status_approval_pembimbing2 === 1)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->uuid }}" data-toggle="modal"
                                                data-target="#uploadModalProdi2">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @endif

                                            @else
                                            <a href="{{ route('laporan.skripsi.approve.file-pembimbing2', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_approval_kaprodi === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_approval_kaprodi === 4)
                                            <span class="text-danger font-weight-bold">Ditolak ke Pembimbing 2</span>

                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_akhir === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @endif
                                        </td>
                                        <td>

                                        </td>
                                        <td>
                                            <div class="row">
                                                <button type="button" class="btn btn-sm btn-primary dt-control"
                                                    data-status="Kaprodi"
                                                    data-tanggal="{{ $row->tanggal_approval_revisi_penilai1 }}"
                                                    data-nim="{{ $row->mahasiswa->nim }}"
                                                    data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                    data-judul-laporan="{{ $row->judul_laporan }}"
                                                    data-pembimbing1="{{ $row->pembimbingPertama->nama }}"
                                                    data-pembimbing2="{{ $row->pembimbingKedua->nama }}"
                                                    data-status-pembimbing1="{{ $row->status_approval_pembimbing1 }}"
                                                    data-status-pembimbing2="{{ $row->status_approval_pembimbing2 }}"
                                                    data-status-prodi="{{ $row->status_approval_kaprodi }}"
                                                    data-note-pembimbing1="{{ $row->note_pembimbing1 }}"
                                                    data-note-pembimbing2="{{ $row->note_pembimbing2 }}"
                                                    data-note-prodi="{{ $row->note_kaprodi }}">
                                                    <i class=" fas fa-folder"></i>
                                                </button>
                                                @if ($row->file_kaprodi !== null)
                                                @if ($form->ditutup > $now)

                                                @if ($row->status_approval_kaprodi === 1)
                                                <form action="{{ route('laporan.skripsi.approve.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="pembimbing" value="1">
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="btn btn-sm btn-danger mx-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    <!-- End -->

                                    <!-- Pembimbing 2 Tidak Ada -->
                                    @elseif ($row->pembimbing2 === null)
                                    @if ($row->status_approval_pembimbing1 === 1)
                                    <tr>
                                        <td>
                                            @if ($row->status_approval_kaprodi === null)
                                            <span class="right badge badge-danger">New</span>
                                            @elseif ($row->status_approval_kaprodi === 2)
                                            <span class="right badge badge-danger">New</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->mahasiswa->nim }}</td>
                                        <td>{{ $row->mahasiswa->nama }}</td>
                                        <td>
                                            @if ($row->file_pembimbing1 !== null)
                                            <a href="{{ route('laporan.skripsi.approve.file-pembimbing1', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->file_kaprodi === null)
                                            @if ($row->status_approval_pembimbing1 === 1 &&
                                            $row->status_approval_kaprodi !== 3)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->uuid }}" data-toggle="modal"
                                                data-target="#uploadModalProdi1">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @endif

                                            @else
                                            <a href="{{ route('laporan.skripsi.approve.file-pembimbing2', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_approval_kaprodi === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_approval_kaprodi === 4)
                                            <span class="text-danger font-weight-bold">Ditolak ke Pembimbing 2</span>

                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_akhir === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @endif
                                        </td>
                                        <td>

                                        </td>
                                        <td>
                                            <div class="row">
                                                <button type="button" class="btn btn-sm btn-primary dt-control"
                                                    data-status="Kaprodi"
                                                    data-tanggal="{{ $row->tanggal_approval_revisi_penilai1 }}"
                                                    data-nim="{{ $row->mahasiswa->nim }}"
                                                    data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                    data-judul-laporan="{{ $row->judul_laporan }}"
                                                    data-pembimbing1="{{ $row->pembimbingPertama->nama }}"
                                                    data-pembimbing2=""
                                                    data-status-pembimbing1="{{ $row->status_approval_pembimbing1 }}"
                                                    data-status-pembimbing2="{{ $row->status_approval_pembimbing2 }}"
                                                    data-status-prodi="{{ $row->status_approval_kaprodi }}"
                                                    data-note-pembimbing1="{{ $row->note_pembimbing1 }}"
                                                    data-note-pembimbing2="{{ $row->note_pembimbing2 }}"
                                                    data-note-prodi="{{ $row->note_kaprodi }}">
                                                    <i class=" fas fa-folder"></i>
                                                </button>
                                                @if ($row->file_kaprodi !== null)
                                                @if ($form->ditutup > $now)

                                                @if ($row->status_approval_kaprodi === 1)
                                                <form action="{{ route('laporan.skripsi.approve.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="pembimbing" value="1">
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="btn btn-sm btn-danger mx-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    @endif

                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End of Card -->

                </div>
            </div>
        </div>
        <!-- End of Container -->
    </section>
</div>

<!-- Modal Prodi Pembimbing 1 -->
<div class="modal fade" id="uploadModalProdi1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload File Hasil Approve Laporan Pembimbing 1</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('laporan.skripsi.approve.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pembimbing" value="3">
                    <input type="hidden" name="laporan_id">

                    <div class="form-group">
                        <label for="status">Status Approval Laporan</label>
                        <div class="icheck-primary" style="margin-right: 1%;">
                            <input type="radio" id="penilaiPertamaPrimary1" name="status" value="1" required />
                            <label for="penilaiPertamaPrimary1">Diterima</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="penilaiPertamaDanger1" name="status" value="0" />
                            <label for="penilaiPertamaDanger1">Ditolak ke Mahasiswa</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="penilaiPertamaDanger2" name="status" value="3" />
                            <label for="penilaiPertamaDanger2">Ditolak ke Pembimbing 1</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="note">Catatan Revisi (Jika Status Ditolak)</label>
                        <div class="input-group">
                            <textarea class="form-control" name="note" placeholder="Catatan Revisi" rows="4"></textarea>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Koreksi -->
                    <div class="form-group">
                        <label for="exampleInputFile">
                            Silahkan upload file laporan yang sudah ditanda tangan atau file revisi jika ada
                            yang perlu
                            direvisi.
                        </label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile" name="file_upload"
                                    accept="application/pdf" required>
                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                            </div>
                        </div>
                    </div>
                    <!-- End -->

                    <!-- Submit Button -->
                    <div class="d-flex flex-row">
                        <div class="mr-2">
                            <input type="submit" class="btn btn-primary" name="action" value="Save" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- Modal Prodi Pembimbing 2 -->
<div class="modal fade" id="uploadModalProdi2">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload File Hasil Approve Laporan Pembimbing 1</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('laporan.skripsi.approve.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pembimbing" value="3">
                    <input type="hidden" name="laporan_id">

                    <div class="form-group">
                        <label for="status">Status Approval Laporan</label>
                        <div class="icheck-primary" style="margin-right: 1%;">
                            <input type="radio" id="penilaiKeduaPrimary1" name="status" value="1" required />
                            <label for="penilaiKeduaPrimary1">Diterima</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="penilaiKeduaDanger1" name="status" value="0" />
                            <label for="penilaiKeduaDanger1">Ditolak ke Mahasiswa</label>
                        </div>
                    </div>
                    <div class="icheck-danger">
                        <input type="radio" id="penilaiKeduaDanger2" name="status" value="4" />
                        <label for="penilaiKeduaDanger1">Ditolak ke Pembimbing 2</label>
                    </div>
            </div>

            <div class="form-group">
                <label for="note">Catatan Revisi (Jika Status Ditolak)</label>
                <div class="input-group">
                    <textarea class="form-control" name="note" placeholder="Catatan Revisi" rows="4"></textarea>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-pen"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File Koreksi -->
            <div class="form-group">
                <label for="exampleInputFile">
                    Silahkan upload file laporan yang sudah ditanda tangan atau file revisi jika ada
                    yang perlu
                    direvisi.
                </label>
                <div class="input-group">
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="exampleInputFile" name="file_upload"
                            accept="application/pdf" required>
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                    </div>
                </div>
            </div>
            <!-- End -->

            <!-- Submit Button -->
            <div class="d-flex flex-row">
                <div class="mr-2">
                    <input type="submit" class="btn btn-primary" name="action" value="Save" />
                </div>
            </div>
            </form>
        </div>
    </div>
    <!-- /.modal-content -->
</div>
<!-- /.modal-dialog -->
</div>

<!-- /.modal -->
@endsection
@section('script')
<script>
    $(function() {
        bsCustomFileInput.init();
    });

    $(document).ready(function() {
        const table = $('#table-data').DataTable({
            pageLength: 100,
            order: [
                [0, 'desc']
            ]
        });

        function format(details) {
            console.log(details);
            if (details.pembimbing2 !== "") {
                return '<div>' +
                    '<table class="table">' +
                    '<tr>' +
                    '<th style="width: 30%;">Status</th>' +
                    '<td>' + details.status + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">NIM</th>' +
                    '<td>' + details.nim + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Nama</th>' +
                    '<td>' + details.nama + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Judul Laporan</th>' +
                    '<td>' + details.judul + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Pembimbing 1</th>' +
                    '<td>' + details.pembimbing1 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Status Approval Pembimbing 1</th>' +
                    '<td>' + details.textStatusPembimbing1 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Note Pembimbing 1</th>' +
                    '<td>' + details.notePembimbing1 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Pembimbing 2</th>' +
                    '<td>' + details.pembimbing2 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Status Approval Pembimbing 2</th>' +
                    '<td>' + details.textStatusPembimbing2 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Note Pembimbing 2</th>' +
                    '<td>' + details.notePembimbing2 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Status Approval Kaprodi</th>' +
                    '<td>' + details.textStatusProdi + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Note Kaprodi</th>' +
                    '<td>' + details.noteProdi + '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>'
            } else {
                return '<div>' +
                    '<table class="table">' +
                    '<tr>' +
                    '<th style="width: 30%;">Status</th>' +
                    '<td>' + details.status + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">NIM</th>' +
                    '<td>' + details.nim + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Nama</th>' +
                    '<td>' + details.nama + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Judul Laporan</th>' +
                    '<td>' + details.judul + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Pembimbing 1</th>' +
                    '<td>' + details.pembimbing1 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Status Approval Pembimbing 1</th>' +
                    '<td>' + details.textStatusPembimbing1 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Note Pembimbing 1</th>' +
                    '<td>' + details.notePembimbing1 + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Status Approval Kaprodi</th>' +
                    '<td>' + details.textStatusProdi + '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<th style="width: 30%;">Note Kaprodi</th>' +
                    '<td>' + details.noteProdi + '</td>' +
                    '</tr>' +
                    '</table>' +
                    '</div>'
            }
        }

        $(document).on('click', '.btn-open-modal', function() {
            let id = $(this).val();
            $('input[name="laporan_id"]').val(id);
        });

        $('#uploadModalProdi1').on('hidden.bs.modal', function() {
            $(this).find('input[name="laporan_id"]').val('');
        });
        $('#uploadModalProdi2').on('hidden.bs.modal', function() {
            $(this).find('input[name="laporan_id"]').val('');
        });

        $('#table-data').on('click', 'td button.dt-control', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);
            let status = $(this).data('status');
            let tanggal = $(this).data('tanggal');
            let nim = $(this).data('nim');
            let nama = $(this).data('mahasiswa');
            let judul = $(this).data('judul-laporan');
            let pembimbing1 = $(this).data('pembimbing1');
            let pembimbing2 = $(this).data('pembimbing2');
            let statusPembimbing1 = $(this).data('status-pembimbing1');
            let statusPembimbing2 = $(this).data('status-pembimbing2');
            let statusProdi = $(this).data('status-prodi');
            let notePembimbing1 = $(this).data('note-pembimbing1');
            let notePembimbing2 = $(this).data('note-pembimbing2');
            let noteProdi = $(this).data('note-prodi');

            let textStatusPembimbing1 = null;
            let textStatusPembimbing2 = null;
            let textStatusProdi = null;
            if (statusPembimbing1 == 0) {
                textStatusPembimbing1 =
                    '<span class="text-danger font-weight-bold">Ditolak ke Mahasiswa</span>';
            } else if (statusPembimbing1 == 1) {
                textStatusPembimbing1 = '<span class="text-primary font-weight-bold">Diterima</span>';
            } else {
                textStatusPembimbing1 = '<span class="text-secondary font-weight-bold">Pending</span>';
            }

            if (pembimbing2 !== "") {
                if (statusPembimbing2 == 0) {
                    textStatusPembimbing2 =
                        '<span class="text-danger font-weight-bold">Ditolak ke Mahasiswa</span>';
                } else if (statusPembimbing2 == 1) {
                    textStatusPembimbing2 = '<span class="text-primary font-weight-bold">Diterima</span>';
                } else if (statusPembimbing2 == 3) {
                    textStatusPembimbing2 =
                        '<span class="text-danger font-weight-bold">Ditolak ke Pembimbing 1</span>';
                } else {
                    textStatusPembimbing2 = '<span class="text-secondary font-weight-bold">Pending</span>';
                }
            }

            if (statusProdi == 0) {
                textStatusProdi =
                    '<span class="text-danger font-weight-bold">Ditolak ke Mahasiswa</span>';
            } else if (statusProdi == 1) {
                textStatusProdi = '<span class="text-primary font-weight-bold">Diterima</span>';
            } else if (statusProdi == 3) {
                textStatusProdi =
                    '<span class="text-danger font-weight-bold">Ditolak ke Pembimbing 1</span>';
            } else if (statusProdi == 4) {
                textStatusProdi =
                    '<span class="text-danger font-weight-bold">Ditolak ke Pembimbing 2</span>';
            } else {
                textStatusProdi = '<span class="text-secondary font-weight-bold">Pending</span>';
            }

            let details = {};
            details.status = status;
            details.tanggal = tanggal;
            details.nim = nim;
            details.nama = nama;
            details.judul = judul;
            details.pembimbing1 = pembimbing1;
            details.pembimbing2 = pembimbing2;
            details.textStatusPembimbing1 = textStatusPembimbing1;
            details.textStatusPembimbing2 = textStatusPembimbing2;
            details.textStatusProdi = textStatusProdi;
            details.notePembimbing1 = notePembimbing1;
            details.notePembimbing2 = notePembimbing2;
            details.noteProdi = noteProdi;

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(details)).show();
                tr.addClass('shown');
            }
        });

        $(document).on('change', 'input[name="status"]', function(e) {
            var value = $(this).attr('value');
            if (value == 0 || value == 3 || value == 4) {
                $('input[name="file_upload"]').removeAttr('required');
                $('textarea[name="note"]').attr('required', 'required');
            } else if (value == 1) {
                $('input[name="file_upload"]').attr('required', 'required');
                $('textarea[name="note"]').removeAttr('required');
            }
        });
    });
</script>










@endsection