@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Form Pengumpulan Proposal RTI</h1>
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

                    @if ($isFormOpen == true)
                    <div class="alert-default-danger px-3 py-2 mb-2 font-weight-bold rounded" role="alert">
                        Tidak dapat membuat pengumpulan proposal rti karena form pencarian pembimbing sedang dibuka.
                    </div>
                    @endif

                    @if ($isMahasiswaWithoutPembimbing == true)
                    <div class="alert-default-danger px-3 py-2 mb-2 font-weight-bold rounded" role="alert">
                        Tidak dapat membuat pengumpulan proposal rti karena ada mahasiswa yang belum memiliki
                        pembimbing.
                    </div>
                    @endif

                    <!-- Card -->
                    <div class="card card-outline card-info scroll">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Form Pengumpulan Proposal RTI
                                </h3>
                                <div class="card-tools">
                                    @if ($isMahasiswaWithoutPembimbing == false && $isFormOpen == false)
                                    @if (count($data) == 0)
                                    <a href="{{ route('proposal.rti.form.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1">
                                        </i>
                                        Tambah
                                    </a>
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
                        <div class="card-body">
                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.85em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 30%;">Judul Pengumpulan Proposal RTI Form</th>
                                        <th>Dibuka</th>
                                        <th>Ditutup</th>
                                        <th style="width: 15%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $d)
                                    <tr data-child-value="{{ $d->keterangan }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $d->judul_form }}</td>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->dibuka)) }}</td>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->ditutup)) }}</td>
                                        <td>
                                            <a class="btn btn-info btn-sm btn-edit-data mr-1"
                                                href="{{ route('proposal.rti.form.edit', $d->uuid) }}">
                                                <i class="fas fa-pencil-alt mr-1"></i>
                                                Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-primary dt-control">
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

        function format(value) {
            return '<div>' +
                '<strong>Keterangan :</strong>' +
                '<p>' + value + '</p>' +
                '</div>';
        }

        $('#table-data').on('click', 'td button.dt-control', function() {
            let tr = $(this).closest('tr');
            let row = table.row(tr);

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown')
            } else {
                row.child(format(tr.data('child-value'))).show();
                tr.addClass('shown')
            }
        });
    });
</script>



@endsection
