@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Daftar Dosen</h1>
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
                                    Daftar Dosen
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('dosen.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1">
                                        </i>
                                        Tambah
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
                                style="font-size: 0.9em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>NID</th>
                                        <th style="width: 20%;">Nama</th>
                                        <th>Gelar</th>
                                        <th>Program Studi</th>
                                        <th>Role</th>
                                        <th style="width: 10%;">Status</th>
                                        <th style="width: 10%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $d)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $d->nid }}</td>
                                        <td>{{ $d->nama }}</td>
                                        <td>{{ $d->gelar }}</td>
                                        <td>{{ $d->program_studi }}</td>
                                        <td>{{ ucfirst($d->role) }}</td>
                                        <td>
                                            @if ($d->status_aktif === 0)
                                            <form action="{{ route('dosen.update.status', $d->uuid) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Tidak Aktif
                                                </button>
                                            </form>
                                            @elseif ($d->status_aktif === 1)
                                            <form action="{{ route('dosen.update.status', $d->uuid) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    Aktif
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-info btn-sm btn-edit-data mr-1"
                                                href="{{ route('dosen.edit', $d->uuid) }}">
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
    $('#table-data').DataTable();
})
</script>
@endsection
