@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Edit Dosen</h1>
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
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Edit Dosen
                                </h3>
                                <div class="card-tools">
                                    <form action="{{ route('dosen.destroy') }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="slug" value="{{ $data->uuid }}" readonly required>
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure ?')">
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form id="formEdit" method="post" action="{{ route('dosen.update', $data->uuid) }}">
                                @csrf
                                @method('PUT')
                                <!-- NID -->
                                <div class="form-group">
                                    <label for="inputNID">NID</label>
                                    <div class="input-group">
                                        <input type="text" id="inputNID" name="nid" class="form-control"
                                            value="{{ $data->nid }}" required readonly>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('nid')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Nama -->
                                <div class="form-group">
                                    <label for="inputNama">Nama</label>
                                    <div class="input-group">
                                        <input type="text" name="nama" id="inputNama" value="{{ $data->nama }}" class="form-control @error('tahun')
                        is-invalid
                    @enderror" placeholder="Enter Nama" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('nama')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Gelar -->
                                <div class="form-group">
                                    <label for="inputGelar">Gelar (Contoh: S.Kom., M.Kom.)</label>
                                    <div class="input-group">
                                        <input type="text" name="gelar" id="inputGelar" value="{{ $data->gelar }}"
                                            class="form-control @error('gelar')
                                                        is-invalid
                                                    @enderror" placeholder="Enter Gelar" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('gelar')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Program Studi -->
                                <div class="form-group">
                                    <label for="inputProgramStudi">Program Studi</label>
                                    <div class="input-group">
                                        <select name="program_studi" id="inputProgramStudi" class="form-control"
                                            required>
                                            <option value="">Pilih ...</option>
                                            @foreach ($program as $p)
                                            <option value="{{ $p->uuid }}"
                                                {{ $data->program_studi_id === $p->id ? 'selected' : '' }}>
                                                {{ $p->program_studi }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('program_studi')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <input type="hidden" name="roleBefore" value="{{ $data->role }}">

                                <!-- Status Dosen Baru -->
                                <div class="form-group">
                                    <label for="inputRole">Status Dosen</label>
                                    <div class="input-group">
                                        <select name="role" id="inputRole" class="form-control" required>
                                            <option value="">Pilih ...</option>
                                            <option value="dosen" {{ $data->role === 'dosen' ? 'selected' : '' }}>Dosen
                                            </option>
                                            <option value="kaprodi" {{ $data->role === 'kaprodi' ? 'selected' : '' }}>
                                                Kaprodi</option>
                                            <option value="sekprodi" {{ $data->role === 'sekprodi' ? 'selected' : '' }}>
                                                Sekprodi</option>
                                        </select>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('role')
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
