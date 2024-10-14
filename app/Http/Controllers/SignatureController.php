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
        $validatedData = $request->validate([
            'id' => 'required|integer|exists:proposal_skripsi,id',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'page_number' => 'required|integer',
        ]);

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

//        $kaprodi = User::where('role', 'kaprodi')
//            ->with('dosen')
//            ->firstOrFail();

        $kaprodi = Dosen::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'kaprodi');
            })
            ->firstOrFail();

        // Step 3: Get the PDF file path
        $fileName = $proposal->file_proposal_random; // e.g., "20241012125326_YDMVXNrBInh4oAoMhE471fFsrYmmmBCZ54HOzkMK.pdf"
        $originalPdfPath = storage_path('app/uploads/proposal/' . $fileName); // Adjust the path as needed

        // Check if the file exists
        if (!file_exists($originalPdfPath)) {
            Log::error("Proposal PDF not found at path: {$originalPdfPath}");
            return response()->json(['error' => 'Proposal PDF not found.'], 404);
        }

        // Step 4: Generate the QR Code with Dummy Data using chillerlan/php-qrcode
        // Define the dummy data you want to encode in the QR code
        $lecturer = Dosen::where('user_id', Auth::user()->id)->firstOrFail();
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

        try {
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

        // Ensure the temp directory exists
        $tempDir = storage_path('app/temp/');
        if (!file_exists($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                Log::error("Failed to create temp directory at: {$tempDir}");
                return response()->json(['error' => 'Server error. Please try again later.'], 500);
            }
        }

        // Step 5: Manipulate the PDF to embed the QR Code
        try {
            // Initialize FPDI
            $pdf = new Fpdi();

            // Set the source file
            $pageCount = $pdf->setSourceFile($originalPdfPath);
            Log::info("Original PDF has {$pageCount} pages.");

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
            return response()->json(['error' => 'Failed to process PDF.', $e], 500);
        }

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

        // Correct usage of Output method: first parameter is the path/name, second is the destination
        try {
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
                'KAPRODI' => $kaprodi->nama,
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
            'title' => 'Verifikasi Proposal Skripsi',
            'subtitle' => 'Verifikasi Proposal Skripsi',
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
                    'title' => 'Mahasiswa',
                    'subtitle' => 'List Mahasiswa Bimbingan',
                    'status' => 'failed',
                    'hash_value' => $hashValue,
                    'error' => 'Hash value mismatch',
                ]);
            }

            return view('pages.dosen.verify.verify', [
                'title' => 'Mahasiswa',
                'subtitle' => 'List Mahasiswa Bimbingan',
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
                'title' => 'Mahasiswa',
                'subtitle' => 'List Mahasiswa Bimbingan',
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
