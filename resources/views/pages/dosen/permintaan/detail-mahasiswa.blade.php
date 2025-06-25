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
<div class="overlay" id="overlay">
    <i class="fas fa-sync fa-spin"></i>
</div>
@php
$now = date('Y-m-d H:i:s');
@endphp
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
                    <h1 class="font-weight-bold">Detail Permintaan Mahasiswa</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Container -->
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-12" id="accordion">
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
                                    Detail Permintaan Mahasiswa
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('permintaan.mahasiswa') }}" class="btn btn-dark btn-sm">
                                        <i class="fas fa-chevron-left mr-1"></i>
                                        Back
                                    </a>
                                    @if ($form->ditutup > $now)
                                    @if ($data->status === 2)
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#addModal">
                                        <i class="fas fa-plus mr-1"></i>
                                        Tambahkan Balasan
                                    </button>
                                    @endif
                                    @endif
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        @if ($data)
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th style="width: 20%;">Status</th>
                                    <td class="p-1">
                                        @if ($data->status === 2)
                                        <div class="alert-default-secondary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Pending
                                        </div>
                                        @elseif ($data->status === 1)
                                        <div class="alert-default-success px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Diterima
                                        </div>
                                        @elseif ($data->status === 0)
                                        <div class="alert-default-danger px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Ditolak
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">NIM</th>
                                    <td>{{ $data->nim }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Nama Mahasiswa</th>
                                    <td>{{ $data->nama }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Tanggal Permintaan</th>
                                    <td>{{ date('l, d F Y H:i:s', strtotime($data->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Permintaan untuk</th>
                                    <td>
                                        @if ($data->status_pembimbing === 1)
                                        Pembimbing Pertama
                                        @elseif ($data->status_pembimbing === 2)
                                        Pembimbing Kedua
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Research Interest</th>
                                    <td>{{ $data->topik_penelitian }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Tipe File Pendukung</th>
                                    <td>
                                        @if ($data->is_rti === 1)
                                        File Proposal RTI
                                        @elseif ($data->is_uploaded === 1)
                                        File Proposal Lama
                                        @else
                                        Tidak ada
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">File Pendukung</th>
                                    <td>
                                        <a href="{{ route('permintaan.mahasiswa.getfile', $data->uuid) }}"
                                            target="_blank">
                                            {{ $data->file_pendukung }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Pesan dari Mahasiswa</th>
                                    <td>{{ $data->note_mahasiswa }}</td>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">Pesan dari Dosen</th>
                                    <td>{{ $data->note_dosen }}</td>
                                </tr>
                            </table>
                        </div>
                        @endif
                    </div>
                    <!-- End of Card -->

                </div>
            </div>
        </div>
        <!-- End of Container -->
    </section>
</div>

<!-- Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambahkan Balasan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addForm" action="{{ route('permintaan.mahasiswa.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="permintaan_mahasiswa" value="{{ $data->uuid }}">

                    <!-- Mahasiswa -->
                    <div class="form-group">
                        <label for="inputProgramStudi">Mahasiswa <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="inputProgramStudi" class="form-control"
                                value="{{ $data->nama }} - {{ $data->nim }}" required readonly>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End -->

                    <!-- Status diterima atau tidak -->
                    <!-- Yes or No -->
                    <div class="form-group">
                        <label for="status">
                            Status diterima atau tidak sebagai mahasiswa bimbingan
                        </label>
                        <div class="icheck-primary">
                            <input type="radio" id="radioPrimary1" name="status" value="1" required>
                            <label for="radioPrimary1">
                                Diterima
                            </label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="radioDanger1" name="status" value="0">
                            <label for="radioDanger1">
                                Ditolak
                            </label>
                        </div>
                    </div>
                    <!-- End -->

                    <!-- Pesan untuk mahasiswa -->
                    <div class="form-group">
                        <label for="note_dosen">Pesan untuk mahasiswa <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <textarea name="note_dosen" id="note_dosen" class="form-control @error('note_dosen')
                                            is-invalid
                                        @enderror" placeholder="Enter pesan" rows="6"
                                required>{{ old('note_dosen') }}</textarea>
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
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#addForm').on('submit', function(e) {
        $('#overlay').show();
    });
});
</script>

@endsection
