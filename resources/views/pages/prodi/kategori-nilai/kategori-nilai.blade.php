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
@include('partials.prodi-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Daftar Kategori Nilai</h1>
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
                                    Daftar Kategori Nilai
                                </h3>
                                <div class="card-tools">
                                    <a href="{{ route('kategori.nilai.create') }}" class="btn btn-primary btn-sm">
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
                            @if ($pembimbing != 100)
                            <div class="alert alert-default-danger">
                                Persentase pembimbing tidak sama dengan 100 persen.
                            </div>
                            @endif
                            @if ($penguji != 100)
                            <div class="alert alert-default-danger">
                                Persentase penguji tidak sama dengan 100 persen.
                            </div>
                            @endif
                            @if ($ketuaSidang != 100)
                            <div class="alert alert-default-danger">
                                Persentase ketua sidang tidak sama dengan 100 persen.
                            </div>
                            @endif
                            <table id="table-data" class="table table-bordered" style="font-size: 1em; width: 100%;">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th>Kategori Penilaian</th>
                                        <th>Persentase</th>
                                        <th>Kategori Untuk</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data as $d)
                                    <tr data-child-value="{{ $d->kategoriNilaiDetail ? $d->kategoriNilaiDetail : '' }}">
                                        <td>{{ $loop->iteration }} </td>
                                        <td>{{ $d->kategori }} </td>
                                        <td>{{ $d->persentase }} </td>
                                        <td>
                                            @if ($d->user === 'pembimbing')
                                            Pembimbing
                                            @elseif ($d->user === 'penguji')
                                            Penguji
                                            @elseif ($d->user === 'ketua_sidang')
                                            Ketua Sidang
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm mr-1 dt-control">
                                                <i class="fas fa-folder mr-1"></i>
                                                Details
                                            </button>
                                            <a class="btn btn-info btn-sm btn-edit-data mr-1"
                                                href="{{ route('kategori.nilai.edit', $d->uuid) }}">
                                                <i class="fas fa-pencil-alt mr-1"></i>
                                                Edit
                                            </a>
                                            <button type="button" class="btn btn-primary btn-sm mr-1 btn-add-detail"
                                                data-toggle="modal" data-target="#addModal" data-value="{{ $d->uuid }}">
                                                <i class="fas fa-plus mr-1"></i>
                                                Tambah
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

<!-- Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Detail Kategori</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form -->
                <form id="formTambah" method="post" action="{{ route('kategori.nilai.detail.store') }}">
                    @csrf
                    <input type="hidden" name="slug">

                    <!-- Kategori Nilai -->
                    <div class="form-group">
                        <label for="detail_kategori">Detail Kategori Nilai <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="detail_kategori" class="form-control @error('detail_kategori')
                                            is-invalid
                                        @enderror" placeholder="Enter Kategori Nilai"
                                value="{{ old('detail_kategori') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                        @error('detail_kategori')
                        <div class="mt-1 text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Persentase -->
                    <div class="form-group">
                        <label for="detail_persentase">Detail Persentase <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="detail_persentase" class="form-control @error('detail_persentase')
                                            is-invalid
                                        @enderror" placeholder="Enter Persentase"
                                value="{{ old('detail_persentase') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-percent"></span>
                                </div>
                            </div>
                        </div>
                        @error('detail_persentase')
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
                <!-- End Form -->
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Detail Kategori</h4>

                <button type="button" class="ml-1 close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Form -->
                <form id="formEdit" method="post" action="{{ route('kategori.nilai.detail.update') }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_slug">

                    <!-- Kategori Nilai -->
                    <div class="form-group">
                        <label for="detail_kategori">Detail Kategori Nilai <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="edit_detail_kategori" class="form-control @error('detail_kategori')
                                            is-invalid
                                        @enderror" placeholder="Enter Kategori Nilai"
                                value="{{ old('detail_kategori') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-pen"></span>
                                </div>
                            </div>
                        </div>
                        @error('detail_kategori')
                        <div class="mt-1 text-danger">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <!-- Persentase -->
                    <div class="form-group">
                        <label for="detail_persentase">Detail Persentase <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="edit_detail_persentase" class="form-control @error('detail_persentase')
                                            is-invalid
                                        @enderror" placeholder="Enter Persentase"
                                value="{{ old('detail_persentase') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-percent"></span>
                                </div>
                            </div>
                        </div>
                        @error('detail_persentase')
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
                <!-- End Form -->
            </div>
            <div class="modal-footer">
                <form action="{{ route('kategori.nilai.detail.destroy') }}" method="post">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="delete_slug" readonly required>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure ?')">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection

@section('script')
<script>
    $(document).ready(function() {
        const table = $('#table-data').DataTable();

        function format(value) {
            let div = document.createElement('div');
            let count = 0;
            let tbody = document.createElement('tbody');
            for (var i = 0; i < value.length; i++) {
                var tr = document.createElement('tr');
                var tdDetailKategori = document.createElement('td');
                var tdDetailPersentase = document.createElement('td');
                var tdAction = document.createElement('td');
                var detailKategori = document.createTextNode(value[i].detail_kategori);
                var detailPersentase = document.createTextNode(value[i].detail_persentase);

                var editButton = document.createElement('button');
                editButton.type = 'button';
                editButton.className = 'btn btn-info btn-sm btn-edit-detail mr-1';
                editButton.setAttribute('data-value', value[i].uuid);
                editButton.setAttribute('data-toggle', 'modal');
                editButton.setAttribute('data-target', '#editModal');

                var icon = document.createElement('i');
                icon.className = 'fas fa-pencil-alt mr-1';

                var buttonText = document.createTextNode('Edit');
                editButton.appendChild(icon);
                editButton.appendChild(buttonText);
                tdDetailKategori.appendChild(detailKategori);
                tdDetailPersentase.appendChild(detailPersentase);
                tdAction.appendChild(editButton);
                tr.appendChild(tdDetailKategori);
                tr.appendChild(tdDetailPersentase);
                tr.appendChild(tdAction);
                tbody.appendChild(tr);
                count += parseInt(value[i].detail_persentase);
            }

            if (count != 100) {
                var alert = document.createElement('div');
                alert.className = 'alert alert-default-danger';
                alert.textContent = 'Persentase detail tidak 100 persen';
                div.appendChild(alert);
            }

            let tableDetail = document.createElement('table');
            tableDetail.id = 'table-detail';
            tableDetail.className = 'table table-striped table-bordered';
            let thead = document.createElement('thead');
            let headerRow = document.createElement('tr');
            let headers = ['Detail Kategori Penilaian', 'Detail Persentase', ''];

            headers.forEach(headerText => {
                let th = document.createElement('th');
                th.textContent = headerText;
                headerRow.appendChild(th);
            });

            thead.appendChild(headerRow);
            tableDetail.appendChild(thead);
            tableDetail.appendChild(tbody);
            div.appendChild(tableDetail);

            return div;
        }

        $('.btn-add-detail').on('click', function() {
            let slug = $(this).data('value');
            $('input[name="slug"]').val(slug);
        });

        $(document).on('click', '.btn-edit-detail', function() {
            let slug = $(this).data('value');
            $('input[name="edit_slug"]').val(slug);
            $('input[name="delete_slug"]').val(slug);
            $('#overlay').show();
            $.ajax({
                url: '/kategori-nilai/detail/relation/' + slug,
                method: 'GET',
                success: function(data) {
                    if (data.data) {
                        $('input[name="edit_detail_kategori"]').val(data.data.detail_kategori);
                        $('input[name="edit_detail_persentase"]').val(data.data
                            .detail_persentase);
                        if (data.is_linked == true) {
                            $('.modal-footer').hide();
                        } else {
                            $('.modal-footer').show();
                        }
                    }
                },
                complete: function() {
                    $('#overlay').hide();
                }
            });
        });

        $('#editModal').on('hidden.bs.modal', function() {
            $('input[name="edit_detail_kategori"]').val('');
            $('input[name="edit_detail_persentase"]').val('');
            $('input[name="edit_slug"]').val('');
            $('input[name="delete_slug"]').val('');
        });

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