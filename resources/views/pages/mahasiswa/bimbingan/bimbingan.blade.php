@extends('layouts.main')

@section('style')
<style>
    .collapse.in {
        display: table-row;
    }
</style>
@endsection

@section('content')
@include('partials.mahasiswa-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Bimbingan</h1>
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
                                    Bimbingan
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#addModal">
                                        <i class="fas fa-plus mr-1">
                                        </i>
                                        Tambah
                                    </button>
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                        title="Collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    @if ($pembimbingPertama)
                                    <p><strong>Pembimbing Pertama</strong> : {{ $pembimbingPertama->pembimbing }} </p>
                                    @endif
                                </div>
                                <div class="col-sm-6">
                                    @if ($pembimbingKedua)
                                    <p><strong>Pembimbing Kedua</strong> : {{ $pembimbingKedua->pembimbing }} </p>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <p><strong>Jumlah Bimbingan</strong> : {{ $jumlahBimbinganPembimbingPertama }}</p>
                                </div>
                                <div class="col-sm-6">
                                    @if ($pembimbingKedua)
                                    <p><strong>Jumlah Bimbingan</strong> : {{ $jumlahBimbinganPembimbingKedua }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <p><strong>Minimum Bimbingan</strong> : {{ $minimumBimbinganPertama }}</p>
                                </div>
                                <div class="col-sm-6">
                                    @if ($pembimbingKedua)
                                    <p><strong>Minimum Bimbingan</strong> : {{ $minimumBimbinganKedua }}</p>
                                    @endif
                                </div>
                            </div>
                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.9em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>Tanggal Bimbingan</th>
                                        <th>Dosen</th>
                                        <th>Status</th>
                                        <th style="width: 15%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ date('d-m-Y', strtotime($row->tanggal_bimbingan)) }}</td>
                                        <td>{{ $row->nama }}</td>
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
                                            @if ($row->status === 0)
                                            <a href="{{ route('bimbingan.edit', $row->uuid) }}"
                                                class="btn btn-sm btn-info btn-edit-form" value="{{ $row->uuid }}">
                                                <i class="fas fa-pen"></i>
                                                Edit
                                            </a>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-primary dt-control"
                                                data-bimbingan="{{ $row->isi_bimbingan }}"
                                                data-saran="{{ $row->saran }}" data-catatan="{{ $row->note }}">
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

        <!-- Add Modal -->
        <div class="modal fade" id="addModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Ajukan Permintaan Dosen Pembimbing</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('bimbingan.store') }}" method="post">
                            @csrf

                            <!-- Dosen -->
                            <div class="form-group">
                                <label for="dosen">Dosen <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select name="dosen" id="dosen" class="form-control @error('dosen')
                                            is-invalid
                                        @enderror" required>
                                        <option value="">Pilih ...</option>
                                        @foreach ($pembimbing as $row)
                                        <option value="{{ $row->slug_pembimbing }}">
                                            {{ $row->pembimbing }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-user"></span>
                                        </div>
                                    </div>
                                </div>
                                @error('dosen')
                                <div class="mt-1 text-danger">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>
                            <!-- End -->

                            <!-- Tanggal Bimbingan -->
                            <div class="form-group">
                                <label for="tanggal_bimbingan">Tanggal Bimbingan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="date" name="tanggal_bimbingan" id="tanggal_bimbingan" class="form-control @error('tanggal_bimbingan')
                                            is-invalid
                                        @enderror" value="{{ old('tanggal_bimbingan') }}" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-calendar"></span>
                                        </div>
                                    </div>
                                </div>
                                @error('tanggal_bimbingan')
                                <div class="mt-1 text-danger">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Isi Bimbingan -->
                            <div class="form-group">
                                <label for="isi_bimbingan">Isi Bimbingan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <textarea name="isi_bimbingan" id="isi_bimbingan" class="form-control @error('isi_bimbingan')
                                        is-invalid
                                    @enderror" rows="4" placeholder="Isi Bimbingan"
                                        required>{{ old('isi_bimbingan') }}</textarea>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-pen"></span>
                                        </div>
                                    </div>
                                </div>
                                @error('isi_bimbingan')
                                <div class="mt-1 text-danger">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                            <!-- Saran -->
                            <div class="form-group">
                                <label for="saran">
                                    Saran <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <textarea name="saran" id="saran" class="form-control @error('saran')
                                        is-invalid
                                    @enderror" rows="4" placeholder="Saran" required>{{ old('saran') }}</textarea>
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-pen"></span>
                                        </div>
                                    </div>
                                </div>
                                @error('saran')
                                <div class="mt-1 text-danger">
                                    {{ $message }}
                                </div>
                                @enderror
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
    </section>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        const table = $('#table-data').DataTable();

        function format(details) {
            return '<div class="text-justify">' +
                '<strong>Isi bimbingan :</strong>' +
                '<p>' + details.bimbingan + '</p>' +
                '</div>' +
                '<div class="text-justify">' +
                '<strong>Saran :</strong>' +
                '<p>' + details.saran + '</p>' +
                '</div>' +
                '<div class="text-justify">' +
                '<strong>Catatan Dosen :</strong>' +
                '<p>' + details.catatan + '</p>' +
                '</div>';
        }

        $('#table-data').on('click', 'td button.dt-control', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);
            let bimbingan = $(this).data('bimbingan');
            let saran = $(this).data('saran');
            let catatan = $(this).data('catatan');
            let details = {};
            details.bimbingan = bimbingan;
            details.saran = saran;
            details.catatan = catatan;

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown')
            } else {
                row.child(format(details)).show();
                tr.addClass('shown')
            }
        });
    });
</script>




@endsection