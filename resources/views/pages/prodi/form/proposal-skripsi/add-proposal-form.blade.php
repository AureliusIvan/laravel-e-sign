@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Buat Form Skripsi</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Container -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">

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
                            <h3 class="card-title">
                                Tambah Form Skripsi
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form id="formTambah" method="post" action="{{ route('proposal.skripsi.form.store') }}">
                                @csrf
                                <input type="hidden" name="tahun_ajaran" value="{{ $tahun }}">
                                <input type="hidden" name="program_studi" value="{{ $prodi }}">

                                <!-- Judul Form -->
                                <div class="form-group">
                                    <label for="judul_form">Judul Form</label>
                                    <div class="input-group">
                                        <textarea name="judul_form" id="judul_form" class="form-control @error('judul_form')
                                                                is-invalid
                                                            @enderror" placeholder="Enter Judul Form" rows="2"
                                            required>{{ old('judul_form') }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-file"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('judul_form')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Keterangan -->
                                <div class="form-group">
                                    <label for="keterangan">Keterangan</label>
                                    <div class="input-group">
                                        <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan')
                                                                is-invalid
                                                            @enderror" placeholder="Enter Keterangan" rows="4"
                                            required>{{ old('keterangan') }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-file"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('keterangan')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Tanggal Awal -->
                                <div class="form-group">
                                    <label for="dibuka">Tanggal Form Dibuka</label>
                                    <div class="input-group">
                                        <input type="datetime-local" name="dibuka" id="dibuka" class="form-control @error('dibuka')
                                            is-invalid
                                        @enderror" step="1" value="{{ old('dibuka') }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('dibuka')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Tanggal Akhir -->
                                <div class="form-group">
                                    <label for="ditutup">Tanggal Form Ditutup</label>
                                    <div class="input-group">
                                        <input type="datetime-local" name="ditutup" id="ditutup" class="form-control @error('ditutup')
                                            is-invalid
                                        @enderror" step="1" value="{{ old('ditutup') }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('ditutup')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Deadline Penilai -->
                                <div class="form-group">
                                    <label for="deadline_penilaian">Deadline Penilaian Proposal oleh Penilai</label>
                                    <div class="input-group">
                                        <input type="datetime-local" name="deadline_penilaian" id="deadline_penilaian" class="form-control @error('deadline_penilaian')
                                            is-invalid
                                        @enderror" step="1" value="{{ old('deadline_penilaian') }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('deadline_penilaian')
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
                                    <div class="mr-2">
                                        <input type="submit" class="btn btn-secondary" name="action" value="Save and Create Another" />
                                    </div>
                                    <div class="mr-2">
                                        <a href="{{ route('proposal.skripsi.form') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>

                            </form>
                            <!-- End Form -->
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


@endsection