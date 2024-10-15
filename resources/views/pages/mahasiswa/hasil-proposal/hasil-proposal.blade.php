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
                    <h1 class="font-weight-bold">Hasil Proposal Skripsi</h1>
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
                                        <th style="width: 25%;">Judul Proposal Skripsi</th>
                                        <td>
                                            {{ $row->judul_proposal }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 25%;">Status Proposal Skripsi</th>
                                        <td>
                                            @if ($row->status_akhir === 1)
                                            <span class="text-primary font-weight-bold">
                                                Diterima
                                            </span>
                                            @elseif ($row->status_akhir === 0)
                                            <span class="text-danger font-weight-bold">
                                                Ditolak
                                            </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                @if ($row->status_akhir !== null)
                                <table class="table-data table table-bordered table-striped"
                                    style="font-size: 0.9em; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Penilai</th>
                                            <th>Status Penilai</th>
                                            <th>Status Approval</th>
                                            <th>File Hasil Periksa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                @if ($row->penilaiPertama)
                                                {{ $row->penilaiPertama->nama }}
                                                @endif
                                            </td>
                                            <td>Penilai 1</td>
                                            <td>
                                                @if ($row->status_approval_penilai1 === 1)
                                                <span class="text-primary font-weight-bold">
                                                    Diterima
                                                </span>
                                                @elseif ($row->status_approval_penilai1 === 0)
                                                <span class="text-danger font-weight-bold">
                                                    Ditolak
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
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                @if ($row->penilaiKedua)
                                                {{ $row->penilaiKedua->nama }}
                                                @endif
                                            </td>
                                            <td>Penilai 2</td>
                                            <td>
                                                @if ($row->status_approval_penilai2 === 1)
                                                <span class="text-primary font-weight-bold">
                                                    Diterima
                                                </span>
                                                @elseif ($row->status_approval_penilai2 === 0)
                                                <span class="text-danger font-weight-bold">
                                                    Ditolak
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
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                @if ($row->penilaiKetiga)
                                                {{ $row->penilaiKetiga->nama }}
                                                @endif
                                            </td>
                                            <td>Penilai 3</td>
                                            <td>
                                                @if ($row->status_approval_penilai3 === 1)
                                                <span class="text-primary font-weight-bold">
                                                    Diterima
                                                </span>
                                                @elseif ($row->status_approval_penilai3 === 0)
                                                <span class="text-danger font-weight-bold">
                                                    Ditolak
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
                                                @endif
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                                @endif
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
