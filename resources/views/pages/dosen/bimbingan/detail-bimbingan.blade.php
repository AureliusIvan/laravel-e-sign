@extends('layouts.main')

@section('style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
.custom {
    width: 2.5em !important;
    margin-right: 0.25em;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-content: center;
    text-align: center;
}

.overlay .fa-sync {
    font-size: 5rem;
    color: white;
}
</style>
@endsection

@section('content')
<div class="overlay" id="overlay">
    <i class="fas fa-sync fa-spin"></i>
</div>
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
                    <h1 class="font-weight-bold">Update Bimbingan</h1>
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
                                    Update Bimbingan
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('bimbingan.dosen') }}" class="btn btn-dark btn-sm">
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
                            <input type="hidden" name="bimbingan" value="{{ $mahasiswa->uuid }}">
                            <div class="row">
                                <div class="col-sm-1">
                                    <p><strong>NIM</strong></p>
                                </div>
                                <div class="col-sm-11">
                                    <p>: {{ $mahasiswa->nim }} </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-1">
                                    <p><strong>Nama</strong></p>
                                </div>
                                <div class="col-sm-11">
                                    <p>: {{ $mahasiswa->nama }} </p>
                                </div>
                            </div>
                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.9em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>Tanggal Bimbingan</th>
                                        <th style="width: 25%;">Isi Bimbingan</th>
                                        <th style="width: 25%;">Saran</th>
                                        <th>Status</th>
                                        <th style="width: 15%;"></th>
                                    </tr>
                                </thead>
                                <tbody>

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

<!-- Modal Reject Bimbingan -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModallLabel">Reject Bimbingan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reject-bimbingan">
                    @csrf
                    <input type="hidden" id="slug" name="slug">
                    <input type="hidden" name="status" value="0">
                    <div class="form-group">
                        <label for="note">Catatan</label>
                        <div class="input-group mb-3">
                            <textarea name="note" id="note" class="form-control @error('note')
                    is-invalid
                @enderror" rows="4" placeholder="Catatan" required>{{ old('note') }}</textarea>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    let slug = $('input[name="bimbingan"]').val();

    $('#table-data').DataTable({
        processing: true,
        ordering: false,
        ajax: {
            url: '/bimbingan/dosen/fetch-all/' + slug,
            type: 'GET',
        },
        columnDefs: [{
            targets: 0,
            createdCell: function(td, cellData, rowData, row, col) {
                $(td).html(row + 1);
            },
        }],
        columns: [{
                data: null
            },
            {
                data: "tanggal_bimbingan",
                render: function(data, type, row) {
                    var dateAr = data.split('-');
                    var newDate = dateAr[2] + '-' + dateAr[1] + '-' + dateAr[0];
                    return newDate;
                }
            },
            {
                data: "isi_bimbingan"
            },
            {
                data: "saran"
            },
            {
                data: "status",
                render: function(data, type, row) {
                    if (row.status == 2) {
                        return '<span class="text-secondary font-weight-bold">Pending</span>';
                    } else if (row.status == 1) {
                        return '<span class="text-primary font-weight-bold">Diterima</span>';
                    } else if (row.status == 0) {
                        return '<span class="text-danger font-weight-bold">Ditolak</span>';
                    }
                }
            },
            {
                data: "uuid",
                render: function(data, type, row) {
                    if (row.status == 2) {
                        return '<button class="btn btn-primary btn-sm btn-accept-bimbingan custom" value="' +
                            data +
                            '">' +
                            '<i class="fas fa-check"></i>' +
                            '</button>' +
                            '<button class="btn btn-danger btn-sm btn-reject-bimbingan custom" value="' +
                            data +
                            '" data-toggle="modal" data-target="#rejectModal">' +
                            '<i class="fas fa-times"></i>' +
                            '</button>'
                    } else {
                        return ""
                    }
                },
            }
        ]
    });


    $(document).on('click', '.btn-accept-bimbingan', function(e) {
        if (confirm('Are you sure?')) {
            $('#overlay').show();
            var slug = $(this).attr('value');
            var status = 1;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: '/bimbingan/dosen/update-status',
                type: 'POST',
                data: {
                    slug: slug,
                    status: status,
                },
                dataType: 'json',
                success: function(data) {
                    if (data.errors) {
                        let errors = '<ul>'
                        $.each(data.errors, function(key, value) {
                            errors += '<li>' + value + '</li>';
                        });
                        errors += '</ul>';
                        $(document).Toasts('create', {
                            title: 'Gagal',
                            class: 'bg-danger',
                            autohide: true,
                            delay: 10000,
                            body: errors
                        });
                    }
                    if (data.status == "success") {
                        $(document).Toasts('create', {
                            title: 'Success',
                            class: 'bg-success',
                            autohide: true,
                            delay: 10000,
                            body: data.message
                        });
                        $('#table-data').DataTable().ajax.reload();
                    } else if (data.status == "error") {
                        $(document).Toasts('create', {
                            title: 'Gagal',
                            class: 'bg-danger',
                            autohide: true,
                            delay: 10000,
                            body: data.message
                        })
                    }
                },
                complete: function() {
                    $('#overlay').hide();
                }
            })
        }
    });

    $(document).on('click', '.btn-reject-bimbingan', function(e) {
        var slug = $(this).attr('value');
        $('input[name="slug"]').val(slug);
    });

    $('#reject-bimbingan').submit(function(e) {
        e.preventDefault();
        $('#overlay').show();
        $.ajax({
            url: '/bimbingan/dosen/update-status',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(data) {
                if (data.errors) {
                    let errors = '<ul>'
                    $.each(data.errors, function(key, value) {
                        errors += '<li>' + value + '</li>';
                    });
                    errors += '</ul>';
                    $(document).Toasts('create', {
                        title: 'Gagal',
                        class: 'bg-danger',
                        autohide: true,
                        delay: 10000,
                        body: errors
                    });
                }
                if (data.status == "success") {
                    $(document).Toasts('create', {
                        title: 'Success',
                        class: 'bg-success',
                        autohide: true,
                        delay: 10000,
                        body: data.message
                    });
                    $('#table-data').DataTable().ajax.reload();
                } else if (data.status == "error") {
                    $(document).Toasts('create', {
                        title: 'Gagal',
                        class: 'bg-danger',
                        autohide: true,
                        delay: 10000,
                        body: data.message
                    })
                }
            },
            complete: function() {
                $('#overlay').hide();
                $('#rejectModal').modal('hide');
            }
        });
    });

});
</script>



@endsection