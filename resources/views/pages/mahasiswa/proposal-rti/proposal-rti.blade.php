@extends('layouts.main')

@section('style')
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
    <div class="overlay" id="overlay">
        <i class="fas fa-sync fa-spin"></i>
    </div>
    @php
        $now = date('Y-m-d H:i:s');
    @endphp
    @include('partials.mahasiswa-nav')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1 class="font-weight-bold">Pengumpulan Proposal RTI</h1>
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
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">&times;</button>
                                <h5><i class="icon fas fa-check"></i>Success!</h5>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">&times;</button>
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

                        @if ($pembimbing === null)
                            <div class="alert alert-default-danger">
                                Anda belum memiliki pembimbing.
                            </div>
                        @else
                            <!-- Card -->
                            @foreach ($data as $d)
                                @php
                                    $row = null;
                                    foreach ($result as $res) {
                                        if ($res->proposalRtiForm->id == $d->id) {
                                            $row = $res;
                                            break;
                                        }
                                    }
                                @endphp

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

                                    @if ($row)
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
                                                        <th style="width: 25%;">Judul Proposal</th>
                                                        <td>
                                                            {{ $row->judul_proposal }}
                                                        </td>
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

                                                    <tr>
                                                        <th style="width: 25%;">Catatan Pembimbing 1</th>
                                                        <td>
                                                            @if ($row->note_pembimbing1 != null)
                                                                {{ $row->note_pembimbing1 }}
                                                            @else
                                                                -
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
                                                        <tr>
                                                            <th style="width: 25%;">Catatan Pembimbing 2</th>
                                                            <td>
                                                                @if ($row->note_pembimbing2 != null)
                                                                    {{ $row->note_pembimbing2 }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endif

                                                    <tr>
                                                        <th style="width: 25%;">File</th>
                                                        <td>
                                                            <div class="row">
                                                                <div class="col-sm-11">
                                                                    <a href="{{ route('proposal.rti.file', $row->uuid) }}"
                                                                        target="_blank">
                                                                        {{ $row->file_proposal }}
                                                                    </a>
                                                                </div>
                                                                <div class="col-sm-1 d-flex justify-content-end">
                                                                    @if ($d->ditutup > $now)
                                                                        @if ($row->status_akhir === 0)
                                                                            @if ($row->status === 1)
                                                                                <form
                                                                                    action="{{ route('proposal.rti.destroy') }}"
                                                                                    method="post">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <input type="hidden" name="slug"
                                                                                        value="{{ $row->uuid }}">
                                                                                    <button type="submit"
                                                                                        class="btn btn-sm btn-danger btn-delete">
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
                                                </table>
                                            </div>
                                            @if ($row->status === 0)
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <button type="button" class="btn btn-primary btn-reupload-modal mb-2"
                                                        value="{{ $d->uuid }}" data-value="{{ $row->uuid }}"
                                                        data-form="{{ $row->proposalRtiForm->uuid }}">
                                                        Upload File
                                                    </button>
                                                </div>
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
                                                @if ($d->ditutup > $now && $d->dibuka < $now)
                                                    <button type="button" class="btn btn-primary btn-open-modal mb-2"
                                                        value="{{ $d->uuid }}">
                                                        Upload File
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif

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
                    <h4 class="modal-title">Pengumpulan Proposal RTI</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formPengumpulan" action="{{ route('proposal.rti.store') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" class="id_form" name="id_form">
                        <input type="hidden" name="reupload" value="0">

                        <div class="form-group">
                            <label for="judul_proposal">Judul Proposal RTI <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <textarea pattern="[^<>]+" class="form-control" id="judul_proposal" name="judul_proposal"
                                    placeholder="Judul Proposal RTI" aria-describedby="basic-addon2" required>{{ old('judul_proposal') }}</textarea>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-pen"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputFile">
                                Silahkan upload file Skripsi anda (max 30MB) <span class="text-danger">*</span>
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
                    <h4 class="modal-title">Pengumpulan Proposal RTI</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formPengumpulan2" action="{{ route('proposal.rti.store') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" class="id_form" name="id_form">
                        <input type="hidden" name="reupload" value="1">
                        <input type="hidden" class="proposal_id" name="proposal_id">

                        <div class="form-group">
                            <label for="judul_proposal">Judul Proposal RTI <span class="text-danger">*</span></label>
                            <div class="input-group mb-3">
                                <textarea pattern="[^<>]+" class="form-control" id="judul_proposal" name="judul_proposal"
                                    placeholder="Judul Proposal RTI" aria-describedby="basic-addon2" required>{{ old('judul_proposal') }}</textarea>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-pen"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputFile">
                                Silahkan upload file Skripsi anda (max 30MB) <span class="text-danger">*</span>
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
            $('.select2').select2()

            $('.select2').select2({
                theme: 'bootstrap4'
            })
        });

        $(document).ready(function() {
            $(document).on('click', '.btn-open-modal', function(event) {
                $('#addModal').modal('show');
                let id = $(this).attr('value');
                $('.id_form').attr('value', id);
            });

            $(document).on('click', '.btn-reupload-modal', function(event) {
                $('#addModal2').modal('show');
                let id = $(this).attr('value');
                let slug = $(this).data('value');
                let form = $(this).data('form');
                $('.id_form').attr('value', id);
                $('.proposal_id').attr('value', slug);
            });

            $('#formPengumpulan').on('submit', function(e) {
                $('#overlay').show();
                let text = $('textarea[name="judul_proposal"]').val().trim();
                let words = text.split(/\s+/);
                if (words.length < 4) {
                    alert('Silahkan masukan judul minimal 4 kata.');
                    e.preventDefault();
                    $('#overlay').hide();
                }
            });

            $('#formPengumpulan2').on('submit', function(e) {
                $('#overlay').show();
                let text = $('#formPengumpulan2 textarea[name="judul_proposal"]').val().trim();
                let words = text.split(/\s+/);
                if (words.length < 4) {
                    alert('Silahkan masukan judul minimal 4 kata.');
                    e.preventDefault();
                    $('#overlay').hide();
                }
            });

            $(document).on('click', '.btn-delete', function(e) {
                if (confirm('Are you sure?')) {
                    $('#overlay').show();
                } else {
                    e.preventDefault();
                }
            });

        })
    </script>
@endsection
