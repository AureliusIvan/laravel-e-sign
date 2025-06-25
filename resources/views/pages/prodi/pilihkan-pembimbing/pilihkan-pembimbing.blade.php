@extends('layouts.main')

@section('style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .custom {
        margin-left: 0.3em;
        width: 2.5em;
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
@php
$now = date('Y-m-d H:i:s');
@endphp
<div class="overlay" id="overlay">
    <i class="fas fa-sync fa-spin"></i>
</div>
@include('partials.prodi-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Add Pembimbing</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    @if ($form)
                    @if ($form->ditutup > $now)
                    <div class="alert-default-danger px-3 py-2 font-weight-bold rounded" role="alert">
                        Form Permintaan Pembimbing sedang dibuka.
                    </div>
                    @else
                    <div class="card card-primary card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold" id="custom-tabs-four-home-tab"
                                        data-toggle="pill" href="#custom-tabs-four-home" role="tab"
                                        aria-controls="custom-tabs-four-home" aria-selected="true">Mahasiswa
                                        Tanpa Pembimbing</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold" id="custom-tabs-four-profile-tab"
                                        data-toggle="pill" href="#custom-tabs-four-profile" role="tab"
                                        aria-controls="custom-tabs-four-profile" aria-selected="false">Mahasiswa
                                        dan Pembimbing</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-four-tabContent">
                                <div class="tab-pane fade show active" id="custom-tabs-four-home" role="tabpanel"
                                    aria-labelledby="custom-tabs-four-home-tab">

                                    <table id="table-data1" class="table table-bordered table-striped"
                                        style="font-size: 0.9em; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>NIM</th>
                                                <th>Nama</th>
                                                <th>Angkatan</th>
                                                <th>Pembimbing 1</th>
                                                <th>Pembimbing 2</th>
                                                <th style="width: 10%;">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-four-profile" role="tabpanel"
                                    aria-labelledby="custom-tabs-four-profile-tab">

                                    <table id="table-data2" class="table table-bordered table-striped"
                                        style="font-size: 0.9em; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>NIM</th>
                                                <th>Nama</th>
                                                <th>Angkatan</th>
                                                <th>Pembimbing 1</th>
                                                <th>Pembimbing 2</th>
                                                <th style="width: 10%;">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="card card-primary card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active font-weight-bold" id="custom-tabs-four-home-tab"
                                        data-toggle="pill" href="#custom-tabs-four-home" role="tab"
                                        aria-controls="custom-tabs-four-home" aria-selected="true">Mahasiswa
                                        Tanpa Pembimbing</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold" id="custom-tabs-four-profile-tab"
                                        data-toggle="pill" href="#custom-tabs-four-profile" role="tab"
                                        aria-controls="custom-tabs-four-profile" aria-selected="false">Mahasiswa
                                        dan Pembimbing</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-four-tabContent">
                                <div class="tab-pane fade show active" id="custom-tabs-four-home" role="tabpanel"
                                    aria-labelledby="custom-tabs-four-home-tab">

                                    <table id="table-data1" class="table table-bordered table-striped"
                                        style="font-size: 0.9em; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>NIM</th>
                                                <th>Nama</th>
                                                <th>Angkatan</th>
                                                <th>Pembimbing 1</th>
                                                <th>Pembimbing 2</th>
                                                <th style="width: 10%;">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="tab-pane fade" id="custom-tabs-four-profile" role="tabpanel"
                                    aria-labelledby="custom-tabs-four-profile-tab">

                                    <table id="table-data2" class="table table-bordered table-striped"
                                        style="font-size: 0.9em; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>NIM</th>
                                                <th>Nama</th>
                                                <th>Angkatan</th>
                                                <th>Pembimbing 1</th>
                                                <th>Pembimbing 2</th>
                                                <th style="width: 10%;">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Add Dosen -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Dosen Pembimbing</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="dosenPembimbing">
                    @csrf
                    <input type="hidden" id="mahasiswa_id" name="mahasiswa_id">
                    <div class="form-group">
                        <label for="allowed_submission">Mahasiswa</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="nama" aria-describedby="basic-addon2" readonly>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="allowed_submission">Dosen Pembimbing 1</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="pembimbing1" required>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="allowed_submission">Dosen Pembimbing 2 (*optional)</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="pembimbing2">
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
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
        $('#table-data1').DataTable({
            processing: true,
            ajax: {
                url: '/pilihkan-pembimbing/mahasiswatanpapembimbing',
                type: 'GET',
            },
            columns: [{
                    data: "nim"
                },
                {
                    data: "nama"
                },
                {
                    data: "angkatan"
                },
                {
                    "defaultContent": ""
                },
                {
                    "defaultContent": ""
                },
                {
                    data: "id",
                    render: function(data, type, row) {
                        return '<div class="row">' +
                            '<button class="btn btn-info btn-sm custom btn-edit-pembimbing" data-id="' +
                            data + '" data-toggle="modal" data-target="#exampleModal">' +
                            '<i class="fas fa-edit"></i>' +
                            '</button>' +
                            '</div>'
                    }
                }
            ]
        })

        $('#table-data2').DataTable({
            processing: true,
            ajax: {
                url: '/pilihkan-pembimbing/mahasiswapembimbing',
                type: 'GET',
            },
            columns: [{
                    data: "mahasiswa_data.nim"
                },
                {
                    data: "mahasiswa_data.nama"
                },
                {
                    data: "mahasiswa_data.angkatan"
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.pembimbing1 != null) {
                            return row.pembimbing1.nama;
                        }
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.pembimbing2 != null) {
                            return row.pembimbing2.nama;
                        } else {
                            return '';
                        }
                    }
                },
                {
                    data: "mahasiswa_data.id",
                    render: function(data, type, row) {
                        return `<div class="row">
                        <button class="btn btn-info btn-sm custom btn-edit-pembimbing" data-id="${data}" data-toggle="modal" data-target="#exampleModal">
                            <i class="fas fa-edit"></i>
                        </button>
                        </div>`;
                    }
                }
            ]
        });

        $(document).on('click', '.btn-edit-pembimbing', function() {
            let id = $(this).data('id');
            console.log(id);
            $('#overlay').show();
            $.ajax({
                url: `/pilihkan-pembimbing/fetchpembimbing/${id}`,
                method: 'GET',
                success: function(data) {
                    console.log(data);
                    if (data.status == 0) {
                        $('#mahasiswa_id').val(data.data.id)
                        $('#nama').val(data.data.nama)
                        $('select[name="pembimbing1"]').val(null)
                        $('select[name="pembimbing2"]').val(null)
                    } else if (data.status == 1) {
                        $('#mahasiswa_id').val(data.data.mahasiswa_data.id)
                        $('#nama').val(data.data.mahasiswa_data.nama)
                        $('select[name="pembimbing1"]').val(data.data.pembimbing1.id)
                        if (data.data.pembimbing2 != null) {
                            $('select[name="pembimbing2"]').val(data.data.pembimbing2.id)
                        }
                    }
                },
                complete: function() {
                    $('#overlay').hide();
                }
            });
        });

        $('#dosenPembimbing').submit(function(e) {
            e.preventDefault();
            $('#overlay').show();
            $.ajax({
                url: '/pilihkan-pembimbing/store',
                method: 'POST',
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
                    $('#table-data1').DataTable().ajax.reload();
                    $('#table-data2').DataTable().ajax.reload();
                },
                complete: function() {
                    $('#overlay').hide();
                }
            });
            $('#exampleModal').modal('hide');
        })
    });
</script>
@endsection