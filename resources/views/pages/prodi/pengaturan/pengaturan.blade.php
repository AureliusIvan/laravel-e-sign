@extends('layouts.main')

@section('content')
@include('partials.prodi-nav')

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
                    <div class="card card-outline card-info scroll">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title">
                                    Pengaturan
                                </h3>
                                @if ($data)

                                <div class="card-tools">
                                    <button class="btn btn-info btn-edit-form btn-sm">
                                        <i class="fas fa-edit mr-1">
                                        </i>
                                        Edit
                                    </button>
                                </div>

                                @endif
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <tr>
                                        <th style="width: 30%;">Kuota Pembimbing Pertama</th>
                                        <td>
                                            {{ $data->pengaturanDetail->kuota_pembimbing_pertama }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="width: 30%;">Kuota Pembimbing Kedua</th>
                                        <td>
                                            {{ $data->pengaturanDetail->kuota_pembimbing_pertama }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

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
    $('#formEdit :input').prop('disabled', true)

    $(document).on('click', '.btn-edit-form', function() {
        $('#formEdit :input').prop('disabled', false)
    });

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
                part1 = 'JudulProposalSkripsi'
            }

            if (part2 === 'nim') {
                part2 = 'NIM'
            } else if (part2 === 'nama') {
                part2 = 'Nama'
            } else {
                part2 = 'JudulProposalSkripsi'
            }

            if (part3 === 'nim') {
                part3 = 'NIM'
            } else if (part3 === 'nama') {
                part3 = 'Nama'
            } else {
                part3 = 'JudulProposalSkripsi'
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
                part1 = 'JudulProposalSkripsi'
            }

            if (part2 === 'nim') {
                part2 = 'NIM'
            } else if (part2 === 'nama') {
                part2 = 'Nama'
            } else {
                part2 = 'JudulProposalSkripsi'
            }

            if (part3 === 'nim') {
                part3 = 'NIM'
            } else if (part3 === 'nama') {
                part3 = 'Nama'
            } else {
                part3 = 'JudulProposalSkripsi'
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
                part1 = 'JudulLaporanSkripsi'
            }

            if (part2 === 'nim') {
                part2 = 'NIM'
            } else if (part2 === 'nama') {
                part2 = 'Nama'
            } else {
                part2 = 'JudulLaporanSkripsi'
            }

            if (part3 === 'nim') {
                part3 = 'NIM'
            } else if (part3 === 'nama') {
                part3 = 'Nama'
            } else {
                part3 = 'JudulLaporanSkripsi'
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
                part1 = 'JudulLaporanSkripsi'
            }

            if (part2 === 'nim') {
                part2 = 'NIM'
            } else if (part2 === 'nama') {
                part2 = 'Nama'
            } else {
                part2 = 'JudulLaporanSkripsi'
            }

            if (part3 === 'nim') {
                part3 = 'NIM'
            } else if (part3 === 'nama') {
                part3 = 'Nama'
            } else {
                part3 = 'JudulLaporanSkripsi'
            }
            penamaanProposal.text(part1 + '_' + part2 + '_' + part3);
        })
})
</script>
@endsection
