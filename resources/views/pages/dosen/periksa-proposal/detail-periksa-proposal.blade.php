@extends('layouts.main')

@section('style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
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
                    <h1 class="font-weight-bold">Periksa Proposal Skripsi</h1>
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
                                    Periksa Proposal Skripsi
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('proposal.skripsi.periksa') }}" id="btn-download"
                                        class="btn btn-sm btn-dark">
                                        <i class="fas fa-chevron-left mr-1"></i>
                                        Back
                                    </a>
                                    @if (count($data) > 0)
                                    <a href="{{ route('proposal.skripsi.periksa.store.download-multiple-file', $form->uuid) }}"
                                        id="btn-download" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-download mr-1"></i>
                                        Download Semua Proposal
                                    </a>
                                    @endif
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
                                style="font-size: 0.85em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 3%;">#</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th style="width: 20%;">Judul Proposal</th>
                                        <th>Status</th>
                                        <th>File Koreksi</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>
                                            @if ($row->penilai1 == $dosen->id)
                                            @if ($row->file_penilai1 == null)
                                            <span class="right badge badge-danger">New</span>
                                            @endif

                                            @elseif ($row->penilai2 == $dosen->id)
                                            @if ($row->file_penilai2 == null)
                                            <span class="right badge badge-danger">New</span>
                                            @endif

                                            @elseif ($row->penilai3 == $dosen->id)
                                            @if ($row->file_penilai3 == null)
                                            <span class="right badge badge-danger">New</span>
                                            @endif

                                            @endif
                                        </td>
                                        <td>{{ $row->mahasiswa->nim }}</td>
                                        <td>{{ $row->mahasiswa->nama }}</td>
                                        <td>{{ $row->judul_proposal }}</td>
                                        <td>
                                            @if ($row->penilai1 == $dosen->id)
                                            @if ($row->status_approval_penilai1 === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_approval_penilai1 === 0)
                                            <span class="text-danger font-weight-bold">Ditolak</span>
                                            @endif

                                            @elseif ($row->penilai2 == $dosen->id)
                                            @if ($row->status_approval_penilai2 === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_approval_penilai2 === 0)
                                            <span class="text-danger font-weight-bold">Ditolak</span>
                                            @endif

                                            @elseif ($row->penilai3 == $dosen->id)
                                            @if ($row->status_approval_penilai3 === 1)
                                            <span class="text-primary font-weight-bold">Diterima</span>
                                            @elseif ($row->status_approval_penilai3 === 0)
                                            <span class="text-danger font-weight-bold">Ditolak</span>
                                            @endif

                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->penilai1 == $dosen->id)

                                            @if ($row->file_penilai1 == null)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->id }}" data-toggle="modal"
                                                data-target="#uploadModalPenilai" data-penilai="1">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @else
                                            <a href="{{ route('proposal.skripsi.periksa.store.download-file-periksa', [$row->uuid, 1]) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif

                                            @elseif ($row->penilai2 == $dosen->id)

                                            @if ($row->file_penilai2 == null)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->id }}" data-toggle="modal"
                                                data-target="#uploadModalPenilai" data-penilai="2">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @else
                                            <a href="{{ route('proposal.skripsi.periksa.store.download-file-periksa', [$row->uuid, 2]) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif

                                            @elseif ($row->penilai3 == $dosen->id)

                                            @if ($row->file_penilai3 == null)
                                            <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                value="{{ $row->id }}" data-toggle="modal"
                                                data-target="#uploadModalPenilai" data-penilai="3">
                                                <i class="fas fa-upload mr-1"></i>
                                                Upload File
                                            </button>
                                            @else
                                            <a href="{{ route('proposal.skripsi.periksa.store.download-file-periksa', [$row->uuid, 3]) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                            @endif

                                            @endif
                                        </td>
                                        <td>
                                            <div class="row">
                                                @if ($row->penilai1 == $dosen->id)

                                                @if ($row->file_penilai1 != null)
                                                @if ($form->deadline_penilaian > $now)
                                                <form action="{{ route('proposal.skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_proposal" value="1">
                                                    <button type="submit" class="btn btn-sm btn-danger btn-delete mx-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                @endif

                                                @elseif ($row->penilai2 == $dosen->id)

                                                @if ($row->file_penilai2 != null)
                                                <form action="{{ route('proposal.skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_proposal" value="2">
                                                    <button type="submit" class="btn btn-sm btn-danger btn-delete mx-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                @elseif ($row->penilai3 == $dosen->id)

                                                @if ($row->file_penilai3 != null)
                                                <form action="{{ route('proposal.skripsi.periksa.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <input type="hidden" name="penilai_proposal" value="3">
                                                    <button type="submit" onclick="return confirm('Are you sure?')"
                                                        class="btn btn-sm btn-danger mx-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endif

                                                @endif
                                            </div>
                                        </td>
                                    </tr>
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

<!-- Modal -->
<div class="modal fade" id="uploadModalPenilai">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload File Hasil Periksa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addForm" action="{{ route('proposal.skripsi.periksa.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="penilai">
                    <input type="hidden" name="proposal_id">

                    <div class="form-group">
                        <label for="status">Status Approval Proposal</label>
                        <div class="icheck-primary" style="margin-right: 1%;">
                            <input type="radio" id="radioPrimary1" name="status" value="1" required />
                            <label for="radioPrimary1">Diterima</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="radioDanger1" name="status" value="0" />
                            <label for="radioDanger1">Ditolak</label>
                        </div>
                    </div>

                    <!-- File Koreksi -->
                    <div class="form-group">
                        <label for="exampleInputFile">
                            Silahkan upload file proposal RTI atau proposal yang sudah anda
                            buat
                        </label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile" name="file"
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
        $('#table-data').DataTable({
            pageLength: 100,
            order: [
                [0, 'desc']
            ]
        });

        $(document).on('click', '.btn-open-modal', function() {
            let id = $(this).val();
            let penilai = $(this).data('penilai');
            $('input[name="penilai"]').val(penilai);
            $('input[name="proposal_id"]').val(id);
        });

        $(document).on('click', '.btn-delete', function() {
            if (confirm('Are you sure?')) {
                $('#overlay').show();
            }
        });

        $('#addForm').on('submit', function(e) {
            $('#overlay').show();
        });

        $('#uploadModalPenilai').on('hidden.bs.modal', function() {
            $(this).find('input[name="penilai"]').val('');
            $(this).find('input[name="proposal_id"]').val('');
        });
    });
</script>



@endsection
