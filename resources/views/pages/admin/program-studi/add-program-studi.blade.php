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
                            <h3 class="card-title">
                                Program Studi
                            </h3>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form id="formTambah" method="post" action="{{ route('programstudi.store') }}">
                                @csrf
                                <!-- Program Studi -->
                                <div class="form-group">
                                    <label for="inputProgramStudi">Program Studi</label>
                                    <div class="input-group">
                                        <input type="text" id="inputProgramStudi" class="form-control @error('program_studi')
                                            is-invalid
                                        @enderror" name="program_studi" placeholder="Enter Program Studi"
                                            value="{{ old('program_studi') }}" required>
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
                                        <input type="submit" class="btn btn-primary" name="action" value="Save" />
                                    </div>
                                    <div class="mr-2">
                                        <input type="submit" class="btn btn-secondary" name="action" value="Save and Create Another" />
                                    </div>
                                    <div class="mr-2">
                                        <a href="{{ route('programstudi') }}" class="btn btn-secondary">Cancel</a>
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