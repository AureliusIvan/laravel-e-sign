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
                    <h1 class="font-weight-bold">Periksa Revisi Proposal Skripsi</h1>
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
                                    Periksa Revisi Proposal Skripsi
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('revisi-proposal-skripsi.periksa') }}" id="btn-download"
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
                                        <th style="width: 15%;">File Revisi Proposal</th>
                                        <th style="width: 15%;">File Hasil Koreksi</th>
                                        <th style="width: 10%;">Status Dari Saya</th>
                                        <th style="width: 10%;">Status Lainnya</th>
                                        <th style="width: 15%;">File Penilai Lain</th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $row)
                                    @if ($row->file_revisi_proposal !== null)
                                    <!-- Penilai 1 -->
                                    @if ($row->penilai1 === $dosen->id)
                                    <tr>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai1 === null)
                                            <span class="right badge badge-danger">New</span>
                                            @elseif ($row->status_revisi_approval_penilai1 === 2)
                                            <span class="right badge badge-danger">New</span>
                                            @elseif ($row->status_revisi_approval_penilai2 === 3)
                                            <span class="right badge badge-danger">New</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->mahasiswa->nim }}</td>
                                        <td>{{ $row->mahasiswa->nama }}</td>
                                        <td>
                                            @if ($row->file_revisi_proposal !== null)
                                            <a href="{{ route('revisi-proposal-skripsi.periksa.getfile', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->file_revisi_penilai1 === null)
                                            @if ($row->status_revisi_approval_penilai1 !== 0)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->uuid }}" data-toggle="modal"
                                                data-target="#uploadModalPenilai1">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @endif
                                            @elseif ($row->file_revisi_penilai1 !== null)
                                            @if ($row->status_revisi_approval_penilai1 == 2 &&
                                            $row->status_revisi_approval_penilai2 == 2 &&
                                            $row->status_revisi_approval_penilai2)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->uuid }}" data-toggle="modal"
                                                data-target="#uploadModalPenilai1">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @else
                                            <a href="{{ route('revisi-proposal-skripsi.periksa.file-revisi-penilai1', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai1 === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_revisi_approval_penilai1 === 0)
                                            <span class="text-danger font-weight-bold">Ditolak</span>

                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai2 === 3)
                                            <span class="text-danger font-weight-bold">Ditolak Penilai 2</span>
                                            @elseif ($row->status_revisi_approval_penilai3 === 3)
                                            <span class="text-danger font-weight-bold">Ditolak Penilai 3</span>
                                            @elseif ($row->status_revisi_approval_penilai2 === 1 ||
                                            $row->status_revisi_approval_penilai3 ===
                                            1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai2 === 3)
                                            @if ($row->file_revisi_penilai2 !== null)
                                            <a href="#" class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                            @endif
                                        </td>
                                        <td>
                                            <div class="row">
                                                <button type="button" class="btn btn-sm btn-primary dt-control"
                                                    data-status="Penilai 1"
                                                    data-tanggal="{{ $row->tanggal_approval_revisi_penilai1 }}"
                                                    data-nim="{{ $row->mahasiswa->nim }}"
                                                    data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                    data-judul-revisi="{{ $row->judul_revisi_proposal }}"
                                                    data-penilai1="{{ $row->penilaiPertama->nama }}"
                                                    data-penilai2="{{ $row->penilaiKedua->nama }}"
                                                    data-penilai3="{{ $row->penilaiKetiga->nama }}"
                                                    data-status-penilai1="{{ $row->status_revisi_approval_penilai1 }}"
                                                    data-status-penilai2="{{ $row->status_revisi_approval_penilai2 }}"
                                                    data-status-penilai3="{{ $row->status_revisi_approval_penilai3 }}"
                                                    data-note-penilai1="{{ $row->note_revisi_penilai1 }}"
                                                    data-note-penilai2="{{ $row->note_revisi_penilai2 }}"
                                                    data-note-penilai3="{{ $row->note_revisi_penilai3 }}">
                                                    <i class=" fas fa-folder"></i>
                                                </button>
                                                @if ($row->file_revisi_penilai1 !== null)
                                                @if ($row->status_revisi_approval_penilai2 === 3)
                                                @if ($form->ditutup > $now)
                                                <form action="{{ route('revisi-proposal-skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_revisi" value="1">
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
                                    <!-- End Penilai 1 -->

                                    <!-- Penilai 2 -->
                                    @elseif ($row->penilai2 === $dosen->id)
                                    @if ($row->status_revisi_approval_penilai1 === 1)
                                    <tr>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai2 === null)
                                            <span class="right badge badge-danger">New</span>
                                            @elseif ($row->status_revisi_approval_penilai2 === 2)
                                            <span class="right badge badge-danger">New</span>
                                            @elseif ($row->status_revisi_approval_penilai3 === 4)
                                            <span class="right badge badge-danger">New</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->mahasiswa->nim }}</td>
                                        <td>{{ $row->mahasiswa->nama }}</td>
                                        <td>
                                            @if ($row->file_revisi_penilai1 !== null)
                                            <a href="{{ route('revisi-proposal-skripsi.periksa.file-revisi-penilai1', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->file_revisi_penilai2 === null)
                                            @if ($row->status_revisi_approval_penilai2 !== 0 &&
                                            $row->status_revisi_approval_penilai2 != 3)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->uuid }}" data-toggle="modal"
                                                data-target="#uploadModalPenilai2">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @endif
                                            @elseif ($row->file_revisi_penilai2 !== null)
                                            <a href="{{ route('revisi-proposal-skripsi.periksa.file-revisi-penilai2', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai2 === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_revisi_approval_penilai2 === 0)
                                            <span class="text-danger font-weight-bold">Ditolak</span>
                                            @elseif ($row->status_revisi_approval_penilai2 === 3)
                                            <span class="text-danger font-weight-bold">Ditolak ke Penilai 1</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai3 === 4)
                                            <span class="text-danger font-weight-bold">Ditolak Penilai 3</span>
                                            @elseif ($row->status_revisi_approval_penilai3 ===
                                            1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai3 === 4)
                                            @if ($row->file_revisi_penilai3 !== null)
                                            <a href="{{ route('revisi-proposal-skripsi.periksa.file-revisi-penilai3', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                            @endif
                                        </td>
                                        <td>
                                            <div class="row">
                                                <button type="button" class="btn btn-sm btn-primary dt-control"
                                                    data-nim="{{ $row->mahasiswa->nim }}"
                                                    data-mahasiswa="{{ $row->mahasiswa->nama }}" data-status="Penilai 2"
                                                    data-tanggal="{{ $row->tanggal_approval_revisi_penilai2 }}"
                                                    data-judul-revisi="{{ $row->judul_revisi_proposal }}"
                                                    data-penilai1="{{ $row->penilaiPertama->nama }}"
                                                    data-penilai2="{{ $row->penilaiKedua->nama }}"
                                                    data-penilai3="{{ $row->penilaiKetiga->nama }}"
                                                    data-status-penilai1="{{ $row->status_revisi_approval_penilai1 }}"
                                                    data-status-penilai2="{{ $row->status_revisi_approval_penilai2 }}"
                                                    data-status-penilai3="{{ $row->status_revisi_approval_penilai3 }}"
                                                    data-note-penilai1="{{ $row->note_revisi_penilai1 }}"
                                                    data-note-penilai2="{{ $row->note_revisi_penilai2 }}"
                                                    data-note-penilai3="{{ $row->note_revisi_penilai3 }}">
                                                    <i class=" fas fa-folder"></i>
                                                </button>
                                                @if ($row->file_revisi_penilai2 !== null)
                                                @if ($form->ditutup > $now)

                                                @if ($row->status_revisi_approval_penilai1 === 1 &&
                                                $row->status_revisi_approval_penilai2 === 2)
                                                <form action="{{ route('revisi-proposal-skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_revisi" value="2">
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="btn btn-sm btn-danger mx-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @elseif ($row->status_revisi_approval_penilai3 === 4 &&
                                                $row->status_revisi_approval_penilai2
                                                === 1)
                                                <form action="{{ route('revisi-proposal-skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_revisi" value="2">
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
                                    <!-- End of Penilai 2 -->

                                    <!-- Penilai 3 -->
                                    @elseif ($row->penilai3 === $dosen->id)
                                    @if ($row->status_revisi_approval_penilai2 === 1)
                                    <tr>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai3 === null)
                                            <span class="right badge badge-danger">New</span>
                                            @elseif ($row->status_revisi_approval_penilai3 === 4)
                                            <span class="right badge badge-danger">New</span>
                                            @elseif ($row->status_revisi_approval_penilai3 === 2)
                                            <span class="right badge badge-danger">New</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->mahasiswa->nim }}</td>
                                        <td>{{ $row->mahasiswa->nama }}</td>
                                        <td>
                                            @if ($row->file_revisi_penilai2 !== null)
                                            <a href="{{ route('revisi-proposal-skripsi.periksa.file-revisi-penilai2', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->file_revisi_penilai3 === null)
                                            @if ($row->status_revisi_approval_penilai3 !== 0 &&
                                            $row->status_revisi_approval_penilai3 !== 4)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->uuid }}" data-toggle="modal"
                                                data-target="#uploadModalPenilai3">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @endif
                                            @elseif ($row->file_revisi_penilai3 !== null)
                                            <a href="{{ route('revisi-proposal-skripsi.periksa.file-revisi-penilai3', $row->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status_revisi_approval_penilai3 === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_revisi_approval_penilai3 === 0)
                                            <span class="text-danger font-weight-bold">Ditolak ke Mahasiswa</span>
                                            @elseif ($row->status_revisi_approval_penilai3 === 4)
                                            <span class="text-danger font-weight-bold">Ditolak ke Penilai 2</span>
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
                                                    data-nim="{{ $row->mahasiswa->nim }}"
                                                    data-mahasiswa="{{ $row->mahasiswa->nama }}" data-status="Penilai 3"
                                                    data-tanggal="{{ $row->tanggal_approval_revisi_penilai2 }}"
                                                    data-judul-revisi="{{ $row->judul_revisi_proposal }}"
                                                    data-penilai1="{{ $row->penilaiPertama->nama }}"
                                                    data-penilai2="{{ $row->penilaiKedua->nama }}"
                                                    data-penilai3="{{ $row->penilaiKetiga->nama }}"
                                                    data-status-penilai1="{{ $row->status_revisi_approval_penilai1 }}"
                                                    data-status-penilai2="{{ $row->status_revisi_approval_penilai2 }}"
                                                    data-status-penilai3="{{ $row->status_revisi_approval_penilai3 }}"
                                                    data-note-penilai1="{{ $row->note_revisi_penilai1 }}"
                                                    data-note-penilai2="{{ $row->note_revisi_penilai2 }}"
                                                    data-note-penilai3="{{ $row->note_revisi_penilai3 }}">
                                                    <i class=" fas fa-folder"></i>
                                                </button>
                                                @if ($row->file_revisi_penilai3 !== null)
                                                @if ($form->ditutup > $now)

                                                @if ($row->status_revisi_approval_penilai2 === 1 ||
                                                $row->status_revisi_approval_penilai3 === 2)
                                                <form action="{{ route('revisi-proposal-skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_revisi" value="3">
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="btn btn-sm btn-danger mx-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @elseif ($form->ditutup > $now)
                                                <form action="{{ route('revisi-proposal-skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_revisi" value="3">
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
                                    <!-- End of Penilai 3 -->

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

<!-- Modal Penilai 1 -->
<div class="modal fade" id="uploadModalPenilai1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload File Hasil Periksa Revisi Proposal Penilai 1</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('revisi-proposal-skripsi.periksa.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="penilai" value="1">
                    <input type="hidden" name="revisi_id">

                    <div class="form-group">
                        <label for="status">Status Approval Revisi Proposal</label>
                        <div class="icheck-primary" style="margin-right: 1%;">
                            <input type="radio" id="penilaiPertamaPrimary1" name="status" value="1" required />
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

                    <!-- File Koreksi -->
                    <div class="form-group">
                        <label for="exampleInputFile">
                            Silahkan upload file revisi proposal yang sudah ditanda tangan atau file revisi jika ada
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

<!-- Modal Penilai 2 -->
<div class="modal fade" id="uploadModalPenilai2">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload File Hasil Periksa Revisi Proposal Penilai 2</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('revisi-proposal-skripsi.periksa.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="penilai" value="2">
                    <input type="hidden" name="revisi_id">

                    <div class="form-group">
                        <label for="status">Status Approval Revisi Proposal</label>
                        <div class="icheck-primary" style="margin-right: 1%;">
                            <input type="radio" id="penilaiKeduaPrimary1" name="status" value="1" required />
                            <label for="penilaiKeduaPrimary1">Diterima</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="penilaiKeduaDanger1" name="status" value="0" />
                            <label for="penilaiKeduaDanger1">Ditolak ke Mahasiswa</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="penilaiKeduaDanger2" name="status" value="3" />
                            <label for="penilaiKeduaDanger2">Ditolak ke Penilai Sebelumnya</label>
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
                            Silahkan upload file revisi proposal yang sudah ditanda tangan atau file revisi jika ada
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

<!-- Modal Penilai 3 -->
<div class="modal fade" id="uploadModalPenilai3">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload File Hasil Periksa Revisi Proposal Penilai 3</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('revisi-proposal-skripsi.periksa.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="penilai" value="3">
                    <input type="hidden" name="revisi_id">

                    <div class="form-group">
                        <label for="status">Status Approval Revisi Proposal</label>
                        <div class="icheck-primary" style="margin-right: 1%;">
                            <input type="radio" id="penilaiKetigaPrimary1" name="status" value="1" required />
                            <label for="penilaiKetigaPrimary1">Diterima</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="penilaiKetigaDanger1" name="status" value="0" />
                            <label for="penilaiKetigaDanger1">Ditolak ke Mahasiswa</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="penilaiKetigaDanger2" name="status" value="4" />
                            <label for="penilaiKetigaDanger2">Ditolak ke Penilai Sebelumnya</label>
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
                            Silahkan upload file revisi proposal yang sudah ditanda tangan atau file revisi jika ada
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
                '<th style="width: 30%;">Judul Revisi Proposal</th>' +
                '<td>' + details.judul + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Penilai 1</th>' +
                '<td>' + details.penilai1 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Status Approval Penilai 1</th>' +
                '<td>' + details.textStatusPenilai1 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Note Penilai 1</th>' +
                '<td>' + details.notePenilai1 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Penilai 2</th>' +
                '<td>' + details.penilai2 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Status Approval Penilai 2</th>' +
                '<td>' + details.textStatusPenilai2 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Note Penilai 2</th>' +
                '<td>' + details.notePenilai2 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Penilai 3</th>' +
                '<td>' + details.penilai3 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Status Approval Penilai 3</th>' +
                '<td>' + details.textStatusPenilai3 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Note Penilai 3</th>' +
                '<td>' + details.notePenilai3 + '</td>' +
                '</tr>' +
                '</table>' +
                '</div>'
        }

        $(document).on('click', '.btn-open-modal', function() {
            let id = $(this).val();
            $('input[name="revisi_id"]').val(id);
        });

        $('#uploadModalPenilai1').on('hidden.bs.modal', function() {
            $(this).find('input[name="revisi_id"]').val('');
        });
        $('#uploadModalPenilai2').on('hidden.bs.modal', function() {
            $(this).find('input[name="revisi_id"]').val('');
        });
        $('#uploadModalPenilai3').on('hidden.bs.modal', function() {
            $(this).find('input[name="revisi_id"]').val('');
        });

        $('#table-data').on('click', 'td button.dt-control', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);
            let status = $(this).data('status');
            let tanggal = $(this).data('tanggal');
            let nim = $(this).data('nim');
            let nama = $(this).data('mahasiswa');
            let judul = $(this).data('judul-revisi');
            let penilai1 = $(this).data('penilai1');
            let penilai2 = $(this).data('penilai2');
            let penilai3 = $(this).data('penilai3');
            let statusPenilai1 = $(this).data('status-penilai1');
            let statusPenilai2 = $(this).data('status-penilai2');
            let statusPenilai3 = $(this).data('status-penilai3');
            let notePenilai1 = $(this).data('note-penilai1');
            let notePenilai2 = $(this).data('note-penilai2');
            let notePenilai3 = $(this).data('note-penilai3');

            let textStatusPenilai1 = null;
            let textStatusPenilai2 = null;
            let textStatusPenilai3 = null;
            if (statusPenilai1 == 0) {
                textStatusPenilai1 =
                    '<span class="text-danger font-weight-bold">Ditolak ke Mahasiswa</span>';
            } else if (statusPenilai1 == 1) {
                textStatusPenilai1 = '<span class="text-primary font-weight-bold">Diterima</span>';
            } else {
                textStatusPenilai1 = '<span class="text-secondary font-weight-bold">Pending</span>';
            }

            if (statusPenilai2 == 0) {
                textStatusPenilai2 =
                    '<span class="text-danger font-weight-bold">Ditolak ke Mahasiswa</span>';
            } else if (statusPenilai2 == 1) {
                textStatusPenilai2 = '<span class="text-primary font-weight-bold">Diterima</span>';
            } else if (statusPenilai2 == 3) {
                textStatusPenilai2 =
                    '<span class="text-danger font-weight-bold">Ditolak ke Penilai 1</span>';
            } else {
                textStatusPenilai2 = '<span class="text-secondary font-weight-bold">Pending</span>';
            }

            if (statusPenilai3 == 0) {
                textStatusPenilai3 =
                    '<span class="text-danger font-weight-bold">Ditolak ke Mahasiswa</span>';
            } else if (statusPenilai3 == 1) {
                textStatusPenilai3 = '<span class="text-primary font-weight-bold">Diterima</span>';
            } else if (statusPenilai3 == 4) {
                textStatusPenilai3 =
                    '<span class="text-danger font-weight-bold">Ditolak ke Penilai 2</span>';
            } else {
                textStatusPenilai3 = '<span class="text-secondary font-weight-bold">Pending</span>';
            }

            let details = {};
            details.status = status;
            details.tanggal = tanggal;
            details.nim = nim;
            details.nama = nama;
            details.judul = judul;
            details.penilai1 = penilai1;
            details.penilai2 = penilai2;
            details.penilai3 = penilai3;
            details.textStatusPenilai1 = textStatusPenilai1;
            details.textStatusPenilai2 = textStatusPenilai2;
            details.textStatusPenilai3 = textStatusPenilai3;
            details.notePenilai1 = notePenilai1;
            details.notePenilai2 = notePenilai2;
            details.notePenilai3 = notePenilai3;

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