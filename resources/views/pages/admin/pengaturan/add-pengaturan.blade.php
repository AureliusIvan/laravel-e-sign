@extends('layouts.main')

@section('content')
@include('partials.admin-nav')

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-6">
          <h1 class="font-weight-bold">Pengaturan</h1>
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
              <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                  Pengaturan
                </h3>
              </div>
            </div>

            <!-- Body -->
            <div class="card-body">
              <!-- Form -->
              <form id="formTambah" method="post" action="{{ route('pengaturan.store') }}">
                @csrf
                <!-- Prodi -->
                <div class="form-group">
                  <label for="inputProgramStudi">Program Studi</label>
                  <div class="input-group">
                    <select name="program_studi" id="inputProgramStudi" class="form-control" required>
                      <option value="">Pilih ...</option>
                      @foreach ($program as $p)
                      @php
                      $exists = false;
                      foreach($available as $a) {
                      if ($p->id === $a->program_studi_id) {
                      $exists = true;
                      break;
                      }
                      }
                      @endphp
                      @if (!$exists)
                      <option value="{{ $p->uuid }}" {{ old('program_studi') == $p->uuid ? 'selected' : '' }}>
                        {{ $p->program_studi }}
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
                  @error('program_studi')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Kuota Pembimbing Pertama -->
                <div class="form-group">
                  <label for="kuota_pembimbing_pertama">Kuota Pembimbing Pertama</label>
                  <div class="input-group">
                    <input type="number" id="kuota_pembimbing_pertama" name="kuota_pembimbing_pertama" class="form-control @error('kuota_pembimbing_pertama')
                        is-invalid
                    @enderror" value="10" min="0" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-users"></span>
                      </div>
                    </div>
                  </div>
                  @error('kuota_pembimbing_pertama')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Kuota Pembimbing Kedua -->
                <div class="form-group">
                  <label for="kuota_pembimbing_kedua">Kuota Pembimbing Kedua</label>
                  <div class="input-group">
                    <input type="number" id="kuota_pembimbing_kedua" name="kuota_pembimbing_kedua" class="form-control @error('kuota_pembimbing_kedua')
                        is-invalid
                    @enderror" value="10" min="0" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-users"></span>
                      </div>
                    </div>
                  </div>
                  @error('kuota_pembimbing_kedua')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Minimal Jumlah Bimbingan -->
                <div class="form-group">
                  <label for="minimum_jumlah_bimbingan">Minimal Jumlah Bimbingan Pembimbing Pertama</label>
                  <div class="input-group">
                    <input type="number" id="minimum_jumlah_bimbingan" name="minimum_jumlah_bimbingan" class="form-control @error('minimum_jumlah_bimbingan')
                        is-invalid
                    @enderror" value="8" placeholder="Enter Minimal Jumlah Bimbingan" min="0" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-users"></span>
                      </div>
                    </div>
                  </div>
                  @error('minimum_jumlah_bimbingan')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Minimal Jumlah Bimbingan Pembimbing Kedua -->
                <div class="form-group">
                  <label for="minimum_jumlah_bimbingan_kedua">Minimal Jumlah Bimbingan Pembimbing Kedua</label>
                  <div class="input-group">
                    <input type="number" id="minimum_jumlah_bimbingan_kedua" name="minimum_jumlah_bimbingan_kedua"
                      class="form-control @error('minimum_jumlah_bimbingan_kedua')
                        is-invalid
                    @enderror" value="6" placeholder="Enter Minimal Jumlah Bimbingan" min="0" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-users"></span>
                      </div>
                    </div>
                  </div>
                  @error('minimum_jumlah_bimbingan_kedua')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Tahun Proposal RTI Habis -->
                <h6 class="font-weight-bold">Tahun Skripsi dengan Proposal RTI Habis</h6>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tahun_rti_tersedia_sampai">Tahun</label>
                      <div class="input-group">
                        <input type="number" id="tahun_rti_tersedia_sampai" name="tahun_rti_tersedia_sampai" class="form-control @error('tahun_rti_tersedia_sampai')
                                  is-invalid
                              @enderror" placeholder="Enter Tahun Habis" min="2000" value="{{ $tahun->tahun }}"
                          required>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-calendar"></span>
                          </div>
                        </div>
                      </div>
                      @error('tahun_rti_tersedia_sampai')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>

                  <div class="col-sm-6">
                    <!-- Semester -->
                    <div class="form-group">
                      <label for="semester_rti_tersedia_sampai">Semester</label>
                      <div class="input-group">
                        <select id="semester_rti_tersedia_sampai" name="semester_rti_tersedia_sampai" class="form-control 
                        @error('semester_rti_tersedia_sampai')
                            is-invalid
                        @enderror" required>
                          <option value="">Pilih ...</option>
                          <option value="genap" {{ ($tahun->semester === 'genap') ? 'selected' : '' }}>Genap</option>
                          <option value="ganjil" {{ ($tahun->semester === 'ganjil') ? 'selected' : '' }}>Ganjil</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="far fa-calendar"></span>
                          </div>
                        </div>
                      </div>
                      @error('semester_rti_tersedia_sampai')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Tahun Proposal Baru Habis -->
                <h6 class="font-weight-bold">Tahun Skripsi Baru Habis (Default: 1,5 tahun)</h6>
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="tahun_proposal_tersedia_sampai">Tahun</label>
                      <div class="input-group">
                        <input type="number" id="tahun_proposal_tersedia_sampai" name="tahun_proposal_tersedia_sampai"
                          class="form-control @error('tahun_proposal_tersedia_sampai')
                                  is-invalid
                              @enderror" placeholder="Enter Tahun Habis" min="2000" value="{{ $tahun->tahun + 1 }}"
                          required>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-calendar"></span>
                          </div>
                        </div>
                      </div>
                      @error('tahun_proposal_tersedia_sampai')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>

                  <!-- Semester -->
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label for="semester_proposal_tersedia_sampai">Semester</label>
                      <div class="input-group">
                        <select id="semester_proposal_tersedia_sampai" name="semester_proposal_tersedia_sampai" class="form-control 
                        @error('semester_proposal_tersedia_sampai')
                            is-invalid
                        @enderror" required>
                          <option value="">Pilih ...</option>
                          <option value="genap" {{ ($tahun->semester === 'genap') ? 'selected' : '' }}>Genap</option>
                          <option value="ganjil" {{ ($tahun->semester === 'ganjil') ? 'selected' : '' }}>Ganjil</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="far fa-calendar"></span>
                          </div>
                        </div>
                      </div>
                      @error('semester_proposal_tersedia_sampai')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->


                <!-- Penamaan Skripsi -->
                <!-- Yes or No -->
                <div class="form-group">
                  <label for="penamaan_proposal">
                    Penamaan Skripsi (*File yang diupload mahasiswa akan dinamai secara otomatis)
                  </label>
                  <div class="icheck-primary">
                    <input type="radio" id="radioPrimary3" name="penamaan_proposal" value="1" checked required>
                    <label for="radioPrimary3">
                      Ya
                    </label>
                  </div>
                  <div class="icheck-danger">
                    <input type="radio" id="radioDanger3" name="penamaan_proposal" value="0">
                    <label for="radioDanger3">
                      Tidak
                    </label>
                  </div>
                </div>

                <h6 class="font-weight-bold">
                  Format Penamaan Skripsi
                </h6>
                <div class="row">
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_proposal_part1">Part 1</label>
                      <div class="input-group">
                        <select id="penamaan_proposal_part1" name="penamaan_proposal_part1" class="form-control 
                        @error('penamaan_proposal_part1')
                            is-invalid
                        @enderror" required>

                          <option value="nim" selected>NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul">Skripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_proposal_part1')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_proposal_part2">Part 2</label>
                      <div class="input-group">
                        <select id="penamaan_proposal_part2" name="penamaan_proposal_part2" class="form-control 
                        @error('penamaan_proposal_part2')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama" selected>Nama</option>
                          <option value="judul">ProposalSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_proposal_part2')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_proposal_part3">Part 3</label>
                      <div class="input-group">
                        <select id="penamaan_proposal_part3" name="penamaan_proposal_part3" class="form-control 
                        @error('penamaan_proposal_part3')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul" selected>ProposalSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_proposal_part3')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="d-flex flex-row">
                  <div>
                    <p>Contoh : &nbsp;</p>
                  </div>
                  <div>
                    <p id="contoh_penamaan_proposal">
                      NIM_Nama_ProposalSkripsi
                    </p>
                  </div>
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Penamaan Revisi Skripsi -->
                <!-- Yes or No -->
                <div class="form-group">
                  <label for="penamaan_revisi_proposal">
                    Penamaan Revisi Skripsi (*File yang diupload mahasiswa akan dinamai secara otomatis)
                  </label>
                  <div class="icheck-primary">
                    <input type="radio" id="radioPrimary4" name="penamaan_revisi_proposal" value="1" checked required>
                    <label for="radioPrimary4">
                      Ya
                    </label>
                  </div>
                  <div class="icheck-danger">
                    <input type="radio" id="radioDanger4" name="penamaan_revisi_proposal" value="0">
                    <label for="radioDanger4">
                      Tidak
                    </label>
                  </div>
                </div>

                <h6 class="font-weight-bold">
                  Format Penamaan Revisi Skripsi
                </h6>
                <div class="row">
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_revisi_proposal_part1">Part 1</label>
                      <div class="input-group">
                        <select id="penamaan_revisi_proposal_part1" name="penamaan_revisi_proposal_part1" class="form-control 
                        @error('penamaan_revisi_proposal_part1')
                            is-invalid
                        @enderror" required>

                          <option value="nim" selected>NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul">RevisiProposal</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_revisi_proposal_part1')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_revisi_proposal_part2">Part 2</label>
                      <div class="input-group">
                        <select id="penamaan_revisi_proposal_part2" name="penamaan_revisi_proposal_part2" class="form-control 
                        @error('penamaan_revisi_proposal_part2')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama" selected>Nama</option>
                          <option value="judul">RevisiProposal</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_revisi_proposal_part2')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_revisi_proposal_part3">Part 3</label>
                      <div class="input-group">
                        <select id="penamaan_revisi_proposal_part3" name="penamaan_revisi_proposal_part3" class="form-control 
                        @error('penamaan_revisi_proposal_part3')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul" selected>RevisiProposal</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_revisi_proposal_part3')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="d-flex flex-row">
                  <div>
                    <p>Contoh : &nbsp;</p>
                  </div>
                  <div>
                    <p id="contoh_penamaan_revisi_proposal">
                      NIM_Nama_RevisiProposalSkripsi
                    </p>
                  </div>
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Penamaan Laporan Skripsi -->
                <!-- Yes or No -->
                <div class="form-group">
                  <label for="penamaan_laporan">
                    Penamaan Laporan Skripsi (*File yang diupload mahasiswa akan dinamai secara otomatis)
                  </label>
                  <div class="icheck-primary">
                    <input type="radio" id="radioPrimary5" name="penamaan_laporan" value="1" checked required>
                    <label for="radioPrimary5">
                      Ya
                    </label>
                  </div>
                  <div class="icheck-danger">
                    <input type="radio" id="radioDanger5" name="penamaan_laporan" value="0">
                    <label for="radioDanger5">
                      Tidak
                    </label>
                  </div>
                </div>

                <h6 class="font-weight-bold">
                  Format Penamaan Laporan Skripsi
                </h6>
                <div class="row">
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_laporan_part1">Part 1</label>
                      <div class="input-group">
                        <select id="penamaan_laporan_part1" name="penamaan_laporan_part1" class="form-control 
                        @error('penamaan_laporan_part1')
                            is-invalid
                        @enderror" required>

                          <option value="nim" selected>NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul">LaporanSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_laporan_part1')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_laporan_part2">Part 2</label>
                      <div class="input-group">
                        <select id="penamaan_laporan_part2" name="penamaan_laporan_part2" class="form-control 
                        @error('penamaan_laporan_part2')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama" selected>Nama</option>
                          <option value="judul">LaporanSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_laporan_part2')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_laporan_part3">Part 3</label>
                      <div class="input-group">
                        <select id="penamaan_laporan_part3" name="penamaan_laporan_part3" class="form-control 
                        @error('penamaan_laporan_part3')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul" selected>LaporanSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_laporan_part3')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="d-flex flex-row">
                  <div>
                    <p>Contoh : &nbsp;</p>
                  </div>
                  <div>
                    <p id="contoh_penamaan_laporan">
                      NIM_Nama_LaporanSkripsi
                    </p>
                  </div>
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Penamaan Revisi Laporan Skripsi -->
                <!-- Yes or No -->
                <div class="form-group">
                  <label for="penamaan_revisi_laporan">
                    Penamaan Revisi Laporan Skripsi (*File yang diupload mahasiswa akan dinamai secara otomatis)
                  </label>
                  <div class="icheck-primary">
                    <input type="radio" id="radioPrimary6" name="penamaan_revisi_laporan" value="1" checked required>
                    <label for="radioPrimary6">
                      Ya
                    </label>
                  </div>
                  <div class="icheck-danger">
                    <input type="radio" id="radioDanger6" name="penamaan_revisi_laporan" value="0">
                    <label for="radioDanger6">
                      Tidak
                    </label>
                  </div>
                </div>

                <h6 class="font-weight-bold">
                  Format Penamaan Revisi Laporan Skripsi
                </h6>
                <div class="row">
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_revisi_laporan_part1">Part 1</label>
                      <div class="input-group">
                        <select id="penamaan_revisi_laporan_part1" name="penamaan_revisi_laporan_part1" class="form-control 
                        @error('penamaan_revisi_laporan_part1')
                            is-invalid
                        @enderror" required>

                          <option value="nim" selected>NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul">RevisiLaporanSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_revisi_laporan_part1')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_revisi_laporan_part2">Part 2</label>
                      <div class="input-group">
                        <select id="penamaan_revisi_laporan_part2" name="penamaan_revisi_laporan_part2" class="form-control 
                        @error('penamaan_revisi_laporan_part2')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama" selected>Nama</option>
                          <option value="judul">RevisiLaporanSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_revisi_laporan_part2')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="form-group">
                      <label for="penamaan_revisi_laporan_part3">Part 3</label>
                      <div class="input-group">
                        <select id="penamaan_revisi_laporan_part3" name="penamaan_revisi_laporan_part3" class="form-control 
                        @error('penamaan_revisi_laporan_part3')
                            is-invalid
                        @enderror" required>

                          <option value="nim">NIM</option>
                          <option value="nama">Nama</option>
                          <option value="judul" selected>RevisiLaporanSkripsi</option>
                        </select>
                        <div class="input-group-append">
                          <div class="input-group-text">
                            <span class="fas fa-file"></span>
                          </div>
                        </div>
                      </div>
                      @error('penamaan_revisi_laporan_part3')
                      <div class="mt-1 text-danger">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                </div>

                <div class="d-flex flex-row">
                  <div>
                    <p>Contoh : &nbsp;</p>
                  </div>
                  <div>
                    <p id="contoh_penamaan_revisi_laporan">
                      NIM_Nama_RevisiLaporanSkripsi
                    </p>
                  </div>
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

                <!-- Jumlah Voting Setuju untuk Skripsi dengan Satu Pembimbing -->
                <div class="form-group">
                  <label for="jumlah_setuju_proposal">Jumlah Voting Setuju untuk Skripsi
                    Mahasiswa yang Pembimbingnya Hanya Satu agar Skripsinya dapat Digunakan (*Default
                    Semua)</label>
                  <div class="input-group">
                    <input type="number" id="jumlah_setuju_proposal" name="jumlah_setuju_proposal" class="form-control @error('jumlah_setuju_proposal')
                        is-invalid
                    @enderror" value="3" placeholder="Enter Jumlah Setuju" min="0" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-users"></span>
                      </div>
                    </div>
                  </div>
                  @error('jumlah_setuju_proposal')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <!-- End -->

                <!-- Jumlah Voting Setuju untuk Sidang Skripsi dengan Satu Pembimbing -->
                <div class="form-group">
                  <label for="jumlah_setuju_sidang_satupembimbing">Jumlah Voting Setuju untuk Sidang Skripsi Mahasiswa
                    yang Pembimbingnya Hanya Satu untuk Meluluskan Mahasiswa (*Default Semua)</label>
                  <div class="input-group">
                    <input type="number" id="jumlah_setuju_sidang_satupembimbing"
                      name="jumlah_setuju_sidang_satupembimbing" class="form-control @error('jumlah_setuju_sidang_satupembimbing')
                        is-invalid
                    @enderror" value="3" placeholder="Enter Jumlah Setuju" min="0" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-users"></span>
                      </div>
                    </div>
                  </div>
                  @error('jumlah_setuju_sidang_satupembimbing')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <!-- End -->

                <!-- Jumlah Voting Setuju untuk Sidang Skripsi dengan Dua Pembimbing -->
                <div class="form-group">
                  <label for="jumlah_setuju_sidang_duapembimbing">Jumlah Voting Setuju untuk Sidang Skripsi Mahasiswa
                    yang Pembimbingnya Dua untuk Meluluskan Mahasiswa (*Default Semua)</label>
                  <div class="input-group">
                    <input type="number" id="jumlah_setuju_sidang_duapembimbing"
                      name="jumlah_setuju_sidang_duapembimbing" class="form-control @error('jumlah_setuju_sidang_duapembimbing')
                        is-invalid
                    @enderror" value="4" placeholder="Enter Jumlah Setuju" min="0" required>
                    <div class="input-group-append">
                      <div class="input-group-text">
                        <span class="fas fa-users"></span>
                      </div>
                    </div>
                  </div>
                  @error('jumlah_setuju_sidang_duapembimbing')
                  <div class="mt-1 text-danger">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
                <hr class="bg-gray-dark">
                <!-- End -->

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
<script>
$(document).ready(function() {
  $('input[name="upload_proposal_lama"]').change(function() {
    if ($('input[name="upload_proposal_lama"]:checked').val() === '1') {
      $('input[name="proposal_lama_expired"]').prop('disabled', false)
      $('input[name="tahun_proposal_lama_tersedia_sampai"]').prop('disabled', false)
      $('select[name="semester_proposal_lama_tersedia_sampai"]').prop('disabled', false)
    } else {
      $('input[name="proposal_lama_expired"]').prop('disabled', true)
      $('input[name="tahun_proposal_lama_tersedia_sampai"]').prop('disabled', true)
      $('select[name="semester_proposal_lama_tersedia_sampai"]').prop('disabled', true)
    }
  });

  $('input[name="proposal_lama_expired"]').change(function() {
    if ($('input[name="proposal_lama_expired"]:checked').val() === '1') {
      $('input[name="tahun_proposal_lama_tersedia_sampai"]').prop('disabled', false)
      $('select[name="semester_proposal_lama_tersedia_sampai"]').prop('disabled', false)
    } else {
      $('input[name="tahun_proposal_lama_tersedia_sampai"]').prop('disabled', true)
      $('select[name="semester_proposal_lama_tersedia_sampai"]').prop('disabled', true)
    }
  });

  $('input[name="penamaan_proposal"]').change(function() {
    if ($('input[name="penamaan_proposal"]:checked').val() === '1') {
      $('select[name="penamaan_proposal_part1"]').prop('disabled', false)
      $('select[name="penamaan_proposal_part2"]').prop('disabled', false)
      $('select[name="penamaan_proposal_part3"]').prop('disabled', false)
    } else {
      $('select[name="penamaan_proposal_part1"]').prop('disabled', true)
      $('select[name="penamaan_proposal_part2"]').prop('disabled', true)
      $('select[name="penamaan_proposal_part3"]').prop('disabled', true)
    }
  })

  $('select[name="penamaan_proposal_part1"], select[name="penamaan_proposal_part2"], select[name="penamaan_proposal_part3"]')
    .change(function() {
      let penamaanProposal = $('#contoh_penamaan_proposal')
      let part1 = $('select[name="penamaan_proposal_part1"]').val();
      let part2 = $('select[name="penamaan_proposal_part2"]').val();
      let part3 = $('select[name="penamaan_proposal_part3"]').val();
      if (part1 === 'nim') {
        part1 = 'NIM'
      } else if (part1 === 'nama') {
        part1 = 'Nama'
      } else {
        part1 = 'ProposalSkripsi'
      }

      if (part2 === 'nim') {
        part2 = 'NIM'
      } else if (part2 === 'nama') {
        part2 = 'Nama'
      } else {
        part2 = 'ProposalSkripsi'
      }

      if (part3 === 'nim') {
        part3 = 'NIM'
      } else if (part3 === 'nama') {
        part3 = 'Nama'
      } else {
        part3 = 'ProposalSkripsi'
      }
      penamaanProposal.text(part1 + '_' + part2 + '_' + part3);
    })

  $('input[name="penamaan_revisi_proposal"]').change(function() {
    if ($('input[name="penamaan_revisi_proposal"]:checked').val() === '1') {
      $('select[name="penamaan_revisi_proposal_part1"]').prop('disabled', false)
      $('select[name="penamaan_revisi_proposal_part2"]').prop('disabled', false)
      $('select[name="penamaan_revisi_proposal_part3"]').prop('disabled', false)
    } else {
      $('select[name="penamaan_revisi_proposal_part1"]').prop('disabled', true)
      $('select[name="penamaan_revisi_proposal_part2"]').prop('disabled', true)
      $('select[name="penamaan_revisi_proposal_part3"]').prop('disabled', true)
    }
  })

  $('select[name="penamaan_revisi_proposal_part1"], select[name="penamaan_revisi_proposal_part2"], select[name="penamaan_revisi_proposal_part3"]')
    .change(function() {
      let penamaanProposal = $('#contoh_penamaan_revisi_proposal')
      let part1 = $('select[name="penamaan_revisi_proposal_part1"]').val();
      let part2 = $('select[name="penamaan_revisi_proposal_part2"]').val();
      let part3 = $('select[name="penamaan_revisi_proposal_part3"]').val();
      if (part1 === 'nim') {
        part1 = 'NIM'
      } else if (part1 === 'nama') {
        part1 = 'Nama'
      } else {
        part1 = 'RevisiProposalSkripsi'
      }

      if (part2 === 'nim') {
        part2 = 'NIM'
      } else if (part2 === 'nama') {
        part2 = 'Nama'
      } else {
        part2 = 'RevisiProposalSkripsi'
      }

      if (part3 === 'nim') {
        part3 = 'NIM'
      } else if (part3 === 'nama') {
        part3 = 'Nama'
      } else {
        part3 = 'RevisiProposalSkripsi'
      }
      penamaanProposal.text(part1 + '_' + part2 + '_' + part3);
    })

  $('input[name="penamaan_laporan"]').change(function() {
    if ($('input[name="penamaan_laporan"]:checked').val() === '1') {
      $('select[name="penamaan_laporan_part1"]').prop('disabled', false)
      $('select[name="penamaan_laporan_part2"]').prop('disabled', false)
      $('select[name="penamaan_laporan_part3"]').prop('disabled', false)
    } else {
      $('select[name="penamaan_laporan_part1"]').prop('disabled', true)
      $('select[name="penamaan_laporan_part2"]').prop('disabled', true)
      $('select[name="penamaan_laporan_part3"]').prop('disabled', true)
    }
  })

  $('select[name="penamaan_laporan_part1"], select[name="penamaan_laporan_part2"], select[name="penamaan_laporan_part3"]')
    .change(function() {
      let penamaanProposal = $('#contoh_penamaan_laporan')
      let part1 = $('select[name="penamaan_laporan_part1"]').val();
      let part2 = $('select[name="penamaan_laporan_part2"]').val();
      let part3 = $('select[name="penamaan_laporan_part3"]').val();
      if (part1 === 'nim') {
        part1 = 'NIM'
      } else if (part1 === 'nama') {
        part1 = 'Nama'
      } else {
        part1 = 'LaporanSkripsi'
      }

      if (part2 === 'nim') {
        part2 = 'NIM'
      } else if (part2 === 'nama') {
        part2 = 'Nama'
      } else {
        part2 = 'LaporanSkripsi'
      }

      if (part3 === 'nim') {
        part3 = 'NIM'
      } else if (part3 === 'nama') {
        part3 = 'Nama'
      } else {
        part3 = 'LaporanSkripsi'
      }
      penamaanProposal.text(part1 + '_' + part2 + '_' + part3);
    })

  $('input[name="penamaan_revisi_laporan"]').change(function() {
    if ($('input[name="penamaan_revisi_laporan"]:checked').val() === '1') {
      $('select[name="penamaan_revisi_laporan_part1"]').prop('disabled', false)
      $('select[name="penamaan_revisi_laporan_part2"]').prop('disabled', false)
      $('select[name="penamaan_revisi_laporan_part3"]').prop('disabled', false)
    } else {
      $('select[name="penamaan_revisi_laporan_part1"]').prop('disabled', true)
      $('select[name="penamaan_revisi_laporan_part2"]').prop('disabled', true)
      $('select[name="penamaan_revisi_laporan_part3"]').prop('disabled', true)
    }
  })

  $('select[name="penamaan_revisi_laporan_part1"], select[name="penamaan_revisi_laporan_part2"], select[name="penamaan_revisi_laporan_part3"]')
    .change(function() {
      let penamaanProposal = $('#contoh_penamaan_revisi_laporan')
      let part1 = $('select[name="penamaan_revisi_laporan_part1"]').val();
      let part2 = $('select[name="penamaan_revisi_laporan_part2"]').val();
      let part3 = $('select[name="penamaan_revisi_laporan_part3"]').val();
      if (part1 === 'nim') {
        part1 = 'NIM'
      } else if (part1 === 'nama') {
        part1 = 'Nama'
      } else {
        part1 = 'RevisiLaporanSkripsi'
      }

      if (part2 === 'nim') {
        part2 = 'NIM'
      } else if (part2 === 'nama') {
        part2 = 'Nama'
      } else {
        part2 = 'RevisiLaporanSkripsi'
      }

      if (part3 === 'nim') {
        part3 = 'NIM'
      } else if (part3 === 'nama') {
        part3 = 'Nama'
      } else {
        part3 = 'RevisiLaporanSkripsi'
      }
      penamaanProposal.text(part1 + '_' + part2 + '_' + part3);
    })
})
</script>


@endsection
