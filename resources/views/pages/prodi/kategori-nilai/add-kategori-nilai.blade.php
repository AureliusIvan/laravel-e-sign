@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Tambah Kategori Nilai</h1>
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
                                Tambah Kategori Nilai
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form id="formTambah" method="post" action="{{ route('kategori.nilai.store') }}">
                                @csrf
                                <input type="hidden" name="program_studi" value="{{ $prodi }}">

                                <!-- Kategori Nilai -->
                                <div class="form-group">
                                    <label for="kategori">Kategori Nilai <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="kategori" class="form-control @error('kategori')
                                            is-invalid
                                        @enderror" placeholder="Enter Kategori Nilai" value="{{ old('kategori') }}"
                                            required>
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
                                        @enderror" placeholder="Enter Persentase" value="{{ old('persentase') }}"
                                            required>
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
                                            <option value="pembimbing">Pembimbing</option>
                                            <option value="penguji">Penguji</option>
                                            <option value="ketua_sidang">Ketua Sidang</option>
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
                                        <input type="submit" class="btn btn-secondary" name="action"
                                            value="Save and Create Another" />
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