@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Form Cari Pembimbing</h1>
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
                                    Form Cari Pembimbing
                                </h3>
                                <div class="card-tools">
                                    @if (count($data) == 0)
                                    <a href="{{ route('caripembimbing.form.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1">
                                        </i>
                                        Tambah
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
                                        <th style="width: 5%;">#</th>
                                        <th>Judul Form</th>
                                        <th style="width: 30%;">Keterangan</th>
                                        <th>Dibuka</th>
                                        <th>Ditutup</th>
                                        <th style="width: 15%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $d)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $d->judul_form }}</td>
                                        <td>{{ $d->keterangan }}</td>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->dibuka)) }}</td>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->ditutup)) }}</td>
                                        <td>
                                            <a class="btn btn-info btn-sm btn-edit-data mr-1"
                                                href="{{ route('caripembimbing.form.edit', $d->uuid) }}">
                                                <i class="fas fa-pencil-alt mr-1"></i>
                                                Edit
                                            </a>
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
    $('#table-data').DataTable({
        ordering: false
    });
});
</script>


@endsection
