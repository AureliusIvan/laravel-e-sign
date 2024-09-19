@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Edit Kategori Nilai</h1>
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
                                Edit Kategori Nilai
                            </h3>
                            <div class="card-tools">
                                @if ($isLinked === false)
                                <form action="{{ route('kategori.nilai.destroy') }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="slug" value="{{ $data->uuid }}" readonly required>
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure ?')">
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
                            <form id="formTambah" method="post"
                                action="{{ route('kategori.nilai.update', $data->uuid) }}">
                                @csrf
                                @method('PUT')

                                <!-- Kategori Nilai -->
                                <div class="form-group">
                                    <label for="kategori">Kategori Nilai <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="kategori" class="form-control @error('kategori')
                                            is-invalid
                                        @enderror" placeholder="Enter Kategori Nilai"
                                            value="{{ old('kategori', $data->kategori) }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-pen"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('kategori')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Persentase -->
                                <div class="form-group">
                                    <label for="persentase">Persentase <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="persentase" class="form-control @error('persentase')
                                            is-invalid
                                        @enderror" placeholder="Enter Persentase"
                                            value="{{ old('persentase', $data->persentase) }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-percent"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('persentase')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Kategori untuk -->
                                <div class="form-group">
                                    <label for="select-user">Kategori untuk</label>
                                    <div class="input-group mb-3">
                                        <select class="form-control @error('user')
                                            is-invalid
                                        @enderror" name="user" required>
                                            <option value="">Pilih ...</option>
                                            <option value="pembimbing"
                                                {{ $data->user === "pembimbing" ? 'selected' : '' }}>Pembimbing</option>
                                            <option value="penguji" {{ $data->user === "penguji" ? 'selected' : '' }}>
                                                Penguji</option>
                                            <option value="ketua_sidang"
                                                {{ $data->user === "ketua_sidang" ? 'selected' : '' }}>Ketua Sidang
                                            </option>
                                        </select>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('user')
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
                                        <a href="{{ route('kategori.nilai') }}" class="btn btn-secondary">Cancel</a>
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