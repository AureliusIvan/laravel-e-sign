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
                    <h1 class="font-weight-bold">Jadwal Sidang</h1>
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
                    <div class="card card-outline card-info scroll">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Jadwal Sidang
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
                                            <button type="button" class="btn btn-sm btn-info dt-control"
                                                data-nim="{{ $row->mahasiswa->nim }}"
                                                data-mahasiswa="{{ $row->mahasiswa->nama }}"
                                                data-judul="{{ $row->laporanAkhir->judul_laporan }}"
                                                data-pembimbing1="{{ $row->pembimbingPertama->nama }}"
                                                data-pembimbing2="{{ $row->pembimbingKedua ? $row->pembimbingKedua->nama : null }}"
                                                data-penguji="{{ $row->pengujiSidang->nama }}"
                                                data-ketua-sidang="{{ $row->ketuaSidang->nama }}">
                                                <i class="fas fa-folder"></i>
                                                Details
                                            </button>
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
@endsection

@section('script')
<script>
    $(document).ready(function() {
        const table = $('#table-data').DataTable({
            ordering: false
        });

        function format(details) {
            return '<div>' +
                '<table class="table">' +
                '<tr>' +
                '<th style="width: 30%;">NIM</th>' +
                '<td>' + details.nim + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Nama Mahasiswa</th>' +
                '<td>' + details.mahasiswa + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Judul Skripsi</th>' +
                '<td>' + details.judul + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Pembimbing 1</th>' +
                '<td>' + details.pembimbing1 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Pembimbing 2</th>' +
                '<td>' + details.pembimbing2 + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Penguji</th>' +
                '<td>' + details.penguji + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Ketua Sidang</th>' +
                '<td>' + details.ketuaSidang + '</td>' +
                '</tr>' +
                '</table>' +
                '</div>'
        }

        $('#table-data').on('click', 'td button.dt-control', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);
            let nim = $(this).data('nim');
            let mahasiswa = $(this).data('mahasiswa');
            let judul = $(this).data('judul');
            let pembimbing1 = $(this).data('pembimbing1');
            let pembimbing2 = $(this).data('pembimbing2');
            let penguji = $(this).data('penguji');
            let ketuaSidang = $(this).data('ketua-sidang');

            details = {};
            details.nim = nim;
            details.mahasiswa = mahasiswa;
            details.judul = judul;
            details.pembimbing1 = pembimbing1;
            if (pembimbing2 == null) {
                details.pembimbing2 = '-';
            } else {
                details.pembimbing2 = pembimbing2;
            }
            details.penguji = penguji;
            details.ketuaSidang = ketuaSidang;

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
            } else {
                row.child(format(details)).show();
                tr.addClass('shown');
            }
        });
    });
</script>





@endsection