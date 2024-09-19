@extends('layouts.main')

@section('content')
@include('partials.mahasiswa-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Edit Bimbingan</h1>
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
                                Edit Bimbingan
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form action="{{ route('bimbingan.update', $data->uuid) }}" method="post">
                                @csrf
                                @method('PUT')

                                <!-- Dosen -->
                                <div class="form-group">
                                    <label for="dosen">Dosen <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <select name="edit_dosen" id="dosen" class="form-control @error('dosen')
                                        is-invalid
                                    @enderror" required>
                                            <option value="">Pilih ...</option>
                                            <option value="{{ $pembimbing->slug_pembimbing1 }}"
                                                {{ $data->slug_pembimbing === $pembimbing->slug_pembimbing1 ? 'selected' : '' }}>
                                                {{ $pembimbing->pembimbing1 }}
                                            </option>
                                            @if ($pembimbing->pembimbing2 != "")
                                            <option value="{{ $pembimbing->slug_pembimbing2 }}"
                                                {{ $data->slug_pembimbing === $pembimbing->slug_pembimbing2 ? 'selected' : '' }}>
                                                {{ $pembimbing->pembimbing2 }}
                                            </option>
                                            @endif
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

                                <!-- Tanggal Bimbingan -->
                                <div class="form-group">
                                    <label for="tanggal_bimbingan">Tanggal Bimbingan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="date" name="edit_tanggal_bimbingan" id="tanggal_bimbingan" class="form-control @error('tanggal_bimbingan')
                                            is-invalid
                                        @enderror" value="{{ old('tanggal_bimbingan', $data->tanggal_bimbingan) }}"
                                            required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-calendar"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('tanggal_bimbingan')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Isi Bimbingan -->
                                <div class="form-group">
                                    <label for="isi_bimbingan">Isi Bimbingan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <textarea name="edit_isi_bimbingan" id="isi_bimbingan" class="form-control @error('isi_bimbingan')
                                        is-invalid
                                    @enderror" rows="4" placeholder="Isi Bimbingan"
                                            required>{{ old('isi_bimbingan', $data->isi_bimbingan) }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-pen"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('isi_bimbingan')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Saran -->
                                <div class="form-group">
                                    <label for="saran">
                                        Saran <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <textarea name="edit_saran" id="saran" class="form-control @error('saran')
                                        is-invalid
                                    @enderror" rows="4" placeholder="Saran"
                                            required>{{ old('saran', $data->saran) }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-pen"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('saran')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Submit Button -->
                                <div class="d-flex flex-row">
                                    <div class="mr-2">
                                        <input type="submit" class="btn btn-primary" name="action" value="Update" />
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