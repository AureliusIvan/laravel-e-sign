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

        #doc-container canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        /* Responsive adjustments for mobile */
        @media (max-width: 768px) {
            .btn-group-vertical .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.5rem;
            }
            
            #table-data {
                font-size: 0.75em !important;
            }
            
            .modal-xl {
                max-width: 95%;
            }
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
                        <h1 class="font-weight-bold">Periksa Skripsi</h1>
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
                                        Periksa Skripsi
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
                                        <th style="width: 20%;">Judul Skripsi</th>
                                        <th>Mahasiswa</th>
                                        <th style="width: 15%;">Status Document</th>
                                        <th style="width: 10%;">Preview</th>
                                        <th style="width: 10%;">Approval</th>
                                        <th style="width: 15%;">
                                            Download Signed Thesis
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($data as $d)
                                        <tr data-child-value="{{ $d->keterangan }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>ID:</strong> {{ $d->judul_proposal }}<br>
                                                <strong>EN:</strong> {{ $d->judul_proposal_en ?? 'Not provided' }}
                                            </td>
                                            <td>{{ $d->mahasiswa->nama }}</td>
                                            <td>
                                                <!-- Overall Status -->
                                                <div class="mb-2">
                                                    @if ($d->signed_proposal)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i>Diterima
                                                        </span>
                                                    @elseif ($d->status_akhir === 0 || $d->status_approval_penilai1 === 0 || $d->status_approval_penilai2 === 0 || $d->status_approval_penilai3 === 0)
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times-circle mr-1"></i>Ditolak
                                                        </span>
                                                    @elseif ($d->status_akhir === 1 && $d->status_approval_penilai1 === 1 && $d->status_approval_penilai2 === 1 && $d->status_approval_penilai3 === 1)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-graduation-cap mr-1"></i>Lulus
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-hourglass-half mr-1"></i>Evaluasi
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Individual Evaluator Status -->
                                                <div class="small">
                                                    <div class="row">
                                                        <div class="col-4 text-center">
                                                            @if ($d->status_approval_penilai1 === 1)
                                                                <i class="fas fa-check text-success" title="Penilai 1: Diterima"></i>
                                                            @elseif ($d->status_approval_penilai1 === 0)
                                                                <i class="fas fa-times text-danger" title="Penilai 1: Ditolak"></i>
                                                            @else
                                                                <i class="fas fa-circle text-muted" title="Penilai 1: Belum"></i>
                                                            @endif
                                                            <br><span class="text-muted" style="font-size: 0.7em;">P1</span>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            @if ($d->status_approval_penilai2 === 1)
                                                                <i class="fas fa-check text-success" title="Penilai 2: Diterima"></i>
                                                            @elseif ($d->status_approval_penilai2 === 0)
                                                                <i class="fas fa-times text-danger" title="Penilai 2: Ditolak"></i>
                                                            @else
                                                                <i class="fas fa-circle text-muted" title="Penilai 2: Belum"></i>
                                                            @endif
                                                            <br><span class="text-muted" style="font-size: 0.7em;">P2</span>
                                                        </div>
                                                        <div class="col-4 text-center">
                                                            @if ($d->status_approval_penilai3 === 1)
                                                                <i class="fas fa-check text-success" title="Penilai 3: Diterima"></i>
                                                            @elseif ($d->status_approval_penilai3 === 0)
                                                                <i class="fas fa-times text-danger" title="Penilai 3: Ditolak"></i>
                                                            @else
                                                                <i class="fas fa-circle text-muted" title="Penilai 3: Belum"></i>
                                                            @endif
                                                            <br><span class="text-muted" style="font-size: 0.7em;">P3</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($d->file_proposal_random)
                                                    <button type="button" class="btn btn-info btn-sm btn-preview-thesis"
                                                            data-file="{{ $d->file_proposal_random }}"
                                                            data-title="{{ $d->judul_proposal }}">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        Preview
                                                    </button>
                                                @else
                                                    <span class="text-muted">No file available</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm btn-open-modal"
                                                        value="{{ $d->id }}"
                                                        data-toggle="modal"
                                                        data-target="#uploadModalPenilai" data-penilai="1">
                                                    <i class="fas fa-gavel mr-1"></i>
                                                    Approve/Reject
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
                <h4 class="modal-title">Persetujuan Proposal Skripsi</h4>
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

                    <!-- File Koreksi and Comment for Rejection -->
                    <div class="form-group" id="rejection-form">
                        <!-- Comment Section (Mandatory for rejection) -->
                        <div class="form-group">
                            <label for="rejection_comment">
                                Komentar Penolakan <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="rejection_comment" name="rejection_comment" 
                                      rows="4" placeholder="Silakan berikan alasan penolakan..."></textarea>
                            <small class="form-text text-muted">Komentar wajib diisi jika proposal ditolak.</small>
                        </div>

                        <!-- File Upload Section (Optional) -->
                        <div class="form-group">
                            <label for="exampleInputFile">
                                Upload File Koreksi <span class="text-muted">(Opsional)</span>
                            </label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="exampleInputFile" name="file"
                                           accept="application/pdf">
                                    <label class="custom-file-label" for="exampleInputFile">Choose file (optional)</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">File koreksi bersifat opsional. Anda dapat menolak proposal hanya dengan memberikan komentar.</small>
                        </div>

                        <div class="d-flex flex-row">
                            <div class="mr-2">
                                <input type="submit" class="btn btn-primary" name="action" value="Save"/>
                            </div>
                        </div>
                    </div>
                    <!-- End -->
                </form>

                <div id="doc-container"
                     style="width: 100%; height: fit-content; background-position: center; object-fit: contain; overflow-y: scroll; border: 1px solid #ddd;"></div>
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

<!-- Thesis Preview Modal -->
<div class="modal fade" id="previewModalThesis">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Preview Thesis</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h5 id="thesis-title-preview">Loading...</h5>
                    </div>
                    <div class="col-md-4 text-right">
                        <a id="download-link-preview" href="#" class="btn btn-success btn-sm" target="_blank">
                            <i class="fas fa-download mr-1"></i>
                            Download PDF
                        </a>
                    </div>
                </div>
                <div id="preview-container" style="width: 100%; height: 70vh; border: 1px solid #ddd; overflow: auto;">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
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

            // Add form submission debugging
            $('#addForm').on('submit', function(e) {
                const formData = new FormData(this);
                console.log('Form submission data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
                
                // Validate rejection comment if status is rejection
                const status = formData.get('status');
                const rejectionComment = formData.get('rejection_comment');
                
                if (status === '0') {
                    if (!rejectionComment || rejectionComment.trim().length < 10) {
                        e.preventDefault();
                        alert('Komentar penolakan harus diisi minimal 10 karakter!');
                        $('#rejection_comment').focus();
                        return false;
                    }
                }
            });

            let url;
            @if (!empty($data) && $data->isNotEmpty())
                // URL will be set dynamically when modal opens
            @endif
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
            let currentPdf = null; // Store current PDF reference

            // Function to load PDF document
            function loadPdfDocument(filename) {
                if (!filename) {
                    console.error('No filename provided for PDF loading');
                    return;
                }

                url = '{{ route("proposal.serve", ":filename") }}'.replace(':filename', filename);
                console.log('Loading PDF from:', url);

                // Clear previous content
                container.innerHTML = '';
                container.appendChild($qrBox[0]); // Re-add QR box

                // Load the PDF document
                pdfjsLib.getDocument(url).promise.then(pdf => {
                    currentPdf = pdf;
                    console.log(`PDF loaded with ${pdf.numPages} pages.`);

                    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                        pdf.getPage(pageNumber).then(page => {
                            const scale = 1.5;
                            const viewport = page.getViewport({scale});

                            const canvas = document.createElement('canvas');
                            canvas.style.marginBottom = '10px';
                            canvas.classList.add('pdf-canvas');
                            canvas.setAttribute('data-page', pageNumber);
                            container.appendChild(canvas);

                            const context = canvas.getContext('2d');
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;

                            page.render({canvasContext: context, viewport: viewport})
                                .promise.then(() => console.log(`Page ${pageNumber} rendered.`))
                                .catch(err => console.error('Error rendering page:', err));
                        }).catch(err => console.error(`Error loading page ${pageNumber}:`, err));
                    }
                }).catch(err => {
                    console.error('Error loading PDF:', err);
                    container.innerHTML = '<div class="alert alert-danger">Gagal memuat dokumen PDF. Silakan coba lagi.</div>';
                });
            }

            // Function to update QR box position
            function updateQRBoxPosition() {
                const containerOffset = $(container).offset();
                const scrollTop = $(container).scrollTop();
                const scrollLeft = $(container).scrollLeft();

                const x = mouseX - containerOffset.left + scrollLeft - 25;
                const y = mouseY - containerOffset.top + scrollTop - 25;

                if (x >= 0 && y >= 0 && x <= container.scrollWidth - 50 && y <= container.scrollHeight - 50) {
                    $qrBox.css({left: `${x}px`, top: `${y}px`}).show();
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

                const pageNumber = parseInt($(this).attr('data-page'));

                $('#signature-form input[name="x"]').val(posX - 25);
                $('#signature-form input[name="y"]').val(posY - 25);
                $('#signature-form input[name="width"]').val($(this).width());
                $('#signature-form input[name="height"]').val($(this).height());
                $('#signature-form input[name="page_number"]').val(pageNumber);
                $('#signature-form').submit();
            });


            $('#uploadModalPenilai').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);  // Button that triggered the modal
                const id = button.val();  // Extract id from button value
                const penilai = button.data('penilai');  // Extract penilai from button data
                
                // Set id in both forms
                $('#signature-form input[name="id"]').val(id);  // Set id in signature form
                $('#addForm input[name="proposal_id"]').val(id);  // Set id in approval form
                $('#addForm input[name="penilai"]').val(penilai);  // Set penilai in approval form
                
                console.log('Modal opened for proposal ID:', id, 'Penilai:', penilai);

                // Find the proposal data and load PDF
                const proposalRow = button.closest('tr');
                const previewButton = proposalRow.find('.btn-preview-thesis');
                if (previewButton.length > 0) {
                    const filename = previewButton.data('file');
                    if (filename) {
                        console.log('Loading PDF for proposal:', filename);
                        loadPdfDocument(filename);
                    } else {
                        console.warn('No filename found for proposal');
                        container.innerHTML = '<div class="alert alert-warning">File proposal tidak tersedia.</div>';
                    }
                } else {
                    console.warn('No preview button found for this proposal');
                    container.innerHTML = '<div class="alert alert-warning">File proposal tidak tersedia.</div>';
                }
            });
        });

        // Handle Preview Thesis functionality
        $(document).on('click', '.btn-preview-thesis', function() {
            const filename = $(this).data('file');
            const title = $(this).data('title');
            const originalFilename = $(this).closest('tr').find('a[download]').attr('download') || 'thesis.pdf';
            
            // Set thesis title
            $('#thesis-title-preview').text(title);
            
            // Set download link
            const downloadUrl = '{{ route("proposal.serve", ":filename") }}'.replace(':filename', filename);
            $('#download-link-preview').attr('href', downloadUrl).attr('download', originalFilename);
            
            // Clear previous content
            const previewContainer = $('#preview-container');
            previewContainer.html(`
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading preview...</p>
                </div>
            `);
            
            // Show modal
            $('#previewModalThesis').modal('show');
            
            // Load PDF preview
            const pdfUrl = downloadUrl;
            loadPdfPreview(pdfUrl, previewContainer);
        });

        function loadPdfPreview(url, container) {
            // Create iframe for PDF preview
            const iframe = `
                <iframe 
                    src="${url}" 
                    style="width: 100%; height: 100%; border: none;"
                    type="application/pdf">
                    <p>Your browser does not support PDF preview. 
                    <a href="${url}" target="_blank">Click here to download the PDF</a></p>
                </iframe>
            `;
            
            container.html(iframe);
        }
    </script>
@endsection
