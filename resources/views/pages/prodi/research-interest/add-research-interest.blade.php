@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Tambah Topik Penelitian</h1>
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
                                Tambah Topik Penelitian
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form id="formTambah" method="post" action="{{ route('research.store') }}">
                                @csrf
                                <input type="hidden" name="program_studi" value="{{ $prodi }}">

                                <!-- Topik Penelitian -->
                                <div class="form-group">
                                    <label for="topik_penelitian">Topik Penelitian <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="topik_penelitian" id="topik_penelitian" class="form-control @error('topik_penelitian')
                                            is-invalid
                                        @enderror" placeholder="Enter Topik Penelitian"
                                            value="{{ old('topik_penelitian') }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-book"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('topik_penelitian')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Kode Penelitian -->
                                <div class="form-group">
                                    <label for="kode_penelitian">Kode Penelitian <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="kode_penelitian" id="kode_penelitian" class="form-control @error('kode_penelitian')
                                            is-invalid
                                        @enderror" placeholder="Enter Kode Penelitian"
                                            value="{{ old('kode_penelitian') }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-book"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('kode_penelitian')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Deskripsi -->
                                <div class="form-group">
                                    <label for="deskripsi">Deskripsi</label>
                                    <div class="input-group">
                                        <textarea name="deskripsi" id="deskripsi" class="form-control @error('deskripsi')
                                            is-invalid
                                        @enderror" placeholder="Enter Deskripsi"
                                            rows="4">{{ old('deskripsi') }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-book"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('deskripsi')
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
                                        <a href="{{ route('research') }}" class="btn btn-secondary">Cancel</a>
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