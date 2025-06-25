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
                    <h1 class="font-weight-bold">Pengumpulan Laporan Skripsi</h1>
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
                        @if (!$result)
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

                            @if ($pembimbingKedua)

                            @elseif ($pembimbingPertama)
                            @if ($jumlahBimbinganPembimbingPertama >= $minimumBimbinganPertama)
                            <div class="d-flex align-items-center justify-content-center">
                                <button type="button" class="btn btn-primary btn-open-modal mb-2"
                                    value="{{ $d->uuid }}">
                                    Upload File
                                </button>
                            </div>
                            @else
                            <div class="alert-default-danger px-3 py-2 font-weight-bold rounded" role="alert">
                                Jumlah bimbingan belum terpenuhi. Minimal {{ $minimumBimbinganPertama }}.
                            </div>
                            @endif
                            @endif

                            @endif
                        </div>
                    </div>
                    @else
                    @foreach ($result as $row)
                    @if ($row->laporanAkhirForm->id == $d->id)
                    <div class="card-body">
                        <p>
                            {{ $d->keterangan }}
                        </p>
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th style="width: 25%;">Status</th>
                                    <td class="p-1">
                                        @if ($row->status === 1)
                                        <div class="alert-default-primary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Submitted
                                        </div>
                                        @elseif ($row->status === 0)
                                        <div class="alert-default-danger px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Not Submitted
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 25%;">Waktu Pengumpulan</th>
                                    <td>{{ date('l, d F Y H:i:s', strtotime($row->updated_at)) }} </td>
                                </tr>
                                <tr>
                                    <th style="width: 25%;">Dibuka</th>
                                    <td>{{ date('l, d F Y H:i:s', strtotime($d->dibuka)) }} </td>
                                </tr>
                                <tr>
                                    <th style="width: 25%;">Ditutup</th>
                                    <td>{{ date('l, d F Y H:i:s', strtotime($d->ditutup)) }} </td>
                                </tr>
                                <tr>
                                    <th style="width: 25%;">Approval Pembimbing 1</th>
                                    <td class="p-1">
                                        @if ($row->status_approval_pembimbing1 === 1)
                                        <div class="alert-default-primary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Diterima
                                        </div>
                                        @elseif ($row->status_approval_pembimbing1 === 0)
                                        <div class="alert-default-danger px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Ditolak
                                        </div>
                                        @else
                                        <div class="alert-default-secondary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Pending
                                        </div>
                                        @endif
                                    </td>
                                </tr>

                                @if ($row->pembimbing2 !== null)
                                <tr>
                                    <th style="width: 25%;">Approval Pembimbing 2</th>
                                    <td class="p-1">
                                        @if ($row->status_approval_pembimbing2 === 1)
                                        <div class="alert-default-primary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Diterima
                                        </div>
                                        @elseif ($row->status_approval_pembimbing2 === 0)
                                        <div class="alert-default-danger px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Ditolak
                                        </div>
                                        @else
                                        <div class="alert-default-secondary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Pending
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endif

                                <tr>
                                    <th style="width: 25%;">Approval Kaprodi</th>
                                    <td class="p-1">
                                        @if ($row->status_approval_kaprodi === 1)
                                        <div class="alert-default-primary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Diterima
                                        </div>
                                        @elseif ($row->status_approval_kaprodi === 0)
                                        <div class="alert-default-danger px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Ditolak
                                        </div>
                                        @else
                                        <div class="alert-default-secondary px-3 py-2 font-weight-bold rounded"
                                            role="alert">
                                            Pending
                                        </div>
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th style="width: 25%;">Catatan Revisi Pembimbing 1</th>
                                    <td>
                                        @if ($row->status_approval_pembimbing1 === 0)
                                        {{ $row->note_pembimbing1 }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>

                                @if ($row->pembimbing2 !== null)
                                <tr>
                                    <th style="width: 25%;">Catatan Revisi Pembimbing 2</th>
                                    <td>
                                        @if ($row->status_approval_pembimbing2 === 0)
                                        {{ $row->note_pembimbing2 }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                @endif

                                <tr>
                                    <th style="width: 25%;">Catatan Revisi Kaprodi</th>
                                    <td>
                                        @if ($row->status_approval_kaprodi === 0)
                                        {{ $row->note_kaprodi }}
                                        @else
                                        -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 25%;">File Revisi</th>
                                    <td>
                                        @if ($row->status_approval_pembimbing1 === 0)
                                        <a href="{{ route('laporan.skripsi.pengumpulan.file-pembimbing1', $row->uuid) }}"
                                            target="_blank">
                                            {{ $row->file_pembimbing1 }}
                                        </a>
                                        @elseif ($row->status_approval_pembimbing2 === 0)
                                        <a href="{{ route('laporan.skripsi.pengumpulan.file-pembimbing2', $row->uuid) }}"
                                            target="_blank">
                                            {{ $row->file_pembimbing2 }}
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 25%;">File</th>
                                    <td>
                                        <div class="row">
                                            <div class="col-sm-11">
                                                <a href="{{ route('laporan.skripsi.pengumpulan.getfile', $row->uuid) }}"
                                                    target="_blank">
                                                    {{ $row->file_laporan }}
                                                </a>
                                            </div>
                                            <div class="col-sm-1 d-flex justify-content-end">
                                                @if ($d->ditutup > $now)
                                                @if ($row->status_approval_pembimbing1 === 0 ||
                                                $row->status_approval_pembimbing2 ===
                                                0 || $row->status_approval_kaprodi === 0)
                                                @if ($row->status === 1)
                                                <form action="{{ route('laporan.skripsi.pengumpulan.destroy') }}"
                                                    method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="slug" value="{{ $row->uuid }}">
                                                    <button onclick="return confirm('Are you sure?')" type="submit"
                                                        class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                @endif
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="width: 25%;">File Final</th>
                                    <td>
                                        @if ($row->status_akhir === 1)
                                        @if ($row->file_kaprodi !== null)
                                        <a href="{{ route('laporan.skripsi.pengumpulan.file-kaprodi') }}"
                                            target="_blank">
                                            {{ $row->file_kaprodi }}
                                        </a>
                                        @endif
                                        @endif
                                    </td>
                                </tr>

                            </table>
                        </div>
                        @if ($d->ditutup > $now && $d->dibuka < $now) @if ($row->status !== 1)
                            <div class="d-flex align-items-center justify-content-center">

                                <button type="button" class="btn btn-primary btn-reupload-modal mb-2"
                                    value="{{ $d->uuid }}" data-value="{{ $row->uuid }}">
                                    Upload File
                                </button>

                            </div>
                            @endif
                            @endif
                    </div>
                    @else
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
                        <div class="d-flex align-items-center justify-content-center">
                            @if ($d->ditutup > $now && $d->dibuka < $now) <button type="button"
                                class="btn btn-primary btn-open-modal mb-2" value="{{ $d->uuid }}">
                                Upload File
                                </button>
                                @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @endif

                </div>
                @endforeach
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
                <h4 class="modal-title">Pengumpulan Laporan Skripsi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formPengumpulan" action="{{ route('laporan.skripsi.pengumpulan.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" class="id_form" name="id_form">
                    <input type="hidden" name="reupload" value="0">

                    <!-- Proposal yang sudah di update dan diterima -->
                    <div class="form-group">
                        <label for="revisi_proposal">Pilih File Revisi Proposal yang Sudah Dikumpulkan <span
                                class="text-danger">*</span></label>
                        <select class="form-control @error('revisi_proposal')
                                            is-invalid
                                        @enderror" id="revisi_proposal" name="revisi_proposal" style="width: 100%;"
                            required>\

                        </select>
                    </div>
                    <!-- End -->

                    <div class="form-group">
                        <label for="exampleInputFile">
                            Silahkan upload file laporan skripsi anda (max 30MB) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile" name="file"
                                    accept="application/pdf" required>
                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                            </div>
                        </div>
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

<!-- Reupload Modal -->
<div class="modal fade" id="addModal2">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pengumpulan Laporan Skripsi</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formPengumpulan" action="{{ route('laporan.skripsi.pengumpulan.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" class="id_form" name="id_form">
                    <input type="hidden" name="reupload" value="1">
                    <input type="hidden" class="laporan_id" name="laporan_id">

                    <!-- Proposal yang sudah di update dan diterima -->
                    <div class="form-group">
                        <label for="revisi_proposal">Pilih File Revisi Proposal yang Sudah Dikumpulkan <span
                                class="text-danger">*</span></label>
                        <select class="form-control @error('revisi_proposal')
                                            is-invalid
                                        @enderror" id="revisi_proposal" name="revisi_proposal" style="width: 100%;"
                            required>\

                        </select>
                    </div>
                    <!-- End -->

                    <div class="form-group">
                        <label for="exampleInputFile">
                            Silahkan upload file laporan skripsi anda (max 30MB) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile" name="file"
                                    accept="application/pdf" required>
                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                            </div>
                        </div>
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
@endsection

@section('script')
<script>
    $(function() {
        bsCustomFileInput.init();
    });

    $(document).ready(function() {
        function fetchData() {
            $('#overlay').show();
            $.ajax({
                url: '/laporan-skripsi/fetch-revisi-proposal',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.data) {
                        $('select[name="revisi_proposal"]').empty();
                        let options = '<option value="">Pilih ...</option>';
                        $.each(data.data, function(index, item) {
                            options +=
                                `<option value="${item.uuid}">${item.judul_revisi_proposal}</option>`;
                        });
                        let select = $('select[name="revisi_proposal"]');
                        select.append(options);
                    }
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
        $(document).on('click', '.btn-open-modal', function(event) {
            $('#addModal').modal('show');
            let id = $(this).attr('value');
            $('.id_form').attr('value', id);
            fetchData();
        });

        $(document).on('click', '.btn-reupload-modal', function(event) {
            $('#addModal2').modal('show');
            let id = $(this).attr('value');
            let slug = $(this).data('value');
            $('.id_form').attr('value', id);
            $('.laporan_id').attr('value', slug);
            fet
            chData();
        });
    })
</script>





@endsection