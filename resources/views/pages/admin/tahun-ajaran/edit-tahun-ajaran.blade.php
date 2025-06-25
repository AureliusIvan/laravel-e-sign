@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="font-weight-bold">Tahun Ajaran</h1>
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
                  Tahun Ajaran
                </h3>
                <div class="card-tools">
                  <form action="{{ route('tahunajaran.destroy') }}" method="post">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="slug" value="{{ $data->uuid }}" readonly required>
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure ?')">
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
              <form id="formEdit" method="post" action="{{ route('tahunajaran.update', $data->uuid) }}">
                @csrf
                @method('PUT')
                <!-- Tahun -->
                <div class="form-group">
                  <label for="inputTahun">Tahun</label>
                  <div class="input-group">
                    <input type="number" id="inputTahun" class="form-control @error('tahun')
                                            is-invalid
                                        @enderror" name="tahun" placeholder="Enter Tahun" value="{{ $data->tahun }}"
                      min="2000" required>
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

                <!-- Semester -->
                <div class="form-group">
                  <label for="inputSemester">Semester</label>
                  <div class="input-group">
                    <select id="inputSemester" name="semester" class="form-control @error('tahun')
                                            is-invalid
                                        @enderror" required>
                      <option value="">Pilih ...</option>
                      <option value="genap" {{ $data->semester === 'genap' ? 'selected' : '' }}>Genap</option>
                      <option value="ganjil" {{ $data->semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                    </select>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="far fa-calendar"></span>
                      </div>
                    </div>
                  </div>
                  @error('semester')
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
