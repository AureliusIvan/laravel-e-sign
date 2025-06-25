@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="font-weight-bold">Tambah Mahasiswa</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('mahasiswa') }}">Mahasiswa</a></li>
            <li class="breadcrumb-item active">Tambah</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <!-- Container -->
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-12">

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

          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Tambah Mahasiswa</h3>
            </div>

            <form action="{{ route('mahasiswa.store') }}" method="post">
              @csrf
              <div class="card-body">
                
                <!-- Email -->
                <div class="form-group">
                  <label for="inputEmail">Email</label>
                  <div class="input-group">
                    <input type="email" name="email" id="inputEmail" value="{{ old('email') }}" class="form-control @error('email')
                        is-invalid
                    @enderror" placeholder="Masukkan Email" required>
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
                  <label for="inputPassword">Password</label>
                  <div class="input-group">
                    <input type="password" name="password" id="inputPassword" class="form-control @error('password')
                        is-invalid
                    @enderror" placeholder="Masukkan Password" required>
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
                  <label for="inputPasswordConfirmation">Konfirmasi Password</label>
                  <div class="input-group">
                    <input type="password" name="password_confirmation" id="inputPasswordConfirmation" class="form-control @error('password_confirmation')
                        is-invalid
                    @enderror" placeholder="Konfirmasi Password" required>
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

                <hr>

                <!-- NIM -->
                <div class="form-group">
                  <label for="inputNim">NIM</label>
                  <div class="input-group">
                    <input type="text" name="nim" id="inputNim" value="{{ old('nim') }}" class="form-control @error('nim')
                        is-invalid
                    @enderror" placeholder="Masukkan NIM" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-id-card"></span>
                      </div>
                    </div>
                  </div>
                  @error('nim')
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
                    @enderror" placeholder="Masukkan Nama" required>
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

                <!-- Program Studi -->
                <div class="form-group">
                  <label for="inputProgramStudi">Program Studi</label>
                  <div class="input-group">
                    <select name="program_studi" id="inputProgramStudi" class="form-control @error('program_studi')
                        is-invalid
                    @enderror" required>
                      <option value="">Pilih Program Studi</option>
                      @foreach ($program as $p)
                      <option value="{{ $p->uuid }}" {{ old('program_studi') == $p->uuid ? 'selected' : '' }}>
                        {{ $p->program_studi }}
                      </option>
                      @endforeach
                    </select>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-graduation-cap"></span>
                      </div>
                    </div>
                  </div>
                  @error('program_studi')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

                <!-- Angkatan -->
                <div class="form-group">
                  <label for="inputAngkatan">Angkatan</label>
                  <div class="input-group">
                    <input type="number" name="angkatan" id="inputAngkatan" value="{{ old('angkatan') }}" class="form-control @error('angkatan')
                        is-invalid
                    @enderror" placeholder="Masukkan Angkatan" min="2000" max="{{ date('Y') + 5 }}" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-calendar"></span>
                      </div>
                    </div>
                  </div>
                  @error('angkatan')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

              </div>

              <!-- Submit Button -->
              <div class="card-footer">
                <div class="d-flex justify-content-between">
                  <a href="{{ route('mahasiswa') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Kembali
                  </a>
                  <div>
                    <button type="submit" name="action" value="Save and Create Another" class="btn btn-primary mr-2">
                      <i class="fas fa-save mr-1"></i>
                      Simpan & Tambah Lagi
                    </button>
                    <button type="submit" name="action" value="Save" class="btn btn-success">
                      <i class="fas fa-save mr-1"></i>
                      Simpan
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </section>
</div>
@endsection 