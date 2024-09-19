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
              <form id="formTambah" method="post" action="{{ route('research.dosen.store') }}">
                @csrf
                <input type="hidden" name="program_studi" value="{{ $prodi }}">

                <!-- Dosen -->
                <div class="form-group">
                  <label for="dosen">Dosen <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <select name="dosen" id="dosen" class="form-control @error('dosen')
                                            is-invalid
                                        @enderror" required>
                      <option value="">Pilih ...</option>
                      @foreach ($dosen as $d)
                      @php
                      $exists = false;
                      foreach($available as $row) {
                      if ($d->uuid === $row->uuid) {
                      $exists = true;
                      break;
                      }
                      }
                      @endphp
                      @if (!$exists)
                      <option value="{{ $d->uuid }}">
                        {{ $d->nama }}
                      </option>
                      @endif
                      @endforeach
                    </select>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-user-graduate"></span>
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

                <!-- Research List -->
                <div class="form-group">
                  <label for="topik_penelitian">Topik Penelitian <span class="text-danger">*</span></label>
                  <select class="select2 @error('topik_penelitian')
                                            is-invalid
                                        @enderror" id="topik_penelitian" name="topik_penelitian[]" multiple="multiple"
                    data-placeholder="Pilih Topik Penelitian" style="width: 100%;" required>
                    @foreach ($research as $row)
                    <option value="{{ $row->uuid }}">
                      {{ $row->topik_penelitian . ' ' }}({{ $row->kode_penelitian }})
                    </option>
                    @endforeach
                  </select>
                </div>
                <!-- End -->

                <!-- Submit Button -->
                <div class="d-flex flex-row">
                  <div class="mr-2">
                    <input type="submit" class="btn btn-primary" name="action" value="Save" />
                  </div>
                  <div class="mr-2">
                    <input type="submit" class="btn btn-secondary" name="action" value="Save and Create Another" />
                  </div>
                  <div class="mr-2">
                    <a href="{{ route('research.dosen') }}" class="btn btn-secondary">Cancel</a>
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
<script>
$(function() {
  $('.select2').select2()

  $('.select2').select2({
    theme: 'bootstrap4'
  })
});
</script>

@endsection
