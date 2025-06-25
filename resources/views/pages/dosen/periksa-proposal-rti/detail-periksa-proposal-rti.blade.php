@extends('layouts.main')

@section('style')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .custom {
            margin-left: 0.3em;
            width: 2.5em;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-content: center;
            text-align: center;
        }

        .overlay .fa-sync {
            font-size: 5rem;
            color: white;
        }
    </style>
@endsection

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
                        <h1 class="font-weight-bold">Periksa Proposal RTI</h1>
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
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">&times;</button>
                                <h5><i class="icon fas fa-check"></i>Success!</h5>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">&times;</button>
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
                                        Periksa Proposal RTI
                                    </h3>
                                    <div class="card-tools">
                                        <a href="{{ route('proposal.rti.periksa') }}" id="btn-download"
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
                                            <th style="width: 15%;">File Proposal RTI</th>
                                            <th style="width: 15%;">Approve Proposal</th>
                                            <th style="width: 10%;">Status Dari Saya</th>
                                            <th style="width: 10%;">Status Lainnya</th>
                                            <th style="width: 10%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($data as $row)
                                            @if ($row->file_proposal !== null)
                                                <!-- Pembimbing 1 -->
                                                @if ($row->pembimbing1 === $dosen->id)
                                                    @if ($row->status_approval_pembimbing1 === 2)
                                                        <tr>
                                                            <td>
                                                                <span class="right badge badge-danger">New</span>
                                                            </td>
                                                            <td>{{ $row->mahasiswa->nim }}</td>
                                                            <td>{{ $row->mahasiswa->nama }}</td>
                                                            <td>
                                                                @if ($row->file_proposal !== null)
                                                                    <a href="{{ route('proposal.rti.file.dosen', $row->uuid) }}"
                                                                        class="btn btn-info btn-sm" target="_blank">
                                                                        <i class="fas fa-download mr-1"></i>
                                                                        Download
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($row->proposalRtiForm->ditutup > $now)
                                                                    <button type="button"
                                                                        class="btn btn-primary btn-sm btn-open-modal"
                                                                        value="{{ $row->uuid }}" data-toggle="modal"
                                                                        data-target="#uploadModalPembimbing1"
                                                                        data-nim="{{ $row->mahasiswa->nim }}"
                                                                        data-nama="{{ $row->mahasiswa->nama }}">
                                                                        <i class="fas fa-upload mr-1"></i>
                                                                        Approve Proposal
                                                                    </button>
                                                                @endif
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                            <td>
                                                                <div class="row">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-primary dt-control"
                                                                        data-nim="{{ $row->mahasiswa->nim }}"
                                                                        data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                                        data-status="Pembimbing 1"
                                                                        data-judul-proposal="{{ $row->judul_proposal }}"
                                                                        data-pembimbing1="{{ $row->pembimbingPertama->nama }}"
                                                                        data-pembimbing2="{{ $row->pembimbingKedua ? $row->pembimbingKedua->nama : '' }}"
                                                                        data-tanggal-pembimbing1="{{ $row->tanggal_approval_pembimbing1 }}"
                                                                        data-tanggal-pembimbing2="{{ $row->tanggal_approval_pembimbing2 }}"
                                                                        data-status-pembimbing1="{{ $row->status_approval_pembimbing1 }}"
                                                                        data-status-pembimbing2="{{ $row->status_approval_pembimbing2 }}"
                                                                        data-status-prodi="{{ $row->status_approval_kaprodi }}"
                                                                        data-note-pembimbing1="{{ $row->note_pembimbing1 }}"
                                                                        data-note-pembimbing2="{{ $row->note_pembimbing2 }}"
                                                                        data-note-prodi="{{ $row->note_kaprodi }}">
                                                                        <i class=" fas fa-folder"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td></td>
                                                            <td>{{ $row->mahasiswa->nim }}</td>
                                                            <td>{{ $row->mahasiswa->nama }}</td>
                                                            <td>
                                                                @if ($row->file_proposal !== null)
                                                                    <a href="{{ route('proposal.rti.file.dosen', $row->uuid) }}"
                                                                        class="btn btn-info btn-sm" target="_blank">
                                                                        <i class="fas fa-download mr-1"></i>
                                                                        Download
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td></td>
                                                            <td>
                                                                @if ($row->status_approval_pembimbing1 === 1)
                                                                    <span
                                                                        class="text-primary font-weight-bold">Diterima</span>
                                                                @elseif ($row->status_approval_pembimbing1 === 0)
                                                                    <span
                                                                        class="text-danger font-weight-bold">Ditolak</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if ($row->status_approval_pembimbing2 === 1)
                                                                    <span
                                                                        class="text-primary font-weight-bold">Diterima</span>
                                                                @elseif ($row->status_akhir === 1)
                                                                    <span
                                                                        class="text-primary font-weight-bold">Diterima</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <div class="row">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-primary dt-control"
                                                                        data-nim="{{ $row->mahasiswa->nim }}"
                                                                        data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                                        data-status="Pembimbing 1"
                                                                        data-tanggal-pembimbing1="{{ $row->tanggal_approval_pembimbing1 }}"
                                                                        data-tanggal-pembimbing2="{{ $row->tanggal_approval_pembimbing2 }}"
                                                                        data-judul-proposal="{{ $row->judul_proposal }}"
                                                                        data-pembimbing1="{{ $row->pembimbingPertama->nama }}"
                                                                        data-pembimbing2="{{ $row->pembimbingKedua ? $row->pembimbingKedua->nama : '' }}"
                                                                        data-status-pembimbing1="{{ $row->status_approval_pembimbing1 }}"
                                                                        data-status-pembimbing2="{{ $row->status_approval_pembimbing2 }}"
                                                                        data-status-prodi="{{ $row->status_approval_kaprodi }}"
                                                                        data-note-pembimbing1="{{ $row->note_pembimbing1 }}"
                                                                        data-note-pembimbing2="{{ $row->note_pembimbing2 }}"
                                                                        data-note-prodi="{{ $row->note_kaprodi }}">
                                                                        <i class=" fas fa-folder"></i>
                                                                    </button>
                                                                    @if ($row->proposalRtiForm->ditutup > $now)
                                                                        @if ($row->pembimbing2 === null)
                                                                            @if ($row->status_akhir === 1)
                                                                                <form
                                                                                    action="{{ route('proposal.rti.periksa.destroy') }}"
                                                                                    method="post">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <input type="hidden" name="slug"
                                                                                        value="{{ $row->uuid }}">
                                                                                    <input type="hidden"
                                                                                        name="pembimbing" value="1">
                                                                                    <button type="submit"
                                                                                        class="btn btn-sm btn-delete btn-danger mx-1">
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
                                                    <!-- End Pembimbing 1 -->

                                                    <!-- Pembimbing 2 -->
                                                @elseif ($row->pembimbing2 === $dosen->id)
                                                    @if ($row->status_approval_pembimbing1 === 1)
                                                        @if ($row->status_approval_pembimbing2 === 2)
                                                            <tr>
                                                                <td>
                                                                    <span class="right badge badge-danger">New</span>
                                                                </td>
                                                                <td>{{ $row->mahasiswa->nim }}</td>
                                                                <td>{{ $row->mahasiswa->nama }}</td>
                                                                <td>
                                                                    @if ($row->file_proposal !== null)
                                                                        <a href="{{ route('proposal.rti.file.dosen', $row->uuid) }}"
                                                                            class="btn btn-info btn-sm" target="_blank">
                                                                            <i class="fas fa-download mr-1"></i>
                                                                            Download
                                                                        </a>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($row->proposalRtiForm->ditutup > $now)
                                                                        <button type="button"
                                                                            class="btn btn-primary btn-sm btn-open-modal"
                                                                            value="{{ $row->uuid }}"
                                                                            data-toggle="modal"
                                                                            data-target="#uploadModalPembimbing2"
                                                                            data-nim="{{ $row->mahasiswa->nim }}"
                                                                            data-nama="{{ $row->mahasiswa->nama }}">
                                                                            <i class="fas fa-upload mr-1"></i>
                                                                            Approve Proposal
                                                                        </button>
                                                                    @endif
                                                                </td>
                                                                <td>

                                                                </td>
                                                                <td>

                                                                </td>
                                                                <td>
                                                                    <div class="row">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-primary dt-control"
                                                                            data-nim="{{ $row->mahasiswa->nim }}"
                                                                            data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                                            data-status="Pembimbing 2"
                                                                            data-tanggal-pembimbing1="{{ $row->tanggal_approval_pembimbing1 }}"
                                                                            data-tanggal-pembimbing2="{{ $row->tanggal_approval_pembimbing2 }}"
                                                                            data-judul-proposal="{{ $row->judul_proposal }}"
                                                                            data-pembimbing1="{{ $row->pembimbingPertama->nama }}"
                                                                            data-pembimbing2="{{ $row->pembimbingKedua ? $row->pembimbingKedua->nama : '' }}"
                                                                            data-status-pembimbing1="{{ $row->status_approval_pembimbing1 }}"
                                                                            data-status-pembimbing2="{{ $row->status_approval_pembimbing2 }}"
                                                                            data-status-prodi="{{ $row->status_approval_kaprodi }}"
                                                                            data-note-pembimbing1="{{ $row->note_pembimbing1 }}"
                                                                            data-note-pembimbing2="{{ $row->note_pembimbing2 }}"
                                                                            data-note-prodi="{{ $row->note_kaprodi }}">
                                                                            <i class=" fas fa-folder"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @else
                                                            <tr>
                                                                <td></td>
                                                                <td>{{ $row->mahasiswa->nim }}</td>
                                                                <td>{{ $row->mahasiswa->nama }}</td>
                                                                <td>
                                                                    @if ($row->file_proposal !== null)
                                                                        <a href="{{ route('proposal.rti.file.dosen', $row->uuid) }}"
                                                                            class="btn btn-info btn-sm" target="_blank">
                                                                            <i class="fas fa-download mr-1"></i>
                                                                            Download
                                                                        </a>
                                                                    @endif
                                                                </td>
                                                                <td></td>
                                                                <td>
                                                                    @if ($row->status_approval_pembimbing2 === 1)
                                                                        <span
                                                                            class="text-primary font-weight-bold">Diterima</span>
                                                                    @elseif ($row->status_approval_pembimbing2 === 0)
                                                                        <span
                                                                            class="text-danger font-weight-bold">Ditolak</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($row->status_akhir === 1)
                                                                        <span
                                                                            class="text-primary font-weight-bold">Diterima</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <div class="row">
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-primary dt-control"
                                                                            data-nim="{{ $row->mahasiswa->nim }}"
                                                                            data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                                            data-status="Pembimbing 2"
                                                                            data-tanggal-pembimbing1="{{ $row->tanggal_approval_pembimbing1 }}"
                                                                            data-tanggal-pembimbing2="{{ $row->tanggal_approval_pembimbing2 }}"
                                                                            data-judul-proposal="{{ $row->judul_proposal }}"
                                                                            data-pembimbing1="{{ $row->pembimbingPertama->nama }}"
                                                                            data-pembimbing2="{{ $row->pembimbingKedua ? $row->pembimbingKedua->nama : '' }}"
                                                                            data-status-pembimbing1="{{ $row->status_approval_pembimbing1 }}"
                                                                            data-status-pembimbing2="{{ $row->status_approval_pembimbing2 }}"
                                                                            data-status-prodi="{{ $row->status_approval_kaprodi }}"
                                                                            data-note-pembimbing1="{{ $row->note_pembimbing1 }}"
                                                                            data-note-pembimbing2="{{ $row->note_pembimbing2 }}"
                                                                            data-note-prodi="{{ $row->note_kaprodi }}">
                                                                            <i class=" fas fa-folder"></i>
                                                                        </button>
                                                                        @if ($row->proposalRtiForm->ditutup > $now)
                                                                            @if ($row->status_akhir === 1)
                                                                                <form
                                                                                    action="{{ route('proposal.rti.periksa.destroy') }}"
                                                                                    method="post">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <input type="hidden" name="slug"
                                                                                        value="{{ $row->uuid }}">
                                                                                    <input type="hidden"
                                                                                        name="pembimbing" value="2">
                                                                                    <button type="submit"
                                                                                        class="btn btn-sm btn-delete btn-danger mx-1">
                                                                                        <i class="fas fa-trash"></i>
                                                                                    </button>
                                                                                </form>
                                                                            @endif
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endif
                                                <!-- End of Pembimbing 2 -->
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

    <!-- Modal Pembimbing 1 -->
    <div class="modal fade" id="uploadModalPembimbing1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Approval Proposal RTI</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('proposal.rti.periksa.store') }}" class="formApprove" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="pembimbing" value="1">
                        <input type="hidden" name="proposal_id">

                        <div class="form-group">
                            <label for="note">Mahasiswa</label>
                            <div class="input-group">
                                <input class="form-control" name="mahasiswa-detail" disabled readonly />
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-user"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status Approval Proposal RTI</label>
                            <div class="icheck-primary" style="margin-right: 1%;">
                                <input type="radio" id="penilaiPertamaPrimary1" name="status" value="1"
                                    required />
                                <label for="penilaiPertamaPrimary1">Diterima</label>
                            </div>
                            <div class="icheck-danger">
                                <input type="radio" id="penilaiPertamaDanger1" name="status" value="0" />
                                <label for="penilaiPertamaDanger1">Ditolak ke Mahasiswa</label>
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

    <!-- Modal Pembimbing 2 -->
    <div class="modal fade" id="uploadModalPembimbing2">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Approval Proposal RTI</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('proposal.rti.periksa.store') }}" class="formApprove" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="pembimbing" value="2">
                        <input type="hidden" name="proposal_id">

                        <div class="form-group">
                            <label for="note">Mahasiswa</label>
                            <div class="input-group">
                                <input class="form-control" name="mahasiswa-detail" disabled readonly />
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-user"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status Approval Proposal RTI</label>
                            <div class="icheck-primary" style="margin-right: 1%;">
                                <input type="radio" id="penilaiKeduaPrimary1" name="status" value="1"
                                    required />
                                <label for="penilaiKeduaPrimary1">Diterima</label>
                            </div>
                            <div class="icheck-danger">
                                <input type="radio" id="penilaiKeduaDanger1" name="status" value="0" />
                                <label for="penilaiKeduaDanger1">Ditolak ke Mahasiswa</label>
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

    <!-- /.modal -->
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            const table = $('#table-data').DataTable({
                ordering: false,
                order: [
                    [0, 'desc']
                ],
            });

            function format(details) {
                if (details.pembimbing2 !== "") {
                    return '<div>' +
                        '<table class="table table-bordered">' +
                        '<tr>' +
                        '<th style="width: 30%;">Status Saya</th>' +
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
                        '<th style="width: 30%;">Judul Proposal</th>' +
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
                        '<th style="width: 30%;">Tanggal Approval Pembimbing 1</th>' +
                        '<td>' + details.tanggalPembimbing1 + '</td>' +
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
                        '<th style="width: 30%;">Tanggal Approval Pembimbing 2</th>' +
                        '<td>' + details.tanggalPembimbing2 + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<th style="width: 30%;">Note Pembimbing 2</th>' +
                        '<td>' + details.notePembimbing2 + '</td>' +
                        '</tr>' +
                        '</table>' +
                        '</div>'
                } else {
                    return '<div>' +
                        '<table class="table table-bordered">' +
                        '<tr>' +
                        '<th style="width: 30%;">Status Saya</th>' +
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
                        '<th style="width: 30%;">Judul Proposal</th>' +
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
                        '<th style="width: 30%;">Tanggal Approval Pembimbing 1</th>' +
                        '<td>' + details.tanggalPembimbing1 + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<th style="width: 30%;">Note Pembimbing 1</th>' +
                        '<td>' + details.notePembimbing1 + '</td>' +
                        '</tr>' +
                        '</table>' +
                        '</div>'
                }
            }

            $('#table-data').on('click', 'td button.dt-control', function() {
                let tr = $(this).closest('tr');
                let row = table.row(tr);
                let status = $(this).data('status');
                let nim = $(this).data('nim');
                let nama = $(this).data('mahasiswa');
                let judul = $(this).data('judul-proposal');
                let pembimbing1 = $(this).data('pembimbing1');
                let pembimbing2 = $(this).data('pembimbing2');
                let tanggalPembimbing1 = $(this).data('tanggal-pembimbing1');
                let tanggalPembimbing2 = $(this).data('tanggal-pembimbing2');
                let statusPembimbing1 = $(this).data('status-pembimbing1');
                let statusPembimbing2 = $(this).data('status-pembimbing2');
                let notePembimbing1 = $(this).data('note-pembimbing1');
                let notePembimbing2 = $(this).data('note-pembimbing2');

                let textStatusPembimbing1 = null;
                let textStatusPembimbing2 = null;
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
                        textStatusPembimbing2 =
                            '<span class="text-primary font-weight-bold">Diterima</span>';
                    } else if (statusPembimbing2 == 3) {
                        textStatusPembimbing2 =
                            '<span class="text-danger font-weight-bold">Ditolak ke Pembimbing 1</span>';
                    } else {
                        textStatusPembimbing2 =
                            '<span class="text-secondary font-weight-bold">Pending</span>';
                    }
                }

                let details = {};
                details.status = status;
                details.nim = nim;
                details.nama = nama;
                details.judul = judul;
                details.pembimbing1 = pembimbing1;
                details.pembimbing2 = pembimbing2;
                details.tanggalPembimbing1 = tanggalPembimbing1;
                details.tanggalPembimbing2 = tanggalPembimbing2;
                details.textStatusPembimbing1 = textStatusPembimbing1;
                details.textStatusPembimbing2 = textStatusPembimbing2;
                details.notePembimbing1 = notePembimbing1;
                details.notePembimbing2 = notePembimbing2;

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    row.child(format(details)).show();
                    tr.addClass('shown');
                }
            });

            $(document).on('click', '.btn-open-modal', function() {
                let id = $(this).val();
                let nim = $(this).data('nim');
                let nama = $(this).data('nama');
                let mahasiswa = nim + ' - ' + nama;
                $('input[name="proposal_id"]').val(id);
                $('input[name="mahasiswa-detail"]').val(mahasiswa);

            });

            $('#uploadModalPembimbing1').on('hidden.bs.modal', function() {
                $(this).find('input[name="proposal_id"]').val('');
                $(this).find('form').find('input[name="status"]').prop('checked', false);
                $(this).find('form').find('textarea[name="note"]').val('');
            });

            $('#uploadModalPembimbing2').on('hidden.bs.modal', function() {
                $(this).find('input[name="proposal_id"]').val('');
                $(this).find('form').find('input[name="status"]').prop('checked', false);
                $(this).find('form').find('textarea[name="note"]').val('');
            });

            $(document).on('change', 'input[name="status"]', function(e) {
                var value = $(this).attr('value');
                if (value == 0 || value == 3 || value == 4) {
                    $('textarea[name="note"]').attr('required', 'required');
                } else if (value == 1) {
                    $('textarea[name="note"]').removeAttr('required');
                }
            });

            $(document).on('click', '.btn-delete', function(e) {
                if (confirm('Are you sure?')) {
                    $('#overlay').show();
                } else {
                    e.preventDefault();
                }
            });

            $('.formApprove').on('submit', function() {
                $('#overlay').show();
            });
        })
    </script>
@endsection
