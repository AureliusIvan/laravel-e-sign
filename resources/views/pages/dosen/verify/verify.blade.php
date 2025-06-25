@extends('layouts.main')

@section('content')
    @if (auth()->user()->role === 'dosen')
        @include('partials.dosen-nav')
    @elseif (auth()->user()->role === 'kaprodi' || auth()->user()->role === 'sekprodi')
        @include('partials.prodi-
nav')
    @endif
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1 class="font-weight-bold">
                            Verify Dokumen
                            </h1>
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
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;
                                </button>
                                <h5><i class="icon fas fa-check"></i>Success!</h5>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;
                                </button>
                                <h5><i class="icon fas fa-ban"></i>Failed!</h5>
                                {{ session('error') }}
                            </div>
                        @endif

                        <!-- Card -->
                        <div class="card card-outline card-info scroll p-4">
                            <!-- Body -->

                            <form id="addForm" action="{{ route('verify.upload') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                <!-- File Koreksi -->
                                <div class="form-group">
                                    <label for="exampleInputFile">
                                        Please upload your document
                                    </label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="exampleInputFile"
                                                   name="file"
                                                   value="{{ old('file') }}"
                                                   accept="application/pdf" required>
                                            <label
                                                id="inputFileLabel"
                                                class="custom-file-label" for="exampleInputFile">
                                                Choose file
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <!-- End -->

                                <!-- Submit Button -->
                                <div class="d-flex flex-row">
                                    <div class="mr-2">
                                        <input type="submit" class="btn btn-primary" name="action" value="Verify Documents"/>
                                    </div>
                                </div>

                                @if ($status === 'success')
                                    <div class="alert alert-success alert-dismissible mt-3">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                            &times;
                                        </button>
                                        <h5><i class="icon fas fa-check"></i>Success!</h5>
                                        Dokumen telah diverifikasi
                                    </div>

                                    <div>
                                        <table>
                                            <tr>
                                                <td>Hash</td>
                                                <td> :</td>
                                                <td>{{ $hash_value }}</td>
                                            </tr>

                                            <tr>
                                                <td>Tipe Laporan</td>
                                                <td> :</td>
                                                <td>{{ $Tipe_Laporan }}</td>
                                            </tr>

                                            <tr>
                                                <td>Judul Laporan</td>
                                                <td> :</td>
                                                <td>{{ $Judul_Laporan }}</td>
                                            </tr>

                                            <tr>
                                                <td>Prodi</td>
                                                <td> :</td>
                                                <td>{{ $Prodi }}</td>
                                            </tr>

                                            <tr>
                                                <td>Tahun</td>
                                                <td> :</td>
                                                <td>{{ $Tahun }}</td>
                                            </tr>

                                            <tr>
                                                <td>Nama Mahasiswa</td>
                                                <td> :</td>
                                                <td>{{ $Nama_Mahasiswa }}</td>
                                            </tr>

                                            <tr>
                                                <td>NIM</td>
                                                <td> :</td>
                                                <td>{{ $NIM }}</td>
                                            </tr>

                                            <tr>
                                                <td>Dosen Pembimbing 1</td>
                                                <td> :</td>
                                                <td>{{ $Dosen_Pembimbing_1__Nama }}</td>
                                            </tr>

                                            <tr>
                                                <td>NIK/NIDN Dosen Pembimbing 1</td>
                                                <td> :</td>
                                                <td>{{ $Dosen_Pembimbing_1__NIDN }}</td>
                                            </tr>

                                            {{--                                            <tr>--}}
                                            {{--                                                <td>Dosen Pembimbing 2</td>--}}
                                            {{--                                                <td> : </td>--}}
                                            {{--                                                <td>{{ $Dosen_Pembimbing_2__Nama }}</td>--}}
                                            {{--                                            </tr>--}}

                                            {{--                                            <tr>--}}
                                            {{--                                                <td>NIK/NIDN Dosen Pembimbing 2</td>--}}
                                            {{--                                                <td> : </td>--}}
                                            {{--                                                <td>{{ $Dosen_Pembimbing_2__NIDN }}</td>--}}
                                            {{--                                            </tr>--}}

                                            {{--                                            <tr>--}}
                                            {{--                                                <td>Dosen Penguji</td>--}}
                                            {{--                                                <td> : </td>--}}
                                            {{--                                                <td>{{ $Dosen_Penguji }}</td>--}}
                                            {{--                                            </tr>--}}

                                            {{--                                            <tr>--}}
                                            {{--                                                <td>Dosen Ketua Sidang</td>--}}
                                            {{--                                                <td> : </td>--}}
                                            {{--                                                <td>{{ $Dosen_Ketua_Sidang }}</td>--}}
                                            {{--                                            --}}
                                            <tr>
                                                <td>KAPRODI</td>
                                                <td> :</td>
                                                <td>{{ $KAPRODI }}</td>
                                            </tr>


                                        </table>
                                    </div>

                                @elseif($status === 'failed')
                                    <div class="alert alert-danger alert-dismissible mt-3">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                            &times;
                                        </button>
                                        <h5><i class="icon fas fa-ban"></i>Failed!</h5>
                                        Dokumen gagal diverifikasi
                                        <p>
                                            ({{ $error }})
                                        </p>
                                    </div>
                                @elseif($status === 'warning')
                                    <div class="alert alert-warning alert-dismissible mt-3">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                            &times;
                                        </button>
                                        <h5><i class="icon fas fa-exclamation-triangle"></i>Warning!</h5>
                                        Dokumen berhasil di upload, namun terdapat kesalahan dalam dokumen
                                    </div>
                                @elseif($status === 'error')
                                    <div class="alert alert-danger alert-dismissible mt-3">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
                                            &times;
                                        </button>
                                        <h5><i class="icon fas fa-ban"></i>Failed!</h5>
                                        Verification FAILED: Document content does not match the signature
                                    </div>
                                @endif
                            </form>

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
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('exampleInputFile');
            const fileLabel = document.getElementById('inputFileLabel');

            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    let fileName = this.files[0].name;
                    if (fileName.length > 20) {
                        fileName = fileName.substring(0, 20) + '...';
                    }
                    fileLabel.textContent = fileName; // Update label with the file name
                } else {
                    fileLabel.textContent = 'Choose file'; // Reset label if no file is selected
                }
            });

        });
    </script>
@endsection


