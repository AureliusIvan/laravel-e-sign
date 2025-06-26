@extends('layouts.main')

@section('style')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
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
$params = Request::segment(3);
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
                    <h1 class="font-weight-bold">Penilai Skripsi</h1>
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
                                    Penilai Proposal
                                </h3>
                                <div class="card-tools">
                                    <button type="button" id="btn-refresh" class="btn btn-sm btn-primary">
                                        <i class="fas fa-sync mr-1"></i>
                                        Refresh Table
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
                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.85em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 3%;">#</th>
                                        <th style="width: 20%;">Skripsi</th>
                                        <th>Topik Penelitian</th>
                                        <th style="width: 15%;">Penilai 1</th>
                                        <th style="width: 15%;">Penilai 2</th>
                                        <th style="width: 15%;">Penilai 3</th>
                                        <th style="width: 10%;"></th>
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
@endsection

@section('script')
<script>
$(document).ready(function() {
    let segment = '{{ Request::segment(3) }}';
    $('#table-data').DataTable({
        processing: true,
        pageLength: 100,
        order: [
            [0, 'desc']
        ],
        ajax: {
            url: '/proposal-skripsi/penilai/' + segment + '/fetchdata',
            method: 'GET',
            // dataSrc: function(json) {
            //     console.log(json);
            //     return json;
            // }
        },
        columns: [{
                data: null,
                render: function(data, type, row) {
                    if (!row.penilai1 && !row.penilai2 && !row.penilai3) {
                        return '<span class="right badge badge-danger">New</span>';
                    } else {
                        return ''
                    }
                },
            },
            {
                data: "judul_proposal",
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (row.topik_penelitian_proposal != null) {
                        return row.topik_penelitian_proposal.map(data =>
                            `<p class="p-0 m-0">${data.research_list.topik_penelitian}</p>`
                        ).join('\n');
                    } else {
                        return '';
                    }
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (row.penilai1 != null) {
                        return '<select name="penilai1" class="form-control form-control-sm select-dosen" data-class="' +
                            row.id + '" data-selected="' + row.penilai1 + '" disabled>' +
                            '</select>'
                    } else {
                        return '<select name="penilai1" class="form-control form-control-sm select-dosen" data-class="' +
                            row.id + '">' +
                            '</select>'
                    }
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (row.penilai2 != null) {
                        return '<select name="penilai2" class="form-control form-control-sm select-dosen" data-class="' +
                            row.id + '" data-selected="' + row.penilai2 + '" disabled>' +
                            '</select>'
                    } else {
                        return '<select name="penilai2" class="form-control form-control-sm select-dosen" data-class="' +
                            row.id + '">' +
                            '</select>'
                    }
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (row.penilai3 != null) {
                        return '<select name="penilai3" class="form-control form-control-sm select-dosen" data-class="' +
                            row.id + '" data-selected="' + row.penilai3 + '" disabled>' +
                            '</select>'
                    } else {
                        return '<select name="penilai3" class="form-control form-control-sm select-dosen" data-class="' +
                            row.id + '">' +
                            '</select>'
                    }
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `<button type="button" class="btn btn-primary btn-submit btn-sm" data-class="${row.id}">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-info btn-edit btn-sm" data-class="${row.id}">
                                                <i class="fas fa-edit"></i>
                                            </button>`
                }
            }
        ]
    });

    function fetchDosen() {
        $('#overlay').show();
        $.ajax({
            url: '/proposal-skripsi/penilai/fetchdosen',
            method: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih ...</option>';
                $.each(data.data, function(index, item) {
                    options += `<option value="${item.id}">${item.nama}</option>`;
                });

                $('#table-data').on('draw.dt', function() {
                    $('.select-dosen').each(function() {
                        let selectedValue = $(this).data('selected');
                        $(this).html(options);
                        $(this).val(selectedValue);
                    });
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching data');
            },
            complete: function() {
                $('#table-data').DataTable().ajax.reload();
                $('#overlay').hide();
            }
        })
    }
    fetchDosen();

    $(document).on('click', '.btn-submit', function() {
        let id = $(this).data('class');
        let selectPenilai1 = $('select[name="penilai1"][data-class="' + id + '"]');
        let selectPenilai2 = $('select[name="penilai2"][data-class="' + id + '"]');
        let selectPenilai3 = $('select[name="penilai3"][data-class="' + id + '"]');
        let penilaiPertama = selectPenilai1.val();
        let penilaiKedua = selectPenilai2.val();
        let penilaiKetiga = selectPenilai3.val();

        if (penilaiPertama == penilaiKedua == penilaiKetiga) {
            alert('Semua penilai sama.');
        } else if (penilaiPertama == penilaiKedua) {
            alert('Penilai pertama sama dengan penilai kedua');
        } else if (penilaiPertama == penilaiKetiga) {
            alert('Penilai pertama sama dengan penilai ketiga');
        } else if (penilaiKedua == penilaiKetiga) {
            alert('Penilai kedua sama dengan penilai ketiga');
        } else {
            $('#overlay').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '/proposal-skripsi/penilai/store',
                method: 'POST',
                data: {
                    id: id,
                    penilai1: penilaiPertama,
                    penilai2: penilaiKedua,
                    penilai3: penilaiKetiga,
                },
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
                    selectPenilai1.prop('disabled', true);
                    selectPenilai2.prop('disabled', true);
                    selectPenilai3.prop('disabled', true);
                }
            });
        }

    });

    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('class');
        let selectPenilai1 = $('select[name="penilai1"][data-class="' + id + '"]');
        let selectPenilai2 = $('select[name="penilai2"][data-class="' + id + '"]');
        let selectPenilai3 = $('select[name="penilai3"][data-class="' + id + '"]');

        selectPenilai1.prop('disabled', false);
        selectPenilai2.prop('disabled', false);
        selectPenilai3.prop('disabled', false);
    });

    $(document).on('click', '#btn-refresh', function() {
        $('#table-data').DataTable().ajax.reload();
    });
});
</script>






@endsection
