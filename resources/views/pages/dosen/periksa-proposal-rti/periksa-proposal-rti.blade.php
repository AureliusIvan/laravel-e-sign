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
                    <h1 class="font-weight-bold">Periksa Proposal RTI</h1>
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
                    <div class="card card-outline card-info scroll">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Periksa Laporan Skripsi
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
                                        <th style="width: 30%;">Judul Periksa Proposal RTI Form</th>
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
                                            <a href="{{ route('proposal.rti.periksa.show', $d->uuid) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-folder"></i>
                                                Details
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

<!-- /.modal -->
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#table-data').DataTable({
            ordering: false,
        });

        $(document).on('click', '.btn-open-modal', function() {
            let id = $(this).val();
            $('input[name="proposal_id"]').val(id);
        });

        $('#uploadModalPembimbing1').on('hidden.bs.modal', function() {
            $(this).find('input[name="proposal_id"]').val('');
        });

        $('#uploadModalPembimbing2').on('hidden.bs.modal', function() {
            $(this).find('input[name="proposal_id"]').val('');
        });

        $(document).on('change', 'input[name="status"]', function(e) {
            var value = $(this).attr('value');
            if (value == 0 || value == 3 || value == 4) {
                $('textarea[name="note"]').attr('required', 'required');
            } else if (value == 1) {
                $('textarea[name="note"]').removeAttr('required');
            }
        });
    })
</script>

@endsection