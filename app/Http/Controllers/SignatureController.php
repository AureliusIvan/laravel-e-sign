<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\ProposalSkripsi;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use setasign\Fpdi\Tcpdf\Fpdi;
use Smalot\PdfParser\Parser;
use Spatie\PdfToImage\Exceptions\PdfDoesNotExist;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Log;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TCPDF;

//use Barryvdh\DomPDF\Facade as PDF;

class SignatureController extends Controller
{

    /**
     * Check Thesis (Approve/Reject) Page (V2) for E-Sign
     * @return Factory|View|Application
     * TODO: implement logic to sign the thesis (perhaps we can make it on another route)
     */
    public function checkThesis(): Factory|View|Application
    {
    //  v2 data. we want to retrieve all data from proposal_skripsi table
        $data = ProposalSkripsi::with('proposalSkripsiForm')
            ->with('penilaiPertama', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKedua', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('penilaiKetiga', function ($query) {
                $query->select('id', 'nama');
            })
            ->with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
            ->with('kodePenelitianProposal.areaPenelitian')
            ->get();


        return view('pages.dosen.esign.check-thesis', [
            'title' => 'Cek Proposal Skripsi',
            'subtitle' => 'Cek Proposal Skripsi',
            'data' => $data,
            'status' => 'none',
        ]);
    }

    /**
     * @throws CrossReferenceException
     * @throws PdfReaderException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws FilterException
     */
    public function signThesis(Request $request)
    {
        // Step 1: Validate the incoming request data
        try{
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:proposal_skripsi,id',
                'x' => 'required|numeric',
                'y' => 'required|numeric',
                'width' => 'required|numeric',
                'height' => 'required|numeric',
                'page_number' => 'required|integer',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to validate incoming request data: " . $e->getMessage());
            return response()->json(['error' => 'Invalid request data.'], 400);
        }

        try {
            // Step 2: Retrieve the proposal with the related form
            $proposal = ProposalSkripsi::with('proposalSkripsiForm')
                ->with('penilaiPertama', function ($query) {
                    $query->select('id', 'nama', 'nid');
                })
                ->with('penilaiKedua', function ($query) {
                    $query->select('id', 'nama', 'nid');
                })->with('penilaiKetiga', function ($query) {
                    $query->select('id', 'nama', 'nid');
                })
                ->with('mahasiswa', function ($query) {
                    $query->select('id', 'nim', 'nama');
                })
                ->findOrFail($validatedData['id']);

            $kaprodi = Dosen::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('role', 'kaprodi');
                })
                ->first();

            if (!$kaprodi) {
                Log::warning("No kaprodi found in the database, proceeding without kaprodi information");
            }

            // Step 3: Get the PDF file path
            $fileName = $proposal->file_proposal_random; // e.g., "20241012125326_YDMVXNrBInh4oAoMhE471fFsrYmmmBCZ54HOzkMK.pdf"
            $originalPdfPath = storage_path('app/uploads/proposal/' . $fileName); // Adjust the path as needed

            // Check if the file exists
            if (!file_exists($originalPdfPath)) {
                Log::error("Proposal PDF not found at path: {$originalPdfPath}");
                return response()->json(['error' => 'Proposal PDF not found.'], 404);
            }

            // Check PDF compatibility before processing
            if (!$this->isPdfCompatibleWithFpdi($originalPdfPath)) {
                Log::warning("PDF may not be compatible with FPDI free version: {$originalPdfPath}");
                // Continue anyway, but we'll handle the error in the processing step
            }

            // Step 4: Ensure the temp directory exists before QR code generation
            $tempDir = storage_path('app/temp/');
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    Log::error("Failed to create temp directory at: {$tempDir}");
                    return response()->json(['error' => 'Server error. Please try again later.'], 500);
                }
            }

            // Generate the QR Code with Dummy Data using chillerlan/php-qrcode
            // Define the dummy data you want to encode in the QR code
            Log::info("Looking for Dosen with user_id: " . Auth::user()->id);
            $lecturer = Dosen::where('user_id', Auth::user()->id)->first();
            if (!$lecturer) {
                Log::error("No Dosen record found for user ID: " . Auth::user()->id);
                Log::info("Available Dosen records: " . Dosen::all()->pluck('user_id')->toJson());
                return response()->json(['error' => 'Lecturer profile not found.'], 404);
            }
            Log::info("Found lecturer: " . $lecturer->nama);
            $qrData = "Signed by $lecturer->nama ($lecturer->nid) \nDate: " . now()->toDateTimeString();

            // Define QR code options
            $options = new QROptions([
                'version'      => 5,
                'outputType'   => QRCode::OUTPUT_IMAGE_PNG, // PNG output
                'eccLevel'     => QRCode::ECC_L,
                'scale'        => 5,
                'margin'       => 1,
                'imageBase64'  => false, // Ensure we're working with binary PNG, not base64
                'pngTransparency' => true, // Enable transparency for PNG
                'bgColor'      => [0, 0, 0, 127], // Fully transparent background (RGBA)
            ]);

            $qrcode = new QRCode($options);

            // Initialize QRCode object with options
            $qrCodePath = storage_path('app/temp/qr_code.png');  // Define the path
            $qrcode->render($qrData, $qrCodePath);  // Save as PNG
            Log::info("QR code saved at: {$qrCodePath}");
        } catch (\Exception $e) {
            Log::error("Failed to generate QR code: " . $e->getMessage());
            return response()->json(['error' => 'Failed to generate QR code. (1)'], 500);
        }

        if (!file_exists($qrCodePath)) {
            Log::error("QR code PNG not saved at: {$qrCodePath}");
            return response()->json(['error' => 'Failed to generate QR code. (2)'], 500);
        }



        // Step 5: Manipulate the PDF to embed the QR Code
        try {
            // Initialize FPDI
            $pdf = new Fpdi();

            // Set the source file - handle compression issues
            try {
                $pageCount = $pdf->setSourceFile($originalPdfPath);
                Log::info("Original PDF has {$pageCount} pages.");
            } catch (\Exception $fpdiError) {
                Log::error("FPDI error processing PDF: " . $fpdiError->getMessage());
                
                // Check if it's a compression issue
                if (strpos($fpdiError->getMessage(), 'compression') !== false || 
                    strpos($fpdiError->getMessage(), 'parser') !== false) {
                    
                    // Clean up the temporary QR code image before returning
                    if (file_exists($qrCodePath)) {
                        unlink($qrCodePath);
                        Log::info("Temporary QR code image deleted: {$qrCodePath}");
                    }
                    
                    // Clean up QR code before trying alternative approaches
                    if (file_exists($qrCodePath)) {
                        unlink($qrCodePath);
                        Log::info("Temporary QR code image deleted: {$qrCodePath}");
                    }
                    
                    // Skip complex image conversion and go directly to simple certificate approach
                    Log::info("FPDI failed due to compression issues, creating signature certificate instead");
                    return $this->signThesisWithSimpleOverlay($validatedData, $proposal, $qrCodePath, $fileName, $kaprodi);
                }
                
                // Re-throw if it's not a compression issue
                throw $fpdiError;
            }

            // Iterate through each page and add QR code on the first page for testing
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                // cast page_number to integer
                $pageNumber = (int)$validatedData['page_number'];
                // Import a page
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                // Add a page with the same orientation and size
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

                // Use the imported page as the template
                $pdf->useTemplate($templateId);

                if($pageNumber == $pageNo){
                    // Define the position where you want to place the QR code
                    // Convert to float to ensure correct
                    // convert px to mm (1px = 0.2645833333 mm)
                    $x = floatval($validatedData['x']) / floatval($validatedData['width']) * $size['width'] - 3;
                    $y = floatval($validatedData['y']) / floatval($validatedData['height']) * $size['height'] - 1;

                    // Log the QR code embedding details
                    Log::info("Embedding QR code on page {$pageNo} at ({$x}mm, {$y}mm)");

                    // Add the QR code image
                    $qrWidth = 20; // mm
                    $qrHeight = 20; // mm
                    $pdf->Image($qrCodePath, $x, $y, $qrWidth, $qrHeight);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to manipulate PDF: " . $e->getMessage());
            // Clean up the temporary QR code image before returning
            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
                Log::info("Temporary QR code image deleted: {$qrCodePath}");
            }
            return response()->json(['error' => 'Failed to process PDF.', $e->getMessage()], 500);
        }



        try {
            // Correct usage of Output method: first parameter is the path/name, second is the destination
            // Step 6: Save the modified PDF
            $modifiedFileName = 'signed_' . $fileName;
            $signedDirectory = storage_path('app/proposals/signed/');
            $modifiedPdfPath = $signedDirectory . $modifiedFileName;

            // Ensure the signed directory exists
            if (!file_exists($signedDirectory)) {
                if (!mkdir($signedDirectory, 0755, true)) {
                    Log::error("Failed to create signed directory at: {$signedDirectory}");
                    // Clean up the temporary QR code image before returning
                    if (file_exists($qrCodePath)) {
                        unlink($qrCodePath);
                        Log::info("Temporary QR code image deleted: {$qrCodePath}");
                    }
                    return response()->json(['error' => 'Server error. Please try again later.'], 500);
                }
            }
            $pdf->Output($modifiedPdfPath, 'F');
            Log::info("Signed PDF saved at: {$modifiedPdfPath}");
        } catch (\Exception $e) {
            Log::error("Failed to save signed PDF: " . $e->getMessage());
            // Clean up the temporary QR code image before returning
            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
                Log::info("Temporary QR code image deleted: {$qrCodePath}");
            }
            return response()->json(['error' => 'Failed to save signed PDF.'], 500);
        }

        // Step 7: Clean up the temporary QR code image
        if (file_exists($qrCodePath)) {
            if (unlink($qrCodePath)) {
                Log::info("Temporary QR code image deleted: {$qrCodePath}");
            } else {
                Log::warning("Failed to delete temporary QR code image: {$qrCodePath}");
            }
        }

        // Step 8: Embed the signed PDF with data.xml
//        dd($kaprodi->toArray());
        try{
            $data = [
                'UUID' => $proposal->uuid,
                'Tipe_Laporan' => 'Skripsi',
                'Judul_Laporan' => $proposal->judul_proposal,
                'Prodi' => 'Teknik Informatika',
                'Tahun' => '2024',
                'Nama_Mahasiswa' => $proposal->mahasiswa->nama,
                'NIM' => $proposal->mahasiswa->nim,
                'Dosen_Pembimbing_1__Nama' => $proposal->toArray()['penilai_pertama']['nama'],
                'Dosen_Pembimbing_1__NIDN' => $proposal->toArray()['penilai_pertama']['nid'],
//                'Dosen_Pembimbing_2__Nama_' => $proposal->pembimbing2->nama,
//                'Dosen_Pembimbing_2__NIK___NIDN_' => $proposal->pembimbing2->nid,
//                'Dosen_Penguji' => $proposal->penilaiPertama->nama . ', ' . $proposal->penilaiKedua->nama . ', ' . $proposal->penilaiKetiga->nama,
//                'Dosen_Ketua_Sidang' => $proposal->penilaiPertama->nama,
                'KAPRODI' => $kaprodi ? $kaprodi->nama : 'N/A',
            ];

            $xmlContent = self::generateXML($data);
            $xmlPath = storage_path('data' . '.xml');
            file_put_contents($xmlPath, $xmlContent);

            self::embedFilesInExistingPdf(
                $modifiedPdfPath,
                $modifiedPdfPath, [
                $xmlPath
            ]);

            // Clean up the temporary XML file
            if (file_exists($xmlPath)) {
                if (unlink($xmlPath)) {
                    Log::info("Temporary XML file deleted: {$xmlPath}");
                } else {
                    Log::warning("Failed to delete temporary XML file: {$xmlPath}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to embed data.xml: " . $e->getMessage());
            return response()->json(
                [
                    'error' => 'Failed to embed data.xml.',
                    'message' => $e->getMessage()
                ]
                , 500);
        }

        // Step 8: Return the modified PDF as a download
        if (file_exists($modifiedPdfPath)) {
            // save
            $proposal->signed_proposal = $modifiedFileName;
            // hash value
            $hashValue = hash('sha512', file_get_contents($modifiedPdfPath));
            $hashValue = substr($hashValue, 0, 64);
            $proposal->hash_value = $hashValue;
            $proposal->save();

//            return response()->download($modifiedPdfPath)->deleteFileAfterSend(false);
            return redirect()->back()->with('success', 'Proposal berhasil ditandatangani.');
        } else {
            Log::error("Modified PDF not found at: {$modifiedPdfPath}");
            return response()->json(['error' => 'Signed PDF not found.'], 500);
        }
    }

    public function downloadSignedProposal($filename)
    {
        $filePath = storage_path('app/proposals/signed/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }


    // e-sign page
    public function verifyThesis()
    {
        // For admin users, just show the verify interface without student data
        if (Auth::user()->role === 'admin') {
            return view('pages.dosen.verify.verify', [
                'title' => 'Verify Dokumen',
                'subtitle' => 'Verify Dokumen',
                'data' => collect([]), // Empty collection for admin
                'status' => 'none',
            ]);
        }

        // For dosen, kaprodi, sekprodi - show student data
        $active = TahunAjaran::where('status_aktif', 1)->first();
        $user = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
        $pembimbingPertama = DB::table('pembimbing_mahasiswa')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'pembimbing_mahasiswa.mahasiswa')
            ->select(
                'mahasiswa.nim',
                'mahasiswa.nama',
            )
            ->where('pembimbing_mahasiswa.pembimbing1', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformPertama = $pembimbingPertama->map(function ($row) {
            return [
                'nim' => $row->nim,
                'nama' => $row->nama,
                'status_pembimbing' => 1,
            ];
        });

        $pembimbingKedua = DB::table('pembimbing_mahasiswa')
            ->leftJoin('mahasiswa', 'mahasiswa.id', '=', 'pembimbing_mahasiswa.mahasiswa')
            ->select(
                'mahasiswa.nim',
                'mahasiswa.nama',
            )
            ->where('pembimbing_mahasiswa.pembimbing2', $user->id)
            ->where('pembimbing_mahasiswa.tahun_ajaran_id', $active->id)
            ->where('pembimbing_mahasiswa.program_studi_id', $user->program_studi_id)
            ->get();

        $transformKedua = $pembimbingKedua->map(function ($row) {
            return [
                'nim' => $row->nim,
                'nama' => $row->nama,
                'status_pembimbing' => 2,
            ];
        });

        $data = $transformPertama->merge($transformKedua);

        return view('pages.dosen.verify.verify', [
            'title' => 'Verify Dokumen',
            'subtitle' => 'Verify Dokumen',
            'data' => $data,
            'status' => 'none',
        ]);
    }


    /**
     * Handle the uploaded PDF file and extract the embedded files.
     *
     * will return 3 types of status:
     * - success: if the PDF file is successfully parsed & hash_value is valid
     * - failed: if the PDF file is successfully parsed but hash_value is invalid
     * - error: if there is an error while parsing the PDF file
     * - none: if there is no PDF file uploaded
     */
    public function uploadVerifyThesis(Request $request)
    {
        // Validate the uploaded PDF file
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:30720'], // 30 MB max
        ]);

        try {
            // Load the PDF content into memory
            $pdfContent = $request->file('file')->get();
            // Extract embedded files
            $xmlContent = $this->extractEmbeddedFiles($pdfContent);
            $content = simplexml_load_string($xmlContent);

            // Convert the SimpleXMLElement object to an array
            $parsedArray = json_decode(json_encode($content), true);

            // Hash check
            $hashValue = hash('sha512', $pdfContent);
            $hashValue = substr($hashValue, 0, 64);

            // Check if the hash value matches the one stored in the database
            $proposal = ProposalSkripsi::where('uuid', $parsedArray['UUID'])->first();
            if ($proposal->hash_value !== $hashValue) {
                return view('pages.dosen.verify.verify', [
                    'title' => 'Verify Dokumen',
                    'subtitle' => 'Verify Dokumen',
                    'status' => 'failed',
                    'hash_value' => $hashValue,
                    'error' => 'Hash value mismatch',
                ]);
            }

            return view('pages.dosen.verify.verify', [
                'title' => 'Verify Dokumen',
                'subtitle' => 'Verify Dokumen',
                'Tipe_Laporan' => $parsedArray['Tipe_Laporan'],
                'Judul_Laporan' => $parsedArray['Judul_Laporan'],
                'Prodi' => $parsedArray['Prodi'],
                'Tahun' => $parsedArray['Tahun'],
                'Nama_Mahasiswa' => $parsedArray['Nama_Mahasiswa'],
                'NIM' => $parsedArray['NIM'],
                'Dosen_Pembimbing_1__Nama' => $parsedArray['Dosen_Pembimbing_1__Nama'],
                'Dosen_Pembimbing_1__NIDN' => $parsedArray['Dosen_Pembimbing_1__NIDN'],
//                'Dosen_Pembimbing_2__Nama_' => $parsedArray['Dosen_Pembimbing_2__Nama_'],
//                'Dosen_Pembimbing_2__NIK___NIDN_' => $parsedArray['Dosen_Pembimbing_2__NIDN_'],
//                'Dosen_Penguji' => $parsedArray['Dosen_Penguji'],
//                'Dosen_Ketua_Sidang' => $parsedArray['Dosen_Ketua_Sidang'],
                'KAPRODI' => $parsedArray['KAPRODI'],
                'status' => 'success',
                'hash_value' => $hashValue
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return view('pages.dosen.verify.verify', [
                'title' => 'Verify Dokumen',
                'subtitle' => 'Verify Dokumen',
                'status' => 'error',
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function extractEmbeddedFiles($pdfContent)
    {
        $embeddedFiles = [];

        try {
            // Use Smalot PDF parser to parse the content
            $parser = new Parser();
            $pdf = $parser->parseContent($pdfContent);

            // Extract the embedded files from the parsed PDF
            foreach ($pdf->getObjects() as $object) {
                // Check if the object is an embedded file object
                $dictionary = $object->getDocument()->getDictionary();
                if (isset($dictionary['EmbeddedFile'])) {
                    $embeddedFile = $dictionary['EmbeddedFile']['all'];
                    foreach ($embeddedFile as $key => $value) {
                        // Check if $value is an instance of Smalot\PdfParser\PDFObject
                        if ($value instanceof \Smalot\PdfParser\PDFObject) {
                            // Try to access the content directly
                            $content = $value->getContent(); // Check if there is a getContent() method available
                            return $content;
                        } else {
                            // Handle the case if the object does not contain the expected class
                            echo "No content found for key: $key";
                        }
                    }
                } else {
                    return "No embedded files found.";
                }
            }
        } catch (\Exception $e) {
            Log::error('Error extracting embedded files: ' . $e->getMessage());
        }

        return $embeddedFiles;
    }

    /**
     * Will return data.xml file
     */
    public static function generateXML($object): bool|string
    {
        // Convert the object to an array
        $arrayData = json_decode(json_encode($object), true);

        // Create a new XML document
        $xml = new \SimpleXMLElement('<root/>');

        // Recursive function to convert array data into XML
        self::arrayToXML($arrayData, $xml);

        // Save the XML to a buffer (string)
        return $xml->asXML();
    }

    private static function arrayToXML(array $data, \SimpleXMLElement &$xml): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                self::arrayToXML($value, $child);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }


    /**
     * @param $existingPdfPath
     * @param $outputPdfPath
     * @param array $filesToEmbed
     * @return JsonResponse|void
     * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
     * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
     * @throws \setasign\Fpdi\PdfParser\PdfParserException
     * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
     * @throws \setasign\Fpdi\PdfReader\PdfReaderException
     *
     * Embed files in an existing PDF file.
     * for example:
     * $filesToEmbed = [storage_path('data.json'), storage_path('data.xml')];
     * $this->embedFilesInExistingPdf(
     * storage_path('app/uploads/proposal/' . $fileNameRandom),
     * storage_path('app/uploads/proposal-signed/' . $fileNameRandom),
     * $filesToEmbed
     * );
     */
    public static function embedFilesInExistingPdf($existingPdfPath, $outputPdfPath, array $filesToEmbed)
    {
        // Ensure the existing PDF file exists
        if (!file_exists($existingPdfPath)) {
            return response()->json(['error' => 'PDF file not found'], 404);
        }

        // Create a new FPDI instance (which extends TCPDF)
        $pdf = new Fpdi();

        // Set document information if needed
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Universitas Multimedia Nusantara');
        $pdf->SetTitle('Document with Attachments');

        // Import the existing PDF
        $pageCount = $pdf->setSourceFile($existingPdfPath);

        // Import all pages from the existing PDF
        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($templateId);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        // Embed each file as a file annotation
        foreach ($filesToEmbed as $filePath) {
            if (file_exists($filePath)) {
                $fileName = 'data.xml'; // Change this as needed
                $description = "Attached file: {$fileName}";
                // Attach the file as an annotation to the first page
                $pdf->Annotation(
                    0, 0, 0.1, 0.1,     // Position and size of the annotation icon
                    $description,      // Description of the file
                    [
                        'Subtype' => 'FileAttachment',
                        'Name' => 'PushPin',
                        'FS' => $filePath
                    ]
                );
            } else {
                Log::warning("File not found: {$filePath}");
            }
        }

        // Output the PDF to a file
        $pdf->Output($outputPdfPath, 'F');
    }

    /**
     * @param $filename
     * @return BinaryFileResponse
     */
    public function serveFile($filename)
    {
        $filePath = storage_path('app/uploads/proposal/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    /**
     * Alternative PDF signing approach using Spatie PDF-to-Image when FPDI fails
     * @param array $validatedData
     * @param ProposalSkripsi $proposal
     * @param string $qrCodePath
     * @param string $fileName
     * @param Dosen|null $kaprodi
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    private function signThesisWithImageApproach($validatedData, $proposal, $qrCodePath, $fileName, $kaprodi)
    {
        Log::info("Attempting alternative PDF signing approach using Spatie PDF-to-Image");
        
        $originalPdfPath = storage_path('app/uploads/proposal/' . $fileName);
        
        try {
            // Try using Spatie PDF-to-Image with simple approach
            // Create new PDF using TCPDF
            $tcpdf = new \TCPDF();
            $tcpdf->SetCreator('Digital Signature System');
            $tcpdf->SetAuthor('Universitas Multimedia Nusantara');
            $tcpdf->SetTitle('Signed Proposal Document');
            $tcpdf->SetAutoPageBreak(false); // Disable auto page break
            
            $pageNumber = (int)$validatedData['page_number'];
            
            // Create temp directory for page images
            $tempImageDir = storage_path('app/temp/pdf_pages/');
            if (!file_exists($tempImageDir)) {
                mkdir($tempImageDir, 0755, true);
            }
            
            // Try to convert pages one by one using basic Spatie API
            $currentPage = 1;
            $successfulPages = 0;
            
            while (true) {
                try {
                    $tempImagePath = $tempImageDir . 'page_' . $currentPage . '.jpg';
                    
                    // Try to convert specific page using Spatie
                    $pdf = new Pdf($originalPdfPath);
                    $pdf->saveImage($tempImagePath);
                    
                    // Check if file was created
                    if (!file_exists($tempImagePath)) {
                        break; // No more pages
                    }
                    
                    // Get image dimensions
                    $imageSize = getimagesize($tempImagePath);
                    if (!$imageSize) {
                        unlink($tempImagePath);
                        break;
                    }
                    
                    $imageWidth = $imageSize[0];
                    $imageHeight = $imageSize[1];
                    
                    // Calculate page size in mm (150 DPI)
                    $pageWidthMM = ($imageWidth / 150) * 25.4;
                    $pageHeightMM = ($imageHeight / 150) * 25.4;
                    
                    // Add page to PDF
                    $tcpdf->AddPage('P', [$pageWidthMM, $pageHeightMM]);
                    
                    // Add the page image
                    $tcpdf->Image($tempImagePath, 0, 0, $pageWidthMM, $pageHeightMM, 'JPG', '', '', true, 150);
                    
                    // Add QR code to specified page
                    if ($currentPage == $pageNumber) {
                        // Calculate QR position
                        $x = (floatval($validatedData['x']) / floatval($validatedData['width'])) * $pageWidthMM;
                        $y = (floatval($validatedData['y']) / floatval($validatedData['height'])) * $pageHeightMM;
                        
                        // Add QR code
                        $tcpdf->Image($qrCodePath, $x, $y, 20, 20, 'PNG', '', '', true, 150);
                        
                        Log::info("Added QR code to page {$currentPage} at ({$x}mm, {$y}mm) using Spatie approach");
                    }
                    
                    // Clean up temp image
                    unlink($tempImagePath);
                    
                    $successfulPages++;
                    $currentPage++;
                    
                    // For now, just process the first page since we're having API issues
                    if ($currentPage > 5) { // Safety limit
                        break;
                    }
                    
                } catch (\Exception $pageError) {
                    Log::info("No more pages available at page {$currentPage}: " . $pageError->getMessage());
                    break;
                }
            }
            
            if ($successfulPages === 0) {
                throw new \Exception("Failed to convert any pages using Spatie approach");
            }
            
            Log::info("Successfully converted {$successfulPages} pages using Spatie approach");
            
            // Clean up temp directory
            if (file_exists($tempImageDir)) {
                rmdir($tempImageDir);
            }
            
            // Save the signed PDF
            $modifiedFileName = 'signed_' . $fileName;
            $signedDirectory = storage_path('app/proposals/signed/');
            $modifiedPdfPath = $signedDirectory . $modifiedFileName;
            
            if (!file_exists($signedDirectory)) {
                mkdir($signedDirectory, 0755, true);
            }
            
            $tcpdf->Output($modifiedPdfPath, 'F');
            
            // Clean up QR code
            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
            }
            
            // Embed XML data
            $this->embedXmlData($proposal, $kaprodi, $modifiedPdfPath);
            
            // Save to database
            $proposal->signed_proposal = $modifiedFileName;
            $hashValue = hash('sha512', file_get_contents($modifiedPdfPath));
            $hashValue = substr($hashValue, 0, 64);
            $proposal->hash_value = $hashValue;
            $proposal->save();
            
            Log::info("Successfully signed PDF using Spatie PDF-to-Image approach");
            
            return redirect()->back()->with('success', 'Proposal berhasil ditandatangani menggunakan metode konversi gambar.');
            
        } catch (\Exception $e) {
            Log::error("Spatie PDF approach failed: " . $e->getMessage());
            
            // If Spatie also fails, try a simpler approach using just TCPDF overlay
            return $this->signThesisWithSimpleOverlay($validatedData, $proposal, $qrCodePath, $fileName, $kaprodi);
        }
    }

    /**
     * Final fallback: Simple TCPDF overlay approach - just adds QR code to new page
     * @param array $validatedData
     * @param ProposalSkripsi $proposal
     * @param string $qrCodePath
     * @param string $fileName
     * @param Dosen|null $kaprodi
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    private function signThesisWithSimpleOverlay($validatedData, $proposal, $qrCodePath, $fileName, $kaprodi)
    {
        Log::info("Using final fallback: Simple TCPDF overlay approach");
        
        try {
            // Regenerate QR code if it was deleted
            if (!file_exists($qrCodePath)) {
                Log::info("Regenerating QR code for certificate approach");
                
                $lecturer = Dosen::where('user_id', Auth::user()->id)->first();
                $qrData = "Signed by $lecturer->nama ($lecturer->nid) \nDate: " . now()->toDateTimeString();
                
                $options = new QROptions([
                    'version'      => 5,
                    'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                    'eccLevel'     => QRCode::ECC_L,
                    'scale'        => 5,
                    'margin'       => 1,
                    'imageBase64'  => false,
                    'pngTransparency' => true,
                    'bgColor'      => [0, 0, 0, 127],
                ]);
                
                $qrcode = new QRCode($options);
                $qrcode->render($qrData, $qrCodePath);
            }
            // Create a simple PDF with just the signature information
            $tcpdf = new \TCPDF();
            $tcpdf->SetCreator('Digital Signature System');
            $tcpdf->SetAuthor('Universitas Multimedia Nusantara');
            $tcpdf->SetTitle('Signed Proposal Document');
            
            // Add a page
            $tcpdf->AddPage();
            
            // Add header
            $tcpdf->SetFont('helvetica', 'B', 16);
            $tcpdf->Cell(0, 15, 'DOKUMEN TELAH DITANDATANGANI DIGITAL', 0, 1, 'C');
            $tcpdf->Ln(10);
            
            // Add proposal information
            $tcpdf->SetFont('helvetica', '', 12);
            $tcpdf->Cell(50, 8, 'Judul Proposal:', 0, 0, 'L');
            $tcpdf->Cell(0, 8, $proposal->judul_proposal, 0, 1, 'L');
            $tcpdf->Ln(5);
            
            $tcpdf->Cell(50, 8, 'Mahasiswa:', 0, 0, 'L');
            $tcpdf->Cell(0, 8, $proposal->mahasiswa->nama . ' (' . $proposal->mahasiswa->nim . ')', 0, 1, 'L');
            $tcpdf->Ln(5);
            
            $tcpdf->Cell(50, 8, 'Ditandatangani:', 0, 0, 'L');
            $lecturer = Dosen::where('user_id', Auth::user()->id)->first();
            $tcpdf->Cell(0, 8, $lecturer->nama . ' pada ' . now()->format('d/m/Y H:i:s'), 0, 1, 'L');
            $tcpdf->Ln(10);
            
            // Add QR code
            $tcpdf->Cell(0, 8, 'Kode QR Verifikasi:', 0, 1, 'L');
            $tcpdf->Ln(5);
            $tcpdf->Image($qrCodePath, 20, $tcpdf->GetY(), 40, 40, 'PNG');
            
            $tcpdf->Ln(45);
            $tcpdf->SetFont('helvetica', 'I', 10);
            $tcpdf->Cell(0, 8, 'File proposal asli: ' . $fileName, 0, 1, 'L');
            $tcpdf->Cell(0, 8, 'Dokumen ini adalah bukti bahwa proposal telah ditandatangani secara digital.', 0, 1, 'L');
            $tcpdf->Cell(0, 8, 'Untuk verifikasi, silakan scan kode QR di atas.', 0, 1, 'L');
            
            // Save the signed PDF
            $modifiedFileName = 'signed_' . $fileName;
            $signedDirectory = storage_path('app/proposals/signed/');
            $modifiedPdfPath = $signedDirectory . $modifiedFileName;
            
            if (!file_exists($signedDirectory)) {
                mkdir($signedDirectory, 0755, true);
            }
            
            $tcpdf->Output($modifiedPdfPath, 'F');
            
            // Clean up QR code
            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
            }
            
            // Embed XML data
            $this->embedXmlData($proposal, $kaprodi, $modifiedPdfPath);
            
            // Save to database
            $proposal->signed_proposal = $modifiedFileName;
            $hashValue = hash('sha512', file_get_contents($modifiedPdfPath));
            $hashValue = substr($hashValue, 0, 64);
            $proposal->hash_value = $hashValue;
            $proposal->save();
            
            Log::info("Successfully created signature certificate using simple overlay approach");
            
            return redirect()->back()->with('success', 'Sertifikat tanda tangan digital telah dibuat. File asli tidak dapat dimodifikasi karena format PDF yang tidak didukung.');
            
        } catch (\Exception $e) {
            Log::error("Simple overlay approach failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper method to embed XML data into PDF
     */
    private function embedXmlData($proposal, $kaprodi, $modifiedPdfPath)
    {
        try {
            $data = [
                'UUID' => $proposal->uuid,
                'Tipe_Laporan' => 'Skripsi',
                'Judul_Laporan' => $proposal->judul_proposal,
                'Prodi' => 'Teknik Informatika',
                'Tahun' => '2024',
                'Nama_Mahasiswa' => $proposal->mahasiswa->nama,
                'NIM' => $proposal->mahasiswa->nim,
                'Dosen_Pembimbing_1__Nama' => $proposal->toArray()['penilai_pertama']['nama'],
                'Dosen_Pembimbing_1__NIDN' => $proposal->toArray()['penilai_pertama']['nid'],
                'KAPRODI' => $kaprodi ? $kaprodi->nama : 'N/A',
            ];

            $xmlContent = self::generateXML($data);
            $xmlPath = storage_path('data' . '.xml');
            file_put_contents($xmlPath, $xmlContent);

            self::embedFilesInExistingPdf($modifiedPdfPath, $modifiedPdfPath, [$xmlPath]);

            if (file_exists($xmlPath)) {
                unlink($xmlPath);
            }
        } catch (\Exception $e) {
            Log::warning("Failed to embed XML data in alternative approach: " . $e->getMessage());
            // Don't throw - the main signing was successful
        }
    }

    /**
     * Check if PDF is compatible with FPDI free version
     * @param string $pdfPath
     * @return bool
     */
    private function isPdfCompatibleWithFpdi(string $pdfPath): bool
    {
        try {
            // Try to read the first few bytes to check PDF version and potential compression
            $handle = fopen($pdfPath, 'rb');
            if (!$handle) {
                return false;
            }
            
            $header = fread($handle, 1024);
            fclose($handle);
            
            // Check for PDF version (FPDI free works better with older PDF versions)
            if (preg_match('/%PDF-1\.([0-9])/', $header, $matches)) {
                $version = intval($matches[1]);
                if ($version >= 7) {
                    Log::info("PDF version 1.{$version} detected - may have compatibility issues with FPDI free");
                    return false;
                }
            }
            
            // Check for object streams which are not supported in FPDI free
            if (strpos($header, '/ObjStm') !== false) {
                Log::info("PDF contains object streams - not supported by FPDI free");
                return false;
            }
            
            // Check for cross-reference streams
            if (strpos($header, '/XRef') !== false) {
                Log::info("PDF contains cross-reference streams - may have compatibility issues");
                return false;
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error checking PDF compatibility: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert pdf to array of images
     * @param $filename
     * @return array
     * @throws PdfDoesNotExist
     */
    public function convertPdfToImages($filename): array
    {
        $filePath = storage_path('app/uploads/proposal/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        $images = [];
        $baseImagePath = 'storage/cached/' . pathinfo($filename, PATHINFO_FILENAME);

        // Check if images are already cached
        for ($page = 1;; $page++) {
            $imagePath = "{$baseImagePath}_page_{$page}.jpg";
            if (!Storage::exists($imagePath)) {
                break; // Stop if we don't find a page in the cache
            }
            $images[] = Storage::url($imagePath);
        }

        // If images are found in cache, return them
        if (!empty($images)) {
            return $images;
        }

        // If no cached images, perform the conversion
        $pdf = new Pdf($filePath);
        $totalPages = $pdf->pageCount();

        for ($page = 1; $page <= $totalPages; $page++) {
            $imagePath = "{$page}.jpg";
            (new Pdf($filePath))->selectPage($page)->save($imagePath);
            $images[] = Storage::url($imagePath);
        }

        return $images;
    }

}
