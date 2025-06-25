@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Edit Area Penelitian</h1>
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
                                Edit Area Penelitian
                            </h3>
                            <div class="card-tools">
                                @if ($isLinked === false)
                                <form action="{{ route('areapenelitian.destroy') }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="slug" value="{{ $data->uuid }}" readonly required>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure ?')">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <!-- Form -->
                            <form method="post" action="{{ route('areapenelitian.update', $data->uuid) }}">
                                @csrf
                                @method('PUT')

                                <!-- Research List -->
                                <div class="form-group">
                                    <label for="topik_penelitian">Pilih Topik Penelitian <span class="text-danger">*</span></label>
                                    <select class="form-control @error('topik_penelitian')
                                            is-invalid
                                        @enderror" id="topik_penelitian" name="topik_penelitian" style="width: 100%;"
                                        required>
                                        <option value="">Pilih ...</option>
                                        @foreach ($research as $row)
                                        <option value="{{ $row->uuid }}"
                                            {{ old('topik_penelitian') == $row->uuid || $data->researchList->uuid == $row->uuid ? 'selected' : '' }}>
                                            {{ $row->topik_penelitian . ' ' }}({{ $row->kode_penelitian }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- End -->

                                <!-- Kode Penelitian -->
                                <div class="form-group">
                                    <label for="kode_area_penelitian">Kode Area Penelitian <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="kode_area_penelitian" id="kode_area_penelitian" class="form-control @error('kode_area_penelitian')
                                            is-invalid
                                        @enderror" placeholder="Enter Kode Area Penelitian"
                                            value="{{ old('kode_area_penelitian', $data->kode_area_penelitian) }}" required>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-book"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('kode_area_penelitian')
                                    <div class="mt-1 text-danger">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <!-- Keterangan -->
                                <div class="form-group">
                                    <label for="keterangan">Keterangan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan')
                                            is-invalid
                                        @enderror" placeholder="Enter Keterangan" rows="4"
                                            required>{{ old('keterangan', $data->keterangan) }}</textarea>
                                        <div class="input-group-append">
                                            <div class="input-group-text">
                                                <span class="fas fa-book"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('keterangan')
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
                                        <a href="{{ route('areapenelitian') }}" class="btn btn-secondary">Cancel</a>
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