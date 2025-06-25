@extends('layouts.main')

@section('content')
@include('partials.mahasiswa-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Detail Permintaan Pembimbing</h1>
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

                    <!-- Card -->
                    <div class="card card-outline card-info scroll">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Detail Permintaan Pembimbing
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('caripembimbing') }}" class="btn btn-dark btn-sm">
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
                                        <div class="alert-default-primary px-3 py-2 font-weight-bold rounded"
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
                                    <th style="width: 20%;">Dosen</th>
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
                                        <a href="{{ route('caripembimbing.getfile', $data->uuid) }}" target="_blank">
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
                    </div>
                    <!-- End of Card -->

                </div>
            </div>
        </div>
        <!-- End of Container -->
    </section>
</div>
@endsection


@section('script')

@endsection
