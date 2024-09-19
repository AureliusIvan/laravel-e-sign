@extends('layouts.main')

@section('content')
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
                    <h1 class="font-weight-bold">Permintaan Mahasiswa</h1>
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
                    @php
                    $now = date('Y-m-d H:i:s');
                    @endphp
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
                        <div class="card-body">
                            <p>
                                {{ $d->keterangan }}
                            </p>
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th style="width: 25%;">Dibuka</th>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->dibuka)) }} </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 25%;">Ditutup</th>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->ditutup)) }} </td>
                                    </tr>
                                </table>
                            </div>
                            <div>
                                <p>
                                    <strong>Total Mahasiswa sebagai Pembimbing Pertama : </strong>
                                    {{ $totalPertama }}
                                </p>
                            </div>

                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.9em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>Mahasiswa</th>
                                        <th>Tanggal Permintaan</th>
                                        <th>Research Interest</th>
                                        <th>Status Pembimbing</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($result as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->nama }}</td>
                                        <td>
                                            {{ date('l, d F Y H:i:s', strtotime($row->created_at)) }}
                                        </td>
                                        <td>{{ $row->topik_penelitian }}</td>
                                        <td>
                                            @if ($row->status_pembimbing === 1)
                                            Pembimbing Pertama
                                            @elseif ($row->status_pembimbing === 2)
                                            Pembimbing Kedua
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row->status === 2)
                                            <span class="text-secondary font-weight-bold">
                                                Pending
                                            </span>
                                            @elseif ($row->status === 1)
                                            <span class="text-primary font-weight-bold">
                                                Diterima
                                            </span>
                                            @elseif ($row->status === 0)
                                            <span class="text-danger font-weight-bold">
                                                Ditolak
                                            </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-primary btn-sm btn-edit-data mr-1"
                                                href="{{ route('permintaan.mahasiswa.show', $row->uuid) }}">
                                                <i class="fas fa-folder mr-1"></i>
                                                Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
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

@section('script')
<script>
$(document).ready(function() {
    $('#table-data').DataTable({
        ordering: false,
    })
});
</script>

@endsection
