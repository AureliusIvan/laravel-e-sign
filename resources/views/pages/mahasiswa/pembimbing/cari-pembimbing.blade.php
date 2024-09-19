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
@include('partials.mahasiswa-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Cari Pembimbing</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Container -->
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-12" id="accordion">
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
                    @php
                    $now = date('Y-m-d H:i:s');
                    @endphp
                    @foreach ($data as $d)
                    <div class="card card-outline card-info scroll">

                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    {{ $d->judul_form }}
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
                            <p>
                                {{ $d->keterangan }}
                            </p>
                            <div class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th style="width: 25%;">Dibuka</th>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->dibuka)) }} </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 25%;">Ditutup</th>
                                        <td>{{ date('l, d F Y H:i:s', strtotime($d->ditutup)) }} </td>
                                    </tr>
                                </table>
                            </div>
                            @if ($d->ditutup > $now)
                            @if ($status === false)
                            @if ($total < 1) <div class="d-flex align-items-center justify-content-center">
                                <button type="button" id="{{ $d->uuid }}" class="btn btn-primary btn-open-modal mb-2"
                                    data-toggle="modal" data-target="#addModal">
                                    Ajukan Permintaan Dosen Pembimbing
                                </button>
                        </div>
                        @endif
                        @endif
                        @endif
                        <table id="table-data" class="table table-bordered table-striped"
                            style="font-size: 0.9em; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">#</th>
                                    <th>Dosen Pembimbing</th>
                                    <th>Tanggal Permintaan</th>
                                    <th>Research Interest</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->nama }}</td>
                                    <td>
                                        {{ date('l, d F Y H:i:s', strtotime($row->created_at)) }}
                                    </td>
                                    <td>{{ $row->topik_penelitian }}</td>
                                    <td>
                                        @if ($row->status === 2)
                                        <span class="text-secondary font-weight-bold">
                                            Pending
                                        </span>
                                        @elseif ($row->status === 1)
                                        <span class="text-primary font-weight-bold">
                                            Diterima
                                        </span>
                                        @elseif ($row->status === 0)
                                        <span class="text-danger font-weight-bold">
                                            Ditolak
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-primary btn-sm btn-edit-data mr-1"
                                            href="{{ route('caripembimbing.show', $row->uuid) }}">
                                            <i class="fas fa-folder mr-1"></i>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
            @endforeach
            <!-- End of Card -->

            <!-- Modal -->
            <div class="modal fade" id="addModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">Ajukan Permintaan Dosen Pembimbing</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="formPengumpulan" action="{{ route('caripembimbing.store') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" class="id_form" name="id_form">


                                <!-- Pembimbing ke -->
                                <div class="form-group">
                                    <label for="pembimbing">Permintaan untuk pembimbing ke</label>
                                    <div class="input-group">
                                        @if ($total === 0)
                                        <input type="text" id="pembimbing" name="pembimbing" class="form-control"
                                            value="Pembimbing Pertama" readonly required>
                                        @elseif ($total === 1)
                                        <input type="text" id="pembimbing" name="pembimbing" class="form-control"
                                            value="Pembimbing Kedua" readonly required>
                                        @endif
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Research Interest -->
                                <div class="form-group">
                                    <label for="research_interest">Silahkan pilih research interest anda <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select name="research_interest" id="research_interest" class="form-control @error('research_interest')
                                is-invalid
                            @enderror" required>
                                            <option value="">Pilih ...</option>
                                            @foreach ($research as $r)
                                            <option value="{{ $r->uuid }}">{{ $r->topik_penelitian }}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-book"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('research_interest')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <!-- End -->

                                <!-- Dosen -->
                                <div class="form-group">
                                    <label for="dosen">Dosen <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select name="dosen" id="dosen" class="form-control @error('dosen')
                                is-invalid
                            @enderror" required>
                                            <option value="">Pilih ...</option>
                                        </select>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('dosen')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                                <!-- End -->

                                <!-- Select tipe file -->
                                <!-- Yes or No -->
                                <div class="form-group">
                                    <label for="upload_file">
                                        Upload File
                                    </label>
                                    <div class="icheck-primary">
                                        <input type="radio" id="radioPrimary1" name="upload_file" value="is_rti"
                                            required>
                                        <label for="radioPrimary1">
                                            Upload File Proposal RTI
                                        </label>
                                    </div>
                                    <div class="icheck-primary">
                                        <input type="radio" id="radioPrimary2" name="upload_file" value="is_uploaded">
                                        <label for="radioPrimary2">
                                            Upload Contoh Proposal
                                        </label>
                                    </div>
                                    <div class="icheck-danger">
                                        <input type="radio" id="radioDanger1" name="upload_file" value="no">
                                        <label for="radioDanger1">
                                            Tidak mengupload file
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="exampleInputFile">
                                        Silahkan upload file proposal RTI atau proposal yang sudah anda
                                        buat
                                    </label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="exampleInputFile"
                                                name="file" accept="application/pdf" required>
                                            <label class="custom-file-label" for="exampleInputFile">Choose
                                                file</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pesan untuk dosen -->
                                <div class="form-group">
                                    <label for="note_mahasiswa">Pesan untuk dosen <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <textarea name="note_mahasiswa" id="note_mahasiswa" class="form-control @error('note_mahasiswa')
                                            is-invalid
                                        @enderror"
                                            placeholder="Selamat pagi/siang/sore bapak/ibu, perkenalkan nama saya ..."
                                            rows="6" required>{{ old('note_mahasiswa') }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-newspaper"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('note_mahasiswa')
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
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->

        </div>
</div>
</div>
<!-- End of Container -->
</section>
</div>

@endsection

@section('script')
<script>
    $(function() {
        bsCustomFileInput.init();
    });

    $(document).ready(function() {
        $('#table-data').DataTable({
            ordering: false,
        });

        $(document).on('click', '.btn-open-modal', function(event) {
            var id = $(this).attr('id');
            $('.id_form').attr('value', id);
        });

        $(document).on('change', '#research_interest', function(e) {
            var slug = $(this).val();
            let select = $('#dosen');
            select.empty();
            select.append('<option value="">Pilih ... </option>');
            $('#overlay').show();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "/caripembimbing/dosen/" + slug,
                method: 'post',
                dataType: 'json',
                success: function(data) {
                    if (data.dosen.length > 0) {
                        $.each(data.dosen, function(index, option) {
                            select.append('<option value="' + option.uuid + '">' +
                                option.nama + '</option>');
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Tidak dapat menemukan dosen');
                },
                complete: function() {
                    $('#overlay').hide();
                }
            })
        });

        $('input[name="upload_file"]').change(function() {
            if ($('input[name="upload_file"]:checked').val() === 'no') {
                $('input[name="file"]').prop('disabled', true);
            } else {
                $('input[name="file"]').prop('disabled', false);
            }
        });

        $('#formPengumpulan').on('submit', function() {
            $('#overlay').show();
        });
    })
</script>

@endsection