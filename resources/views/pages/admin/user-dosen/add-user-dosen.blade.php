@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Tambah Dosen</h1>
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
                                    Tambah Dosen
                                </h3>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form id="formTambah" method="post" action="{{ route('dosen.store') }}">
                                @csrf
                                <!-- NID -->
                                <div class="form-group">
                                    <label for="inputNID">NID</label>
                                    <div class="input-group">
                                        <input type="text" id="inputNID" name="nid" class="form-control @error('nid')
                        is-invalid
                    @enderror" value="{{ old('nid') }}" placeholder="Enter NID" required>
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
                                        <input type="text" name="nama" id="inputNama" value="{{ old('nama') }}" class="form-control @error('nama')
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
                                        <input type="text" name="gelar" id="inputGelar" value="{{ old('gelar') }}"
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

                                <!-- Prodi -->
                                <div class="form-group">
                                    <label for="inputProgramStudi">Program Studi</label>
                                    <div class="input-group">
                                        <select name="program_studi" id="inputProgramStudi" class="form-control"
                                            required>
                                            <option value="">Pilih ...</option>
                                            @foreach ($program as $p)
                                            <option value="{{ $p->uuid }}"
                                                {{ old('program_studi') == $p->uuid ? 'selected' : '' }}>
                                                {{ $p->program_studi }}
                                            </option>
                                            @endforeach
                                        </select>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-user-graduate"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('program_studi')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label for="inputEmail">Email</label>
                                    <div class="input-group">
                                        <input type="email" name="email" id="inputEmail" value="{{ old('email') }}"
                                            class="form-control @error('email')
                        is-invalid
                    @enderror" placeholder="Enter Email" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-envelope"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('email')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control @error('password')
                                is-invalid
                            @enderror" placeholder="Minimal 8 character" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-lock"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('password')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control @error('password_confirmation')
                                is-invalid
                            @enderror" placeholder="Confirm Password" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-lock"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('password_confirmation')
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
                                        <a href="{{ route('dosen') }}" class="btn btn-secondary">Cancel</a>
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
