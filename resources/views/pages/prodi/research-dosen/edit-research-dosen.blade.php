@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">Edit Topik Penelitian</h1>
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
                                Edit Topik Penelitian
                            </h3>
                            <div class="card-tools">
                                @if ($isLinked === false)
                                <form action="{{ route('research.dosen.destroy') }}" method="post">
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
                            <form id="formEdit" method="post" action="{{ route('research.dosen.update', $data->uuid) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="prodi" value="{{ $prodi }}">

                                <!-- Prodi -->
                                <div class="form-group">
                                    <label for="dosen">Dosen <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" id="dosen" class="form-control" value="{{ $data->nama }}" readonly required>
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

                                <!-- Research Before -->
                                @foreach ($data->researchDosen as $d)
                                <input type="hidden" name="research_before[]" value="{{ $d->uuid }}" readonly required>
                                @endforeach

                                <!-- Research List -->
                                <div class="form-group">
                                    <label for="topik_penelitian">Topik Penelitian <span class="text-danger">*</span></label>
                                    <select class="select2" id="topik_penelitian" name="topik_penelitian[]" multiple="multiple"
                                        data-placeholder="Pilih Topik Penelitian" style="width: 100%;" required>
                                        @foreach ($research as $row)
                                        @php
                                        foreach ($data->researchDosen as $d) {
                                        $selected = false;
                                        if($row->uuid === $d->researchList->uuid) {
                                        $selected = true;
                                        break;
                                        }
                                        }
                                        @endphp
                                        @if ($selected)
                                        <option value="{{ $row->uuid }}" selected>
                                            {{ $row->topik_penelitian . ' ' }}({{ $row->kode_penelitian }})
                                        </option>
                                        @else
                                        <option value="{{ $row->uuid }}">
                                            {{ $row->topik_penelitian . ' ' }}({{ $row->kode_penelitian }})
                                        </option>
                                        @endif
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