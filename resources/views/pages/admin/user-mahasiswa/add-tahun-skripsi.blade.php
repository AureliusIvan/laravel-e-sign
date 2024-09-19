@extends('layouts.main')

@section('content')
@include('partials.admin-nav')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="font-weight-bold">Tahun Skripsi Mahasiswa</h1>
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
                Tahun Skripsi Mahasiswa
              </h3>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Form -->
              <form id="formTambah" method="post" action="{{ route('mahasiswa.store.tahun.skripsi') }}">
                @csrf
                <!-- Angkatan -->
                <div class="form-group">
                  <label for="inputAngkatan">Angkatan Mahasiswa</label>
                  <div class="input-group">
                    <select name="angkatan" id="inputAngkatan" class="form-control" required>
                      <option value="">Pilih ...</option>
                      @foreach ($angkatan as $row)
                      <option value="{{ $row }}" {{ old('angkatan') == $row ? 'selected' : '' }}>
                        {{ $row }}
                      </option>
                      @endforeach
                    </select>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="far fa-calendar"></span>
                      </div>
                    </div>
                  </div>
                  @error('tahun')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>

                <!-- Tahun Ajaran -->
                <div class="form-group">
                  <label for="inputTahunAjaran">Tahun Ajaran</label>
                  <div class="input-group">
                    <select id="inputTahunAjaran" name="tahun_ajaran" class="form-control 
                    @error('tahun')
                        is-invalid
                    @enderror" required>
                      <option value="">Pilih ...</option>
                      @foreach ($tahun as $row)
                      <option value="{{ $row->uuid }}">{{ $row->tahun . ' Semester ' . ucfirst($row->semester) }}
                      </option>
                      @endforeach
                    </select>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="far fa-calendar"></span>
                      </div>
                    </div>
                  </div>
                  @error('tahun_ajaran')
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
