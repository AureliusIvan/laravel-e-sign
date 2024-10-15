@extends('layouts.main')

@section('style')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Style for document container */
        #doc-container {
            position: relative;
            width: 100%;
            height: 80vh;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    @if (auth()->user()->role === 'dosen')
        @include('partials.dosen-nav')
    @elseif (auth()->user()->role === 'kaprodi' || auth()->user()->role === 'sekprodi')
        @include('partials.prodi-nav')
    @endif

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h1 class="font-weight-bold">Periksa Proposal Skripsi</h1>
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
                        <div class="card card-outline card-info scroll">
                            <!-- Header -->
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">
                                        Periksa Proposal Skripsi
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse"
                                                title="Collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="card-body">
                                <table id="table-data" class="table table-bordered table-striped"
                                       style="font-size: 0.85em; width: 100%;">
                                    <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 30%;">Judul Proposal Skripsi</th>
                                        <th>Mahasiswa</th>
                                        <th style="width: 15%;">Details</th>
                                        <th style="width: 15%;">
                                            Download Signed Proposal
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data as $d)
                                        <tr data-child-value="{{ $d->keterangan }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $d->judul_proposal }}</td>
                                            <td>{{ $d->mahasiswa->nama }}</td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                        value="{{ $d->id }}"
                                                        data-toggle="modal"
                                                        data-target="#uploadModalPenilai" data-penilai="1">
                                                    <i class="fas fa-upload mr-1"></i>
                                                    Details
                                                </button>
                                            </td>
                                            <td>
                                                @if($d->signed_proposal)
                                                    <a href="{{ route('proposal.signed.download', basename($d->signed_proposal)) }}"
                                                       class="btn btn-primary btn-sm">
                                                        <i class="fas fa-download mr-1"></i>
                                                        Download
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End of Container -->
        </section>
    </div>
@endsection

<div class="modal fade" id="uploadModalPenilai">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Upload File Hasil Periksa</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addForm" action="{{ route('proposal.skripsi.periksa.store') }}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="penilai">
                    <input type="hidden" name="proposal_id">

                    <div class="form-group">
                        <label for="status">Status Approval Proposal</label>
                        <div class="icheck-primary" style="margin-right: 1%;">
                            <input type="radio" id="radioPrimary1" name="status" value="1" required/>
                            <label for="radioPrimary1">Diterima</label>
                        </div>
                        <div class="icheck-danger">
                            <input type="radio" id="radioDanger1" name="status" value="0"/>
                            <label for="radioDanger1">Ditolak</label>
                        </div>
                    </div>

                    <!-- File Koreksi -->
                    <div class="form-group" id="rejection-form">
                        <label for="exampleInputFile">
                            Silahkan upload file proposal RTI atau proposal yang sudah anda
                            buat
                        </label>
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="exampleInputFile" name="file"
                                       accept="application/pdf" required>
                                <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                            </div>
                        </div>
                        <br>
                        <div class="d-flex flex-row">
                            <div class="mr-2">
                                <input type="submit" class="btn btn-primary" name="action" value="Save"/>
                            </div>
                        </div>
                    </div>
                    <!-- End -->
                </form>

                <div id="doc-container" style="width: 100%; height: 600px; overflow-y: scroll; border: 1px solid #ddd;"></div>
                <form id="signature-form" action={{ route('sign.thesis') }} method="POST" hidden="">
                    @csrf
                    <input type="hidden" name="x">
                    <input type="hidden" name="y">
                    <input type="hidden" name="page_number">
                    <input type="hidden" name="width">
                    <input type="hidden" name="height">
                    <input type="hidden" name="id">
                    <button type="submit">Submit</button>
                </form>

            </div>
        </div>
        <!-- /.modal-content -->
    </div>
</div>

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle conditional rendering on Radio Selection
            const diterimaRadio = document.getElementById('radioPrimary1'); // 'Diterima' radio
            const ditolakRadio = document.getElementById('radioDanger1'); // 'Ditolak' radio
            const signatureDocContainer = document.getElementById('doc-container'); // Signature form
            const rejectionFormGroupContainer = document.getElementById('rejection-form'); // Rejection form
            // Initially hide the signature form
            signatureDocContainer.style.display = 'none';
            rejectionFormGroupContainer.style.display = 'none';

            // Function to toggle the visibility of the signature form
            function toggleSignatureForm() {
                if (diterimaRadio.checked) {
                    signatureDocContainer.style.display = 'block'; // Show signature form
                    rejectionFormGroupContainer.style.display = 'none'; // Hide rejection form

                } else {
                    signatureDocContainer.style.display = 'none'; // Hide signature form
                    rejectionFormGroupContainer.style.display = 'block'; // Show rejection form
                }
            }

            // Attach event listeners to the radio buttons
            diterimaRadio.addEventListener('change', toggleSignatureForm);
            ditolakRadio.addEventListener('change', toggleSignatureForm);

            const url = '{{ route('proposal.serve', basename($d->file_proposal_random)) }}';
            const container = document.getElementById('doc-container');  // Holds all canvases

            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

            // Create the QR box and append it to the container
            const $qrBox = $('<div class="qr-box"></div>').appendTo(container);
            $qrBox.css({
                position: 'absolute',
                width: '100px',
                height: '100px',
                border: '2px solid black',
                display: 'none',
                pointerEvents: 'none',
                zIndex: 1000,
                background: 'rgba(255, 255, 255, 0.5)',
            });

            let mouseX = 0, mouseY = 0;  // Store last mouse position

            // Load the PDF document
            pdfjsLib.getDocument(url).promise.then(pdf => {
                console.log(`PDF loaded with ${pdf.numPages} pages.`);

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    pdf.getPage(pageNumber).then(page => {
                        const scale = 1.5;
                        const viewport = page.getViewport({ scale });

                        const canvas = document.createElement('canvas');
                        canvas.style.marginBottom = '10px';
                        canvas.classList.add('pdf-canvas');
                        container.appendChild(canvas);

                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        page.render({ canvasContext: context, viewport: viewport })
                            .promise.then(() => console.log(`Page ${pageNumber} rendered.`))
                            .catch(err => console.error('Error rendering page:', err));
                    }).catch(err => console.error(`Error loading page ${pageNumber}:`, err));
                }
            }).catch(err => console.error('Error loading PDF:', err));

            // Function to update QR box position
            function updateQRBoxPosition() {
                const containerOffset = $(container).offset();
                const scrollTop = $(container).scrollTop();
                const scrollLeft = $(container).scrollLeft();

                const x = mouseX - containerOffset.left + scrollLeft - 25;
                const y = mouseY - containerOffset.top + scrollTop - 25;

                if (x >= 0 && y >= 0 && x <= container.scrollWidth - 50 && y <= container.scrollHeight - 50) {
                    $qrBox.css({ left: `${x}px`, top: `${y}px` }).show();
                } else {
                    $qrBox.hide();
                }
            }

            // Track mouse movement within the container
            $(container).on('mousemove', function (e) {
                mouseX = e.pageX;  // Update mouseX
                mouseY = e.pageY;  // Update mouseY
                updateQRBoxPosition();
            });

            // Update QR box on scroll
            $(container).on('scroll', function () {
                updateQRBoxPosition();  // Update position on scroll
            });

            // Hide the QR box when leaving the container
            $(container).on('mouseleave', function () {
                $qrBox.hide();
            });

            // Capture click coordinates and submit the form
            $(container).on('click', '.pdf-canvas', function (e) {
                const offset = $(this).offset();
                const posX = e.pageX - offset.left;
                const posY = e.pageY - offset.top;

                $('#signature-form input[name="x"]').val(posX) - 25;
                $('#signature-form input[name="y"]').val(posY) - 25;
                $('#signature-form input[name="width"]').val($(this).width());
                $('#signature-form input[name="height"]').val($(this).height());
                $('#signature-form input[name="page_number"]').val($(this).index());
                $('#signature-form').submit();
            });


            $('#uploadModalPenilai').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);  // Button that triggered the modal
                const id = button.val();  // Extract id from button value
                $('#signature-form input[name="id"]').val(id);  // Set id in form
            });
        });

    </script>
@endsection
