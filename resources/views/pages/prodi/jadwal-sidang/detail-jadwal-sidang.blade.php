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
                    <h1 class="font-weight-bold">Tambahkan Jadwal Sidang</h1>
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
                                    Tambahkan Jadwal Sidang
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
                            <input type="hidden" name="segment" value="{{ $segment }}">
                            <table id="table-data" class="table table-bordered table-striped"
                                style="font-size: 0.85em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">NIM</th>
                                        <th style="width: 20%;">Nama</th>
                                        <th style="width: 25%;">Judul Laporan</th>
                                        <th style="width: 20%;">Tanggal Sidang</th>
                                        <th style="width: 10%;">Ruang Sidang</th>
                                        <th style="width: 15%;"></th>
                                    </tr>
                                </thead>
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

<!-- Modal Add Jadwal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Jadwal Sidang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="jadwalSidang">
                    @csrf
                    <input type="hidden" name="laporan">
                    <div class="form-group">
                        <label for="nama">Mahasiswa</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="nama" aria-describedby="basic-addon2" readonly>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Sidang -->
                    <div class="form-group">
                        <label for="jadwal_sidang">Jadwal Sidang</label>
                        <div class="input-group">
                            <input type="datetime-local" name="jadwal_sidang" id="jadwal_sidang" class="form-control @error('jadwal_sidang')
                                            is-invalid
                                        @enderror" step="1" value="{{ old('jadwal_sidang') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-calendar"></span>
                                </div>
                            </div>
                        </div>
                        @error('jadwal_sidang')
                        <div class="mt-1 text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="ruang_sidang">Ruang Sidang</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control @error('ruang_sidang') is-invalid @enderror"
                                name="ruang_sidang" aria-describedby="basic-addon2" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                        @error('ruang_sidang')
                        <div class="mt-1 text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="allowed_submission">Pembimbing 1</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="pembimbing1" disabled required>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="allowed_submission">Pembimbing 2</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="pembimbing2" disabled>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="penguji">Penguji</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="penguji" required>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ketua_sidang">Ketua Sidang</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="ketua_sidang" required>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
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

<!-- Modal Edit Jadwal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit Jadwal Sidang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="jadwalSidang">
                    @csrf
                    <input type="hidden" name="laporan">
                    <div class="form-group">
                        <label for="nama">Mahasiswa</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="nama" aria-describedby="basic-addon2" readonly>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Jadwal Sidang -->
                    <div class="form-group">
                        <label for="jadwal_sidang">Jadwal Sidang</label>
                        <div class="input-group">
                            <input type="datetime-local" name="jadwal_sidang" id="jadwal_sidang" class="form-control @error('jadwal_sidang')
                                            is-invalid
                                        @enderror" step="1" value="{{ old('jadwal_sidang') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-calendar"></span>
                                </div>
                            </div>
                        </div>
                        @error('jadwal_sidang')
                        <div class="mt-1 text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="ruang_sidang">Ruang Sidang</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control @error('ruang_sidang') is-invalid @enderror"
                                name="ruang_sidang" aria-describedby="basic-addon2" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                        @error('ruang_sidang')
                        <div class="mt-1 text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="allowed_submission">Pembimbing 1</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="pembimbing1" disabled required>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="allowed_submission">Pembimbing 2</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="pembimbing2" disabled>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="penguji">Penguji</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="penguji" required>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ketua_sidang">Ketua Sidang</label>
                        <div class="input-group mb-3">
                            <select class="select-dosen form-control" name="ketua_sidang" required>
                                <option value="">Tidak ada...</option>
                                @foreach ($dosen as $d)
                                <option value="{{ $d['id'] }}">{{ $d['nama'] }}</option>
                                @endforeach
                            </select>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
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
        let segment = $('input[name="segment"]').val();
        const table = $('#table-data').DataTable({
            processing: true,
            ordering: false,
            ajax: {
                url: '/jadwal-sidang/prodi/fetch-data/' + segment,
                type: 'GET',
            },
            columns: [{
                    data: "mahasiswa.nim",
                },
                {
                    data: "mahasiswa.nama",
                },
                {
                    data: "judul_laporan",
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.jadwal_sidang.length > 0) {
                            let jadwal;
                            row.jadwal_sidang.forEach(item => {
                                jadwal = item.jadwal_sidang;
                            })
                            return jadwal;
                        } else {
                            return "";
                        }
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.jadwal_sidang.length > 0) {
                            let ruang;
                            row.jadwal_sidang.forEach(item => {
                                ruang = item.ruang_sidang;
                            })
                            return ruang;
                        } else {
                            return "";
                        }
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        if (row.jadwal_sidang.length > 0) {
                            let slug;
                            let penguji;
                            let ketuaSidang;
                            row.jadwal_sidang.forEach(item => {
                                slug = item.uuid;
                                penguji = item.penguji.nama;
                                ketuaSidang = item.ketua_sidang.nama;
                            });
                            let pembimbing1 = null;
                            let pembimbing2 = null;
                            if (row.pembimbing_pertama) {
                                pembimbing1 = row.pembimbing_pertama.nama;
                            }
                            if (row.pembimbing_kedua) {
                                pembimbing2 = row.pembimbing_kedua.nama;
                            }
                            return `<div class="row">
                            <button class="btn btn-primary btn-sm custom dt-control" data-id="${slug}" data-pembimbing1="${pembimbing1}" data-pembimbing2="${pembimbing2}" data-mahasiswa="${row.mahasiswa.nama}"  data-nim="${row.mahasiswa.nim}" data-penguji="${penguji}" data-ketua-sidang="${ketuaSidang}">
                                <i class="fas fa-folder"></i>
                            </button>
                            <button class="btn btn-danger btn-sm custom btn-delete-jadwal" data-id="${slug}">
                                <i class="fas fa-trash"></i>
                            </button>
                            </div>`;
                        } else {
                            let pembimbing1 = null;
                            let pembimbing2 = null;
                            if (row.pembimbing_pertama) {
                                pembimbing1 = row.pembimbing_pertama.id;
                            }
                            if (row.pembimbing_kedua) {
                                pembimbing2 = row.pembimbing_kedua.id;
                            }
                            return `<div class="row">
                            <button class="btn btn-primary btn-add-jadwal btn-sm custom" data-toggle="modal" data-target="#addModal" data-pembimbing1="${pembimbing1}" data-pembimbing2="${pembimbing2}" data-mahasiswa="${row.mahasiswa.nama}" data-nim="${row.mahasiswa.nim}" data-laporan="${row.uuid}">
                                <i class="fas fa-plus"></i>
                            </button>
                            </div>`;
                        }
                    }
                }
            ]
        });

        function format(details) {
            return '<div>' +
                '<table class="table table-bordered">' +
                '<tr>' +
                '<th style="width: 30%;">NIM</th>' +
                '<td>' + details.nim + '</td>' +
                '</tr>' +
                '<tr>' +
                '<th style="width: 30%;">Nama Mahasiswa</th>' +
                '<td>' + details.mahasiswa + '</td>' +
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
            let pembimbing1 = $(this).data('pembimbing1');
            let pembimbing2 = $(this).data('pembimbing2');
            let penguji = $(this).data('penguji');
            let ketuaSidang = $(this).data('ketua-sidang');

            let details = {};
            details.pembimbing1 = pembimbing1;
            if (pembimbing2 == null) {
                details.pembimbing2 = '-';
            } else {
                details.pembimbing2 = pembimbing2;
            }
            details.nim = nim;
            details.mahasiswa = mahasiswa;
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

        $(document).on('click', '.btn-add-jadwal', function() {
            let pembimbing1 = $(this).data('pembimbing1');
            let pembimbing2 = $(this).data('pembimbing2');
            let mahasiswa = $(this).data('mahasiswa');
            let slug = $(this).data('laporan');
            $('#nama').val(mahasiswa);
            $('input[name="laporan"]').val(slug);
            $('select[name="pembimbing1"]').val(pembimbing1);
            $('select[name="pembimbing2"]').val(pembimbing2);
        });

        $('#jadwalSidang').submit(function(e) {
            e.preventDefault();
            let pembimbing1 = $('select[name="pembimbing1"]').val();
            let pembimbing2 = $('select[name="pembimbing2"]').val();
            let penguji = $('select[name="penguji"]').val();
            let ketuaSidang = $('select[name="ketua_sidang"]').val();

            if (pembimbing2 !== null) {
                if (pembimbing2 == penguji) {
                    alert('Penguji sama dengan pembimbing 2');
                    return;
                } else if (pembimbing2 == ketuaSidang) {
                    alert('Ketua sidang sama dengan pembimbing 2');
                    return;
                }

                if (pembimbing1 == penguji) {
                    alert('Penguji sama dengan pembimbing 1');
                    return;
                } else if (pembimbing1 == ketuaSidang) {
                    alert('Ketua sidang sama dengan pembimbing 1');
                    return;
                }
            } else {
                if (pembimbing1 == penguji) {
                    alert('Penguji sama dengan pembimbing 1');
                    return;
                } else if (pembimbing1 == ketuaSidang) {
                    alert('Ketua sidang sama dengan pembimbing 1');
                    return;
                }
            }

            $('#overlay').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: '/jadwal-sidang/prodi/store',
                method: 'POST',
                data: $(this).serialize(),
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
                    $('#table-data').DataTable().ajax.reload();
                    $('#addModal').modal('hide');
                }
            });
        });

        $(document).on('click', '.btn-delete-jadwal', function() {
            if (confirm('Are you sure ?')) {
                let slug = $(this).data('id');
                $('#overlay').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: '/jadwal-sidang/prodi/destroy',
                    type: 'POST',
                    data: {
                        slug: slug,
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
                        $('#table-data').DataTable().ajax.reload();
                    }
                });
            }
        });
    });
</script>
@endsection