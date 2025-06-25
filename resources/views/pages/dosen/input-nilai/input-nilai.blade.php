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
                    <h1 class="font-weight-bold">Input Nilai Mahasiswa</h1>
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

                    @if ($kategori == true && $detail == true)
                    <!-- Card -->
                    <div class="card card-outline card-info scroll">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Input Nilai Mahasiswa
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
                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.85em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th style="width: 15%;">File Laporan</th>
                                        <th>Tanggal Sidang</th>
                                        <th>Ruang Sidang</th>
                                        <th style="width: 15%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }} </td>
                                        <td>{{ $row->mahasiswa->nim }} </td>
                                        <td>{{ $row->mahasiswa->nama }} </td>
                                        <td>
                                            <a href="{{ route('nilai.sidang.file-laporan', $row->laporanAkhir->uuid) }}"
                                                class="btn btn-info btn-sm" target="_blank">
                                                <i class="fas fa-download mr-1"></i>
                                                Download
                                            </a>
                                        </td>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($row->jadwal_sidang)) }} </td>
                                        <td>{{ $row->ruang_sidang }} </td>
                                        <td>
                                            <a href="{{ route('nilai.sidang.show', $row->uuid) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i>
                                                Input
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End of Card -->

                    @else
                    <div class="alert alert-default-danger">
                        Rubrik penilaian belum dibuat.
                    </div>
                    @endif

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
        const table = $('#table-data').DataTable({
            ordering: false
        });
    });
</script>





@endsection