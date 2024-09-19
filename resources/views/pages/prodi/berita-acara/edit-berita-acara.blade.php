@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Berita Acara</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <!-- Container -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">

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
                                Berita Acara
                            </h3>
                            <div class="card-tools">
                                @if ($isLinked === false)
                                <form action="{{ route('berita.destroy') }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="slug" value="{{ $data->uuid }}" readonly required>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure ?')">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form id="formTambah" method="post" action="{{ route('berita.update', $data->uuid) }}">
                                @csrf
                                @method('PUT')
                                <!-- Tanggal Awal -->
                                <div class="form-group">
                                    <label for="tanggal_awal">Tanggal Dimulai</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control @error('tanggal_awal')
                                            is-invalid
                                        @enderror" value="{{ old('tanggal_awal', $data->tanggal_awal) }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('tanggal_awal')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Tanggal Akhir -->
                                <div class="form-group">
                                    <label for="tanggal_akhir">Tanggal Berakhir</label>
                                    <div class="input-group">
                                        <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control @error('tanggal_akhir')
                                            is-invalid
                                        @enderror" value="{{ old('tanggal_akhir', $data->tanggal_akhir) }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('tanggal_akhir')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Berita Acara -->
                                <div class="form-group">
                                    <label for="isi_berita">Berita Acara</label>
                                    <div class="input-group">
                                        <textarea name="isi_berita" id="isi_berita" class="form-control" placeholder="Enter Berita Acara"
                                            rows="4" required>{{ old('isi_berita', $data->isi_berita) }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-newspaper"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('isi_berita')
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