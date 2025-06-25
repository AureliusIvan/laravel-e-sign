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
                    <h1 class="font-weight-bold">List Mahasiswa Bimbingan</h1>
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
                                    List Mahasiswa Bimbingan
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
                            <table id="table-data" class="table table-bordered table-striped" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">#</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Status Pembimbing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row['nim'] }}</td>
                                        <td>{{ $row['nama'] }}</td>
                                        <td>
                                            @if ($row['status_pembimbing'] === 1)
                                            Pembimbing Pertama
                                            @elseif ($row['status_pembimbing'] === 2)
                                            Pembimbing Kedua
                                            @endif
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
        ordering: false,
    })
});
</script>


@endsection