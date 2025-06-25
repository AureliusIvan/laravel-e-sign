@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold">{{ $title }}</h1>
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

                    <!-- Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daftar Proposal Skripsi Yang Sudah Disubmit</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIM</th>
                                            <th>Nama Mahasiswa</th>
                                            <th>Program Studi</th>
                                            <th>Judul Proposal</th>
                                            <th>Form</th>
                                            <th>Tahun Ajaran</th>
                                            <th>Tanggal Submit</th>
                                            <th>Status</th>
                                            <th>Penilai 1</th>
                                            <th>Status P1</th>
                                            <th>Penilai 2</th>
                                            <th>Status P2</th>
                                            <th>Penilai 3</th>
                                            <th>Status P3</th>
                                            <th>Status Akhir</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($proposals as $index => $proposal)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $proposal->nim }}</td>
                                                <td>{{ $proposal->mahasiswa_nama }}</td>
                                                <td>{{ $proposal->program_studi }}</td>
                                                <td>
                                                    <div class="text-wrap" style="max-width: 300px;">
                                                        {{ $proposal->judul_proposal }}
                                                    </div>
                                                </td>
                                                <td>{{ $proposal->judul_form }}</td>
                                                <td>{{ $proposal->tahun }}/{{ $proposal->semester }}</td>
                                                <td>{{ \Carbon\Carbon::parse($proposal->created_at)->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    @if($proposal->status == 1)
                                                        <span class="badge badge-success">Aktif</span>
                                                    @else
                                                        <span class="badge badge-secondary">Tidak Aktif</span>
                                                    @endif
                                                </td>
                                                <td>{{ $proposal->penilai1_nama ?? '-' }}</td>
                                                <td>
                                                    @if($proposal->status_approval_penilai1 == 1)
                                                        <span class="badge badge-success">Disetujui</span>
                                                        <br><small>{{ $proposal->tanggal_approval_penilai1 ? \Carbon\Carbon::parse($proposal->tanggal_approval_penilai1)->format('d/m/Y') : '' }}</small>
                                                    @elseif($proposal->status_approval_penilai1 == 0)
                                                        <span class="badge badge-danger">Ditolak</span>
                                                        <br><small>{{ $proposal->tanggal_approval_penilai1 ? \Carbon\Carbon::parse($proposal->tanggal_approval_penilai1)->format('d/m/Y') : '' }}</small>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $proposal->penilai2_nama ?? '-' }}</td>
                                                <td>
                                                    @if($proposal->status_approval_penilai2 == 1)
                                                        <span class="badge badge-success">Disetujui</span>
                                                        <br><small>{{ $proposal->tanggal_approval_penilai2 ? \Carbon\Carbon::parse($proposal->tanggal_approval_penilai2)->format('d/m/Y') : '' }}</small>
                                                    @elseif($proposal->status_approval_penilai2 == 0)
                                                        <span class="badge badge-danger">Ditolak</span>
                                                        <br><small>{{ $proposal->tanggal_approval_penilai2 ? \Carbon\Carbon::parse($proposal->tanggal_approval_penilai2)->format('d/m/Y') : '' }}</small>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $proposal->penilai3_nama ?? '-' }}</td>
                                                <td>
                                                    @if($proposal->status_approval_penilai3 == 1)
                                                        <span class="badge badge-success">Disetujui</span>
                                                        <br><small>{{ $proposal->tanggal_approval_penilai3 ? \Carbon\Carbon::parse($proposal->tanggal_approval_penilai3)->format('d/m/Y') : '' }}</small>
                                                    @elseif($proposal->status_approval_penilai3 == 0)
                                                        <span class="badge badge-danger">Ditolak</span>
                                                        <br><small>{{ $proposal->tanggal_approval_penilai3 ? \Carbon\Carbon::parse($proposal->tanggal_approval_penilai3)->format('d/m/Y') : '' }}</small>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($proposal->status_akhir == 1)
                                                        <span class="badge badge-success">Disetujui</span>
                                                    @elseif($proposal->status_akhir == 0)
                                                        <span class="badge badge-danger">Ditolak</span>
                                                    @else
                                                        <span class="badge badge-warning">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
@endsection

@section('script')
<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"],
            "pageLength": 25,
            "order": [[ 7, "desc" ]], // Order by submit date descending
            "columnDefs": [
                { "orderable": false, "targets": [4] } // Disable ordering for title column
            ]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection 