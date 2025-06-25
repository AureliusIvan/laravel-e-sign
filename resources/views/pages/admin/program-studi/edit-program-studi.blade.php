@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="font-weight-bold">Program Studi</h1>
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
                  Program Studi
                </h3>
                <div class="card-tools">
                  @if ($isLinked === false)
                  <form action="{{ route('programstudi.destroy') }}" method="post">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="slug" value="{{ $program->uuid }}" readonly required>
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure ?')">
                      <i class="fas fa-trash"></i>
                      Delete
                    </button>
                  </form>
                  @endif
                </div>
              </div>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Form -->
              <form id="formEdit" method="post" action="{{ route('programstudi.update', $program->uuid) }}">
                @csrf
                @method('PUT')
                <!-- Program Studi -->
                <div class="form-group">
                  <label for="editProgramStudi">Program Studi</label>
                  <div class="input-group">
                    <input type="text" id="editProgramStudi" class="form-control @error('program_studi')
                                            is-invalid
                                        @enderror" name="program_studi" placeholder="Enter Program Studi"
                      value="{{ $program->program_studi }}" required>
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
