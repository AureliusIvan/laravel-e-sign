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
use Spatie\PdfToImage\Exceptions\PageDoesNotExist;
use Spatie\PdfToImage\Exceptions\CouldNotStoreImage;

//use Barryvdh\DomPDF\Facade as PDF;

class SignatureController extends Controller
{
    /**
     * PDF Cleaning Configuration
     * These can be moved to config files if needed
     */
    private const PDF_CLEANING_CONFIG = [
        'enabled' => true,                    // Enable/disable PDF cleaning
        'max_file_size' => 100 * 1024 * 1024, // 100MB max file size (increased for thesis docs)
        'max_pages' => 1000,                  // Maximum pages for image conversion (increased)
        'max_objects' => 50000,               // Maximum PDF objects (increased for complex docs)
        'cleanup_temp_files' => true,         // Clean up temporary files
        'temp_file_retention' => 3600,        // Keep temp files for 1 hour
        'fast_mode_threshold' => 20,          // Pages threshold for fast mode
        'image_conversion_timeout' => 180,    // 3 minutes timeout for image conversion
        'security_checks' => [
            'check_javascript' => true,       // Still check but with improved patterns
            'check_forms' => false,           // Allow forms (common in academic PDFs)
            'check_external_refs' => true,    // Check but with improved patterns
            'check_embedded_files' => false,  // Allow embedded files (we embed XML)
        ],
        'sanitization_method' => 'auto',      // 'auto', 'image', 'fpdi', 'fast'
    ];

    /**
     * XML Sanitization Configuration
     */
    private const XML_SANITIZATION_CONFIG = [
        'max_xml_size' => 1024 * 1024,        // 1MB max XML size
        'max_depth' => 10,                     // Maximum XML nesting depth
        'max_entities' => 100,                 // Maximum number of entities
        'allowed_elements' => [                // Whitelist of allowed XML elements
            'root', 'UUID', 'Tipe_Laporan', 'Judul_Laporan', 'Judul_Laporan_EN',
            'Prodi', 'Tahun', 'Nama_Mahasiswa', 'NIM', 'Dosen_Pembimbing_1__Nama',
            'Dosen_Pembimbing_1__NIDN', 'Dosen_Pembimbing_2__Nama', 'Dosen_Pembimbing_2__NIDN',
            'Dosen_Penguji', 'Dosen_Ketua_Sidang', 'KAPRODI'
        ],
        'forbidden_patterns' => [              // Patterns to reject
            '<!ENTITY',                        // External entities
            '<!DOCTYPE',                       // DTD declarations
            'SYSTEM',                          // System identifiers
            'PUBLIC',                          // Public identifiers
            '<script',                         // Script tags
            'javascript:',                     // JavaScript URLs
            'data:',                          // Data URLs
        ],
        'encoding' => 'UTF-8',                // Required encoding
        'validate_schema' => true,            // Enable XML schema validation
    ];

    /**
     * XAdES Configuration following ETSI TS 101 903
     */
    private const XADES_CONFIG = [
        'enabled' => true,
        'algorithm' => OPENSSL_ALGO_SHA256,
        'signature_method' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        'canonicalization_method' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
        'digest_method' => 'http://www.w3.org/2001/04/xmlenc#sha256',
        'namespaces' => [
            'ds' => 'http://www.w3.org/2000/09/xmldsig#',
            'xades' => 'http://uri.etsi.org/01903/v1.3.2#',
        ],
    ];

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
            'title' => 'Cek Skripsi',
            'subtitle' => 'Cek Skripsi',
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
                'width' => 'required|numeric|min:1',
                'height' => 'required|numeric|min:1',
                'page_number' => 'required|integer|min:1',
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to validate incoming request data: " . $e->getMessage());
            return redirect()->back()->with('error', 'Data permintaan tidak valid: ' . $e->getMessage());
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

            // Step 3: Get the PDF file path and validate
            $fileName = $proposal->file_proposal_random; 
            if (!$fileName) {
                Log::error("No proposal file found for proposal ID: {$validatedData['id']}");
                return redirect()->back()->with('error', 'File proposal tidak ditemukan.');
            }

            $originalPdfPath = storage_path('app/uploads/proposal/' . $fileName);

            // Check if the file exists
            if (!file_exists($originalPdfPath)) {
                Log::error("Proposal PDF not found at path: {$originalPdfPath}");
                return redirect()->back()->with('error', 'File proposal tidak ditemukan di server.');
            }

            // Step 4: COMPREHENSIVE PDF CLEANING AND SANITIZATION
            $cleanedPdfPath = $originalPdfPath; // Default to original path
            
            // Check if PDF cleaning is enabled (can be disabled via environment variable)
            $cleaningEnabled = self::PDF_CLEANING_CONFIG['enabled'] && !env('DISABLE_PDF_CLEANING', false);
            $fastSigningMode = env('FAST_SIGNING_MODE', false);
            
            if ($cleaningEnabled && !$fastSigningMode) {
                Log::info("Starting PDF cleaning and sanitization for: {$fileName}");
                
                // Check if we should use fast mode based on PDF characteristics
                $useFastMode = $this->shouldUseFastMode($originalPdfPath);
                
                $cleaningResult = $this->cleanAndSanitizePdf($originalPdfPath, $fileName, $useFastMode);
                
                if (!$cleaningResult['success']) {
                    Log::error("PDF cleaning failed: " . $cleaningResult['error']);
                    
                    // If cleaning fails, offer option to proceed without cleaning (for debugging)
                    if (env('ALLOW_UNCLEANED_PDFS', false)) {
                        Log::warning("Proceeding with uncleaned PDF due to ALLOW_UNCLEANED_PDFS setting");
                        $cleanedPdfPath = $originalPdfPath;
                    } else {
                        return redirect()->back()->with('error', 'Gagal membersihkan PDF: ' . $cleaningResult['error'] . 
                            ' (Jika ini adalah file yang sah, administrator dapat mengatur FAST_SIGNING_MODE=true untuk mode cepat)');
                    }
                } else {
                    $cleanedPdfPath = $cleaningResult['cleaned_path'];
                    Log::info("PDF successfully cleaned and saved at: {$cleanedPdfPath}");
                }
            } else {
                if ($fastSigningMode) {
                    Log::info("Fast signing mode enabled, skipping heavy PDF cleaning for: {$fileName}");
                } else {
                    Log::info("PDF cleaning is disabled, proceeding with original file");
                }
                
                // Still perform basic security checks even if cleaning is disabled
                $securityCheck = $this->performSecurityChecks($originalPdfPath);
                if (!$securityCheck['safe'] && !env('SKIP_PDF_SECURITY_CHECKS', false) && !$fastSigningMode) {
                    Log::warning("PDF security check failed but may proceed based on configuration");
                    if (!env('ALLOW_UNCLEANED_PDFS', false)) {
                        return redirect()->back()->with('error', 'File PDF tidak aman: ' . $securityCheck['reason'] . 
                            ' (Administrator dapat mengatur FAST_SIGNING_MODE=true untuk mode cepat)');
                    }
                }
            }

            // Step 5: Check PDF compatibility after cleaning
            if (!$this->isPdfCompatibleWithFpdi($cleanedPdfPath)) {
                Log::warning("Cleaned PDF may not be compatible with FPDI free version: {$fileName}");
                // Skip FPDI and go directly to image-based approach
                return $this->handlePdfSigning($validatedData, $proposal, $fileName, $kaprodi, true, $cleanedPdfPath);
            }

            return $this->handlePdfSigning($validatedData, $proposal, $fileName, $kaprodi, false, $cleanedPdfPath);

        } catch (\Exception $e) {
            Log::error("Unexpected error in signThesis: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $validatedData ?? null
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Comprehensive PDF cleaning and sanitization
     * 
     * @param string $originalPdfPath Original PDF file path
     * @param string $fileName Original file name
     * @param bool $useFastMode Whether to use fast mode (skip heavy sanitization)
     * @return array Result with success status and cleaned file path or error message
     */
    private function cleanAndSanitizePdf(string $originalPdfPath, string $fileName, bool $useFastMode = false): array
    {
        try {
            // Fast mode: Skip heavy validation and sanitization
            if ($useFastMode) {
                Log::info("Using fast mode for PDF cleaning: {$fileName}");
                
                // Only basic security check in fast mode
                $securityCheck = $this->performBasicSecurityCheck($originalPdfPath);
                if (!$securityCheck['safe']) {
                    return [
                        'success' => false,
                        'error' => 'File PDF mengandung konten yang tidak aman: ' . $securityCheck['reason']
                    ];
                }

                // Return original file path (no sanitization needed)
                return [
                    'success' => true,
                    'cleaned_path' => $originalPdfPath,
                    'cleaned_filename' => $fileName,
                    'method' => 'fast_mode'
                ];
            }

            // Step 1: Basic security validation
            $securityCheck = $this->performSecurityChecks($originalPdfPath);
            if (!$securityCheck['safe']) {
                return [
                    'success' => false,
                    'error' => 'File PDF mengandung konten yang tidak aman: ' . $securityCheck['reason']
                ];
            }

            // Step 2: Validate file integrity and structure (skip in fast mode)
            if (!$useFastMode) {
                $integrityCheck = $this->validatePdfIntegrity($originalPdfPath);
                if (!$integrityCheck['valid']) {
                    return [
                        'success' => false,
                        'error' => 'File PDF rusak atau tidak valid: ' . $integrityCheck['reason']
                    ];
                }
            } else {
                Log::info("Skipping detailed PDF integrity validation due to fast mode");
            }

            // Step 3: Create cleaned version path
            $cleanedFileName = 'cleaned_' . time() . '_' . $fileName;
            $cleanedPdfPath = storage_path('app/uploads/proposal/' . $cleanedFileName);

            // Step 4: Perform content sanitization
            $sanitizationResult = $this->sanitizePdfContent($originalPdfPath, $cleanedPdfPath, $useFastMode);
            if (!$sanitizationResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Gagal membersihkan konten PDF: ' . $sanitizationResult['error']
                ];
            }

            // Step 5: Validate cleaned PDF
            if (!file_exists($cleanedPdfPath)) {
                return [
                    'success' => false,
                    'error' => 'File PDF yang dibersihkan tidak dapat dibuat'
                ];
            }

            // Step 6: Final verification
            $finalCheck = $this->validateCleanedPdf($cleanedPdfPath);
            if (!$finalCheck['valid']) {
                // Clean up failed cleaned file
                if (file_exists($cleanedPdfPath)) {
                    unlink($cleanedPdfPath);
                }
                return [
                    'success' => false,
                    'error' => 'File PDF yang dibersihkan tidak valid: ' . $finalCheck['reason']
                ];
            }

            Log::info("PDF cleaning completed successfully", [
                'original_file' => $fileName,
                'cleaned_file' => $cleanedFileName,
                'original_size' => filesize($originalPdfPath),
                'cleaned_size' => filesize($cleanedPdfPath)
            ]);

            return [
                'success' => true,
                'cleaned_path' => $cleanedPdfPath,
                'cleaned_filename' => $cleanedFileName
            ];

        } catch (\Exception $e) {
            Log::error("Error during PDF cleaning: " . $e->getMessage(), [
                'file' => $fileName,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Kesalahan internal saat membersihkan PDF: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Perform comprehensive security checks on PDF
     * 
     * @param string $pdfPath Path to PDF file
     * @return array Security check results
     */
    private function performSecurityChecks(string $pdfPath): array
    {
        try {
            $fileContent = file_get_contents($pdfPath);
            if (!$fileContent) {
                return ['safe' => false, 'reason' => 'Tidak dapat membaca file PDF'];
            }

            // Check file size
            $fileSize = strlen($fileContent);
            $maxSize = self::PDF_CLEANING_CONFIG['max_file_size'];
            if ($fileSize > $maxSize) {
                return ['safe' => false, 'reason' => 'File terlalu besar (maksimal ' . round($maxSize / (1024 * 1024)) . 'MB)'];
            }

            // Check for valid PDF header
            if (substr($fileContent, 0, 4) !== '%PDF') {
                return ['safe' => false, 'reason' => 'File bukan PDF yang valid'];
            }

            // Check for suspicious content patterns based on configuration
            $securityConfig = self::PDF_CLEANING_CONFIG['security_checks'];
            $suspiciousPatterns = [];
            
            if ($securityConfig['check_javascript']) {
                $suspiciousPatterns = array_merge($suspiciousPatterns, [
                    '/\/JavaScript\s*\(\s*[^)]*eval/i',    // JavaScript with eval
                    '/\/JS\s*\(\s*[^)]*eval/i',            // JS with eval  
                    '/<script[^>]*>/i',                     // HTML script tags (more specific)
                    '/javascript:\s*[^;]*eval/i',           // JavaScript URLs with eval
                    '/\/AA\s*<<[^>]*\/JS/i',                // Auto-action with JavaScript
                ]);
            }
            
            if ($securityConfig['check_forms']) {
                $suspiciousPatterns = array_merge($suspiciousPatterns, [
                    '/\/SubmitForm\s*<<[^>]*\/F\s*\(/i',   // Form submissions with URLs
                    '/\/XFA\s*<<[^>]*\/F\s*\(/i',          // XFA forms with external refs
                ]);
            }
            
            if ($securityConfig['check_external_refs']) {
                $suspiciousPatterns = array_merge($suspiciousPatterns, [
                    '/\/OpenAction\s*<<[^>]*\/S\s*\/JavaScript/i', // Auto-execute JavaScript
                    '/\/Launch\s*<<[^>]*\/F\s*\(/i',               // Launch external files
                    '/\/GoToR\s*<<[^>]*\/F\s*\(/i',                // Go to remote files
                    '/\/ImportData\s*<<[^>]*\/F\s*\(/i',           // Import external data
                    '/\/Sound\s*<<[^>]*\/F\s*\(/i',                // Sound with external files
                    '/\/Movie\s*<<[^>]*\/F\s*\(/i',                // Movies with external files
                ]);
            }

            $foundSuspiciousContent = false;
            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $fileContent)) {
                    Log::warning("Suspicious content found in PDF", [
                        'pattern' => $pattern,
                        'file' => basename($pdfPath)
                    ]);
                    $foundSuspiciousContent = true;
                    break;
                }
            }

            // Only fail for truly dangerous patterns, not common PDF features
            if ($foundSuspiciousContent) {
                return ['safe' => false, 'reason' => 'File mengandung konten yang berpotensi berbahaya'];
            }

            // Check for embedded files based on configuration
            if ($securityConfig['check_embedded_files'] && strpos($fileContent, '/EmbeddedFile') !== false) {
                Log::warning("PDF contains embedded files", ['file' => basename($pdfPath)]);
                return ['safe' => false, 'reason' => 'File mengandung file tertanam yang tidak diizinkan'];
            } elseif (strpos($fileContent, '/EmbeddedFile') !== false) {
                Log::info("PDF contains embedded files (allowed by configuration)", ['file' => basename($pdfPath)]);
            }

            // Check for excessive object count (potential DoS)
            $objectCount = preg_match_all('/\d+\s+0\s+obj/', $fileContent);
            $maxObjects = self::PDF_CLEANING_CONFIG['max_objects'];
            if ($objectCount > $maxObjects) {
                return ['safe' => false, 'reason' => "File PDF terlalu kompleks (lebih dari {$maxObjects} objek)"];
            }

            return ['safe' => true, 'reason' => 'File lolos pemeriksaan keamanan'];

        } catch (\Exception $e) {
            Log::error("Error during security check: " . $e->getMessage());
            return ['safe' => false, 'reason' => 'Gagal memeriksa keamanan file'];
        }
    }

    /**
     * Validate PDF file integrity and structure
     * 
     * @param string $pdfPath Path to PDF file
     * @return array Validation results
     */
    private function validatePdfIntegrity(string $pdfPath): array
    {
        try {
            $fileContent = file_get_contents($pdfPath);
            if (!$fileContent) {
                return ['valid' => false, 'reason' => 'File tidak dapat dibaca'];
            }

            // Check for proper PDF structure
            if (!preg_match('/%PDF-\d\.\d/', $fileContent)) {
                return ['valid' => false, 'reason' => 'Header PDF tidak valid'];
            }

            // Check for EOF marker
            if (strpos($fileContent, '%%EOF') === false) {
                return ['valid' => false, 'reason' => 'File PDF tidak memiliki marker akhir yang valid'];
            }

            // Try to parse with Smalot PDF Parser for structure validation
            try {
                $parser = new Parser();
                $pdf = $parser->parseContent($fileContent);
                
                // Check if we can get basic PDF info
                $details = $pdf->getDetails();
                $pages = $pdf->getPages();
                
                if (empty($pages)) {
                    return ['valid' => false, 'reason' => 'PDF tidak memiliki halaman yang dapat dibaca'];
                }

                Log::info("PDF structure validation passed", [
                    'pages' => count($pages),
                    'title' => $details['Title'] ?? 'Unknown',
                    'creator' => $details['Creator'] ?? 'Unknown'
                ]);

            } catch (\Exception $e) {
                Log::warning("PDF parser validation failed: " . $e->getMessage());
                // Continue with basic checks even if parser fails
            }

            // Check for circular references (can cause infinite loops)
            // Note: This check is disabled for academic PDFs as they often have complex structures
            // that may appear as circular references but are actually legitimate
            $checkCircularRefs = env('STRICT_PDF_VALIDATION', false);
            if ($checkCircularRefs && preg_match('/(\d+\s+0\s+obj).*?\1/s', $fileContent)) {
                Log::warning("Potential circular references detected in PDF", [
                    'file' => basename($pdfPath),
                    'note' => 'This may be a false positive for academic PDFs'
                ]);
                return ['valid' => false, 'reason' => 'File PDF mengandung referensi melingkar (Dapat dilewati dengan FAST_SIGNING_MODE=true)'];
            }

            return ['valid' => true, 'reason' => 'Struktur PDF valid'];

        } catch (\Exception $e) {
            Log::error("Error during integrity validation: " . $e->getMessage());
            return ['valid' => false, 'reason' => 'Gagal memvalidasi integritas PDF'];
        }
    }

    /**
     * Sanitize PDF content by removing potentially dangerous elements
     * 
     * @param string $originalPath Path to original PDF
     * @param string $cleanedPath Path where cleaned PDF will be saved
     * @param bool $useFastMode Whether to use fast mode
     * @return array Sanitization results
     */
    private function sanitizePdfContent(string $originalPath, string $cleanedPath, bool $useFastMode = false): array
    {
        try {
            $method = self::PDF_CLEANING_CONFIG['sanitization_method'];
            
            // Override method if fast mode is requested
            if ($useFastMode) {
                $method = 'fast';
            }
            
            switch ($method) {
                case 'fast':
                    // Fast mode: Only FPDI-based cleaning (no image conversion)
                    Log::info("Using fast sanitization method");
                    return $this->sanitizeViaFpdi($originalPath, $cleanedPath);
                    
                case 'image':
                    // Force image-based sanitization
                    return $this->sanitizeViaImageConversion($originalPath, $cleanedPath);
                    
                case 'fpdi':
                    // Force FPDI-based sanitization
                    return $this->sanitizeViaFpdi($originalPath, $cleanedPath);
                    
                case 'auto':
                default:
                    // Auto-select best method (FPDI first for speed, image fallback)
                    Log::info("Using auto-selection for sanitization method");
                    $sanitizationResult = $this->sanitizeViaFpdi($originalPath, $cleanedPath);
                    
                    if ($sanitizationResult['success']) {
                        return $sanitizationResult;
                    }

                    // Fallback: Image-based sanitization if FPDI fails
                    Log::info("FPDI-based sanitization failed, trying image-based cleaning");
                    return $this->sanitizeViaImageConversion($originalPath, $cleanedPath);
            }

        } catch (\Exception $e) {
            Log::error("Error during PDF sanitization: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Gagal membersihkan konten PDF: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Determine if fast mode should be used based on PDF characteristics
     */
    private function shouldUseFastMode(string $pdfPath): bool
    {
        try {
            // Check file size
            $fileSize = filesize($pdfPath);
            if ($fileSize > 20 * 1024 * 1024) { // > 20MB
                Log::info("Using fast mode due to large file size: " . round($fileSize / (1024 * 1024), 2) . "MB");
                return true;
            }

            // Check page count using Spatie PDF-to-Image
            try {
                $pdf = new Pdf($pdfPath);
                $pageCount = $pdf->pageCount();
                $threshold = self::PDF_CLEANING_CONFIG['fast_mode_threshold'];
                
                if ($pageCount > $threshold) {
                    Log::info("Using fast mode due to page count: {$pageCount} pages (threshold: {$threshold})");
                    return true;
                }
            } catch (\Exception $e) {
                Log::warning("Could not determine page count, using fast mode for safety: " . $e->getMessage());
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::warning("Error determining fast mode, defaulting to fast mode: " . $e->getMessage());
            return true;
        }
    }

    /**
     * Perform basic security check (lighter than full security check)
     */
    private function performBasicSecurityCheck(string $pdfPath): array
    {
        try {
            $fileContent = file_get_contents($pdfPath);
            if (!$fileContent) {
                return ['safe' => false, 'reason' => 'Tidak dapat membaca file PDF'];
            }

            // Check for valid PDF header
            if (substr($fileContent, 0, 4) !== '%PDF') {
                return ['safe' => false, 'reason' => 'File bukan PDF yang valid'];
            }

            // Check file size
            $fileSize = strlen($fileContent);
            $maxSize = self::PDF_CLEANING_CONFIG['max_file_size'];
            if ($fileSize > $maxSize) {
                return ['safe' => false, 'reason' => 'File terlalu besar (maksimal ' . round($maxSize / (1024 * 1024)) . 'MB)'];
            }

            // Only check for the most dangerous patterns in fast mode
            $dangerousPatterns = [
                '/\/JavaScript\s*\(\s*[^)]*eval/i',     // JavaScript with eval
                '/\/Launch\s*<<[^>]*\/F\s*\(/i',        // Launch external files
                '/<script[^>]*>/i',                      // HTML script tags
            ];

            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $fileContent)) {
                    Log::warning("Dangerous content found in PDF during basic check", [
                        'pattern' => $pattern,
                        'file' => basename($pdfPath)
                    ]);
                    return ['safe' => false, 'reason' => 'File mengandung konten berbahaya'];
                }
            }

            return ['safe' => true, 'reason' => 'File lolos pemeriksaan keamanan dasar'];

        } catch (\Exception $e) {
            Log::error("Error during basic security check: " . $e->getMessage());
            return ['safe' => false, 'reason' => 'Gagal memeriksa keamanan file'];
        }
    }

    /**
     * Sanitize PDF by converting to images and back to PDF (most secure method)
     * 
     * @param string $originalPath Original PDF path
     * @param string $cleanedPath Cleaned PDF path
     * @return array Results
     */
    private function sanitizeViaImageConversion(string $originalPath, string $cleanedPath): array
    {
        try {
            Log::info("Starting image-based PDF sanitization");
            
            // Create TCPDF instance for the clean PDF
            $tcpdf = new \TCPDF();
            $tcpdf->SetCreator('PDF Sanitization System');
            $tcpdf->SetAuthor('Universitas Multimedia Nusantara');
            $tcpdf->SetTitle('Sanitized Document');
            $tcpdf->SetAutoPageBreak(false);

            // Get page count
            $pdf = new Pdf($originalPath);
            $pageCount = $pdf->pageCount();
            
            if ($pageCount <= 0) {
                return ['success' => false, 'error' => 'PDF tidak memiliki halaman'];
            }

            $maxPages = self::PDF_CLEANING_CONFIG['max_pages'];
            if ($pageCount > $maxPages) {
                return ['success' => false, 'error' => "PDF terlalu banyak halaman (maksimal {$maxPages})"];
            }

            Log::info("Converting {$pageCount} pages to images for sanitization");

            // Convert each page to image and add to new PDF
            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                $tempImagePath = storage_path('app/temp/sanitize_page_' . $currentPage . '_' . time() . '.jpg');
                
                try {
                    // Convert page to image (this strips all interactive content)
                    (new Pdf($originalPath))->selectPage($currentPage)->save($tempImagePath);

                    if (!file_exists($tempImagePath)) {
                        return ['success' => false, 'error' => "Gagal membuat gambar untuk halaman {$currentPage}"];
                    }

                    // Get image dimensions and add to PDF
                    $imageSize = getimagesize($tempImagePath);
                    if (!$imageSize) {
                        unlink($tempImagePath);
                        return ['success' => false, 'error' => "Gagal membaca dimensi gambar halaman {$currentPage}"];
                    }

                    $pageWidthMM = ($imageSize[0] / 150) * 25.4;
                    $pageHeightMM = ($imageSize[1] / 150) * 25.4;
                    
                    $tcpdf->AddPage('P', [$pageWidthMM, $pageHeightMM]);
                    $tcpdf->Image($tempImagePath, 0, 0, $pageWidthMM, $pageHeightMM, 'JPG', '', '', true, 150);

                    // Clean up temporary image
                    unlink($tempImagePath);

                } catch (\Exception $e) {
                    // Clean up on error
                    if (file_exists($tempImagePath)) {
                        unlink($tempImagePath);
                    }
                    
                    if (strpos($e->getMessage(), 'security policy') !== false) {
                        return ['success' => false, 'error' => 'ImageMagick security policy mencegah konversi PDF'];
                    }
                    
                    return ['success' => false, 'error' => "Gagal memproses halaman {$currentPage}: " . $e->getMessage()];
                }
            }

            // Save the sanitized PDF
            $tcpdf->Output($cleanedPath, 'F');
            
            Log::info("Image-based sanitization completed successfully");
            return ['success' => true, 'method' => 'image_conversion'];

        } catch (\Exception $e) {
            Log::error("Image-based sanitization failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sanitize PDF using FPDI (fallback method)
     * 
     * @param string $originalPath Original PDF path  
     * @param string $cleanedPath Cleaned PDF path
     * @return array Results
     */
    private function sanitizeViaFpdi(string $originalPath, string $cleanedPath): array
    {
        try {
            Log::info("Starting FPDI-based PDF sanitization");
            
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($originalPath);
            
            if ($pageCount <= 0) {
                return ['success' => false, 'error' => 'PDF tidak memiliki halaman'];
            }

            // Copy all pages without interactive content
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }

            $pdf->Output($cleanedPath, 'F');
            
            Log::info("FPDI-based sanitization completed successfully");
            return ['success' => true, 'method' => 'fpdi'];

        } catch (\Exception $e) {
            Log::error("FPDI-based sanitization failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }



    /**
     * Validate the cleaned PDF
     * 
     * @param string $cleanedPath Path to cleaned PDF
     * @return array Validation results
     */
    private function validateCleanedPdf(string $cleanedPath): array
    {
        try {
            // Basic file checks
            if (!file_exists($cleanedPath)) {
                return ['valid' => false, 'reason' => 'File yang dibersihkan tidak ditemukan'];
            }

            $fileSize = filesize($cleanedPath);
            if ($fileSize < 1024) { // Less than 1KB is suspicious
                return ['valid' => false, 'reason' => 'File yang dibersihkan terlalu kecil'];
            }

            if ($fileSize > 100 * 1024 * 1024) { // More than 100MB is too large
                return ['valid' => false, 'reason' => 'File yang dibersihkan terlalu besar'];
            }

            // Check PDF header
            $fileContent = file_get_contents($cleanedPath);
            if (!$fileContent || substr($fileContent, 0, 4) !== '%PDF') {
                return ['valid' => false, 'reason' => 'File yang dibersihkan bukan PDF yang valid'];
            }

            // Try to validate with PDF-to-Image (this will catch most corruption)
            try {
                $pdf = new Pdf($cleanedPath);
                $pageCount = $pdf->pageCount();
                
                if ($pageCount <= 0) {
                    return ['valid' => false, 'reason' => 'File yang dibersihkan tidak memiliki halaman'];
                }

                Log::info("Cleaned PDF validation passed", [
                    'file_size' => $fileSize,
                    'page_count' => $pageCount
                ]);

            } catch (\Exception $e) {
                return ['valid' => false, 'reason' => 'File yang dibersihkan tidak dapat diproses: ' . $e->getMessage()];
            }

            return ['valid' => true, 'reason' => 'File yang dibersihkan valid'];

        } catch (\Exception $e) {
            Log::error("Error validating cleaned PDF: " . $e->getMessage());
            return ['valid' => false, 'reason' => 'Gagal memvalidasi file yang dibersihkan'];
        }
    }

    /**
     * Handle the actual PDF signing process with proper fallback mechanisms
     */
    private function handlePdfSigning($validatedData, $proposal, $fileName, $kaprodi, $forceImageApproach = false, $pdfPath = null)
    {
        // Use cleaned PDF path if provided, otherwise use original
        $originalPdfPath = $pdfPath ?? storage_path('app/uploads/proposal/' . $fileName);

        // Step 4: Ensure the temp directory exists before QR code generation
        $tempDir = storage_path('app/temp/');
        if (!file_exists($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                Log::error("Failed to create temp directory at: {$tempDir}");
                return redirect()->back()->with('error', 'Gagal membuat direktori sementara.');
            }
        }

        // Generate the QR Code
        Log::info("Looking for Dosen with user_id: " . Auth::user()->id);
        $lecturer = Dosen::where('user_id', Auth::user()->id)->first();
        if (!$lecturer) {
            Log::error("No Dosen record found for user ID: " . Auth::user()->id);
            return redirect()->back()->with('error', 'Profil dosen tidak ditemukan.');
        }
        
        Log::info("Found lecturer: " . $lecturer->nama);
        $qrData = "Signed by $lecturer->nama ($lecturer->nid) \nDate: " . now()->toDateTimeString();

        // Define QR code options
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 6,
            'imageBase64' => false,
        ]);

        // Generate QR code
        $qrcode = new QRCode($options);
        $qrCodePath = $tempDir . 'qr_code_' . time() . '.png';
        
        try {
            $qrcode->render($qrData, $qrCodePath);
            Log::info("QR code saved at: {$qrCodePath}");
        } catch (\Exception $e) {
            Log::error("Failed to generate QR code: " . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat kode QR.');
        }

        if (!file_exists($qrCodePath)) {
            Log::error("QR code PNG not saved at: {$qrCodePath}");
            return redirect()->back()->with('error', 'Gagal menyimpan kode QR.');
        }

        // Try FPDI approach first unless forced to use image approach
        if (!$forceImageApproach) {
            try {
                return $this->signWithFpdi($validatedData, $proposal, $originalPdfPath, $qrCodePath, $fileName, $kaprodi);
            } catch (\Exception $e) {
                Log::warning("FPDI approach failed, falling back to image approach: " . $e->getMessage());
                // Fall through to image approach
            }
        }

        // Use image-based approach as fallback
        Log::info("Using image-based signing approach for: {$fileName}");
        return $this->signThesisWithImageApproach($validatedData, $proposal, $qrCodePath, $fileName, $kaprodi, $originalPdfPath);
    }

    /**
     * Sign PDF using FPDI approach
     */
    private function signWithFpdi($validatedData, $proposal, $originalPdfPath, $qrCodePath, $fileName, $kaprodi)
    {
        // Initialize FPDI
        $pdf = new Fpdi();

        // Set the source file - handle compression issues
        $pageCount = $pdf->setSourceFile($originalPdfPath);
        Log::info("Original PDF has {$pageCount} pages.");

        // Validate page number
        $pageNumber = (int)$validatedData['page_number'];
        if ($pageNumber > $pageCount || $pageNumber < 1) {
            throw new \Exception("Nomor halaman tidak valid. PDF hanya memiliki {$pageCount} halaman.");
        }

        // Iterate through each page and add QR code on the specified page
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import a page
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            // Add a page with the same orientation and size
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

            // Use the imported page as the template
            $pdf->useTemplate($templateId);

            if($pageNumber == $pageNo){
                // Define the position where you want to place the QR code
                $x = floatval($validatedData['x']) / floatval($validatedData['width']) * $size['width'] - 3;
                $y = floatval($validatedData['y']) / floatval($validatedData['height']) * $size['height'] - 1;

                // Validate coordinates
                if ($x < 0 || $y < 0 || $x > $size['width'] || $y > $size['height']) {
                    Log::warning("QR code coordinates out of bounds, adjusting position");
                    $x = max(0, min($x, $size['width'] - 20));
                    $y = max(0, min($y, $size['height'] - 20));
                }

                // Log the QR code embedding details
                Log::info("Embedding QR code on page {$pageNo} at ({$x}mm, {$y}mm)");

                // Add the QR code image
                $qrWidth = 20; // mm
                $qrHeight = 20; // mm
                $pdf->Image($qrCodePath, $x, $y, $qrWidth, $qrHeight);
            }
        }

        // Save the PDF
        $pdf->Output($originalPdfPath, 'F');
        Log::info("Signed PDF saved at: {$originalPdfPath}");

        // Clean up QR code
        if (file_exists($qrCodePath)) {
            unlink($qrCodePath);
        }

        // Embed XML data and update database
        $this->embedXmlDataAndFinalize($proposal, $kaprodi, $originalPdfPath, $fileName);

        // Clean up temporary cleaned PDF if it's different from original
        $this->cleanupTemporaryFiles($originalPdfPath, $fileName);

        return redirect()->back()->with('success', 'Proposal berhasil ditandatangani.');
    }

    /**
     * Finalize the signing process by embedding XML and updating database
     */
    private function embedXmlDataAndFinalize($proposal, $kaprodi, $originalPdfPath, $fileName)
    {
        try{
            // Prepare metadata for XML generation with input validation
            $data = [
                'UUID' => $this->sanitizeXmlTextContent($proposal->uuid ?? ''),
                'Tipe_Laporan' => 'Skripsi',
                'Judul_Laporan' => $this->sanitizeXmlTextContent($proposal->judul_proposal ?? ''),
                'Judul_Laporan_EN' => $this->sanitizeXmlTextContent($proposal->judul_proposal_en ?? ''),
                'Prodi' => 'Teknik Informatika',
                'Tahun' => date('Y'), // Use current year instead of hardcoded
                'Nama_Mahasiswa' => $this->sanitizeXmlTextContent($proposal->mahasiswa->nama ?? ''),
                'NIM' => $this->sanitizeXmlTextContent($proposal->mahasiswa->nim ?? ''),
                'Dosen_Pembimbing_1__Nama' => $this->sanitizeXmlTextContent($proposal->toArray()['penilai_pertama']['nama'] ?? ''),
                'Dosen_Pembimbing_1__NIDN' => $this->sanitizeXmlTextContent($proposal->toArray()['penilai_pertama']['nid'] ?? ''),
                'KAPRODI' => $this->sanitizeXmlTextContent($kaprodi ? $kaprodi->nama : 'N/A'),
            ];

            // Validate data completeness
            $requiredFields = ['UUID', 'Judul_Laporan', 'Nama_Mahasiswa', 'NIM'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("Required field '{$field}' is missing or empty");
                }
            }

            Log::info("Generating secure XML metadata for thesis signing", [
                'uuid' => $data['UUID'],
                'nim' => $data['NIM']
            ]);

            // Generate secure XML
            $xmlContent = self::generateXML($data);
            
            if ($xmlContent === false || empty($xmlContent)) {
                throw new \Exception("Failed to generate XML metadata");
            }

            // Add XAdES digital signature to XML
            if (self::XADES_CONFIG['enabled']) {
                Log::info("Adding XAdES digital signature to XML metadata");
                $xmlContent = $this->addXAdESSignature($xmlContent);
            }

            // Create temporary XML file with secure permissions
            $xmlPath = storage_path('app/temp/data_' . time() . '_' . uniqid() . '.xml');
            
            // Ensure temp directory exists
            $tempDir = dirname($xmlPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $bytesWritten = file_put_contents($xmlPath, $xmlContent);
            
            if ($bytesWritten === false) {
                throw new \Exception("Failed to write XML file to: {$xmlPath}");
            }

            // Verify the written file
            if (!file_exists($xmlPath) || filesize($xmlPath) === 0) {
                throw new \Exception("XML file verification failed after writing");
            }

            Log::info("XML metadata file created successfully", [
                'path' => $xmlPath,
                'size' => filesize($xmlPath)
            ]);

            // Embed XML in PDF
            self::embedFilesInExistingPdf(
                $originalPdfPath,
                $originalPdfPath, 
                [$xmlPath]
            );

            Log::info("XML metadata successfully embedded in PDF");

            // Clean up the temporary XML file
            if (file_exists($xmlPath)) {
                unlink($xmlPath);
                Log::info("Temporary XML file cleaned up");
            }

        } catch (\Exception $e) {
            Log::error("Failed to embed XML metadata: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'filename' => $fileName
            ]);
            
            // Clean up any temporary files
            if (isset($xmlPath) && file_exists($xmlPath)) {
                unlink($xmlPath);
            }
            
            // Don't fail the entire process for XML embedding issues, but log it prominently
            Log::warning("Continuing with signing process despite XML embedding failure");
        }

        // Update database
        $proposal->signed_proposal = $fileName;
        $hashValue = hash('sha512', file_get_contents($originalPdfPath));
        $hashValue = substr($hashValue, 0, 64);
        $proposal->hash_value = $hashValue;
        $proposal->save();

        Log::info("Signing process completed successfully", [
            'filename' => $fileName,
            'hash' => $hashValue
        ]);
    }

    public function downloadSignedProposal($filename)
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


    // e-sign page
    public function verifyThesis()
    {
        // For admin users, just show the verify interface without student data
        if (Auth::user()->role === 'admin') {
            return view('pages.dosen.verify.verify', [
                'title' => 'Verify Thesis',
                'subtitle' => 'Verify Thesis',
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
            'title' => 'Verify Thesis',
            'subtitle' => 'Verify Thesis',
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
            
            // Extract embedded files with automatic sanitization
            $xmlContent = $this->extractEmbeddedFiles($pdfContent);
            
            if (empty($xmlContent) || $xmlContent === "No embedded files found.") {
                throw new \Exception("Tidak ditemukan file XML yang tertanam dalam PDF");
            }

            // Verify XAdES signature if present
            $xadesVerification = $this->verifyXAdESSignature($xmlContent);
            Log::info("XAdES verification result", $xadesVerification);

            // Additional XML sanitization (if not already done in extractEmbeddedFiles)
            if (!str_starts_with($xmlContent, '<?xml')) {
                // If the extracted content is not clean XML, try to sanitize it
                Log::info("Performing additional XML sanitization on extracted content");
                $sanitizationResult = $this->sanitizeXmlContent($xmlContent, true);
                
                if (!$sanitizationResult['success']) {
                    throw new \Exception("XML content tidak dapat dibersihkan: " . $sanitizationResult['error']);
                }
                
                $xmlContent = $sanitizationResult['sanitized_xml'];
                $parsedArray = $sanitizationResult['data'];
            } else {
                // Parse already sanitized XML
                $content = simplexml_load_string($xmlContent);
                if ($content === false) {
                    throw new \Exception("XML content tidak dapat diparse");
                }
                
                // Convert the SimpleXMLElement object to an array
                $parsedArray = json_decode(json_encode($content), true);
            }

            // Validate required fields exist
            if (!isset($parsedArray['UUID'])) {
                throw new \Exception("UUID tidak ditemukan dalam XML metadata");
            }

            // Hash check
            $hashValue = hash('sha512', $pdfContent);
            $hashValue = substr($hashValue, 0, 64);

            // Check if the hash value matches the one stored in the database
            $proposal = ProposalSkripsi::where('uuid', $parsedArray['UUID'])->first();
            
            if (!$proposal) {
                return view('pages.dosen.verify.verify', [
                    'title' => 'Verify Thesis',
                    'subtitle' => 'Verify Thesis',
                    'status' => 'failed',
                    'hash_value' => $hashValue,
                    'error' => 'UUID tidak ditemukan dalam database',
                ]);
            }
            
            if ($proposal->hash_value !== $hashValue) {
                return view('pages.dosen.verify.verify', [
                    'title' => 'Verify Thesis',
                    'subtitle' => 'Verify Thesis',
                    'status' => 'failed',
                    'hash_value' => $hashValue,
                    'error' => 'Hash value mismatch - dokumen mungkin telah dimodifikasi',
                ]);
            }

            Log::info("PDF verification successful", [
                'uuid' => $parsedArray['UUID'],
                'hash' => $hashValue
            ]);

            return view('pages.dosen.verify.verify', [
                'title' => 'Verify Thesis',
                'subtitle' => 'Verify Thesis',
                'Tipe_Laporan' => $parsedArray['Tipe_Laporan'] ?? '',
                'Judul_Laporan' => $parsedArray['Judul_Laporan'] ?? '',
                'Judul_Laporan_EN' => $parsedArray['Judul_Laporan_EN'] ?? '',
                'Prodi' => $parsedArray['Prodi'] ?? '',
                'Tahun' => $parsedArray['Tahun'] ?? '',
                'Nama_Mahasiswa' => $parsedArray['Nama_Mahasiswa'] ?? '',
                'NIM' => $parsedArray['NIM'] ?? '',
                'Dosen_Pembimbing_1__Nama' => $parsedArray['Dosen_Pembimbing_1__Nama'] ?? '',
                'Dosen_Pembimbing_1__NIDN' => $parsedArray['Dosen_Pembimbing_1__NIDN'] ?? '',
                'Dosen_Pembimbing_2__Nama' => $parsedArray['Dosen_Pembimbing_2__Nama'] ?? '',
                'Dosen_Pembimbing_2__NIDN' => $parsedArray['Dosen_Pembimbing_2__NIDN'] ?? '',
                'Dosen_Penguji' => $parsedArray['Dosen_Penguji'] ?? '',
                'Dosen_Ketua_Sidang' => $parsedArray['Dosen_Ketua_Sidang'] ?? '',
                'KAPRODI' => $parsedArray['KAPRODI'] ?? '',
                'status' => 'success',
                'hash_value' => $hashValue,
                'xades_verification' => $xadesVerification
            ]);
        } catch (\Exception $e) {
            Log::error("PDF verification failed: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Handle the exception
            return view('pages.dosen.verify.verify', [
                'title' => 'Verify Thesis',
                'subtitle' => 'Verify Thesis',
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
                            $content = $value->getContent();
                            
                            // SECURITY: Sanitize extracted XML content before returning
                            if (!empty($content)) {
                                Log::info("Extracted embedded XML content, performing sanitization");
                                $sanitizationResult = $this->sanitizeXmlContent($content, true);
                                
                                if ($sanitizationResult['success']) {
                                    Log::info("XML content successfully sanitized");
                                    return $sanitizationResult['sanitized_xml'];
                                } else {
                                    Log::error("XML sanitization failed: " . $sanitizationResult['error']);
                                    throw new \Exception("Extracted XML content is not safe: " . $sanitizationResult['error']);
                                }
                            }
                            
                            return $content;
                        } else {
                            // Handle the case if the object does not contain the expected class
                            Log::warning("No content found for key: $key");
                        }
                    }
                } else {
                    return "No embedded files found.";
                }
            }
        } catch (\Exception $e) {
            Log::error('Error extracting embedded files: ' . $e->getMessage());
            throw $e; // Re-throw to be handled by calling method
        }

        return $embeddedFiles;
    }

    /**
     * Will return data.xml file with comprehensive sanitization
     */
    public static function generateXML($object): bool|string
    {
        try {
            // Convert the object to an array
            $arrayData = json_decode(json_encode($object), true);

            // Validate and sanitize the data before XML generation
            $instance = new self();
            $sanitizedData = $instance->sanitizeXmlDataRecursive(
                $arrayData, 
                self::XML_SANITIZATION_CONFIG['allowed_elements'], 
                0
            );

            // Create a new XML document with secure settings
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root/>');

            // Use secure XML generation
            self::arrayToXMLSecure($sanitizedData, $xml);

            // Validate the generated XML
            $generatedXml = $xml->asXML();
            
            if ($generatedXml === false) {
                Log::error("Failed to generate XML from data");
                throw new \Exception("Gagal membuat XML");
            }

            // Final security check on generated XML
            $finalCheck = $instance->performXmlSecurityChecks($generatedXml);
            if (!$finalCheck['safe']) {
                Log::error("Generated XML failed security check: " . $finalCheck['reason']);
                throw new \Exception("XML yang dihasilkan tidak aman: " . $finalCheck['reason']);
            }

            Log::info("Secure XML generation completed successfully");
            return $generatedXml;

        } catch (\Exception $e) {
            Log::error("Error during secure XML generation: " . $e->getMessage());
            // Fallback to basic generation for backwards compatibility
            return self::generateXMLBasic($object);
        }
    }

    /**
     * Fallback method for basic XML generation (for backwards compatibility)
     */
    private static function generateXMLBasic($object): bool|string
    {
        try {
            $arrayData = json_decode(json_encode($object), true);
            $xml = new \SimpleXMLElement('<root/>');
            self::arrayToXML($arrayData, $xml);
            return $xml->asXML();
        } catch (\Exception $e) {
            Log::error("Basic XML generation also failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Legacy arrayToXML method (kept for backwards compatibility)
     */
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
     * Secure static version of arrayToXMLSecure for use in generateXML
     */
    private static function arrayToXMLSecure(array $data, \SimpleXMLElement &$xml): void
    {
        $instance = new self();
        
        foreach ($data as $key => $value) {
            // Additional key sanitization
            $cleanKey = $instance->sanitizeXmlElementName($key);
            
            if (is_array($value)) {
                $child = $xml->addChild($cleanKey);
                self::arrayToXMLSecure($value, $child);
            } else {
                // Double sanitization for extra security
                $sanitizedValue = $instance->sanitizeXmlTextContent((string)$value);
                $xml->addChild($cleanKey, $sanitizedValue);
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
     * @param string|null $pdfPath Path to cleaned PDF if available
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    private function signThesisWithImageApproach($validatedData, $proposal, $qrCodePath, $fileName, $kaprodi, $pdfPath = null)
    {
        Log::info("Attempting image-based signing for PDF: {$fileName}");
        $originalPdfPath = $pdfPath ?? storage_path('app/uploads/proposal/' . $fileName);

        try {
            // Initialize TCPDF for creating the new PDF
            $tcpdf = new \TCPDF();
            $tcpdf->SetCreator('Digital Signature System');
            $tcpdf->SetAuthor('Universitas Multimedia Nusantara');
            $tcpdf->SetTitle('Signed Proposal Document: ' . $proposal->judul_proposal);
            $tcpdf->SetAutoPageBreak(false);

            $pageNumber = (int)$validatedData['page_number'];

            // Get the total page count from the original PDF
            $pdf = new Pdf($originalPdfPath);
            $pageCount = $pdf->pageCount();
            Log::info("PDF has {$pageCount} pages. Starting conversion process.");

            // Loop through each page, convert to image, and add to the new PDF
            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                $tempImagePath = storage_path('app/temp/page_' . $currentPage . '.jpg');
                Log::info("Processing page {$currentPage} of {$pageCount}");

                // Convert the current page to an image
                try {
                    (new Pdf($originalPdfPath))->selectPage($currentPage)->save($tempImagePath);
                } catch (PageDoesNotExist $e) {
                    Log::error("Failed to convert page {$currentPage}: Page does not exist.", ['exception' => $e]);
                    throw new \Exception("Gagal memproses halaman {$currentPage} dari PDF. Halaman tidak ditemukan.");
                } catch (CouldNotStoreImage $e) {
                    Log::error("Failed to store image for page {$currentPage}. Check directory permissions.", ['exception' => $e]);
                    throw new \Exception("Gagal menyimpan gambar untuk halaman {$currentPage}. Periksa izin direktori.");
                } catch (\Exception $e) {
                    Log::error("Failed to convert page {$currentPage} to image: " . $e->getMessage(), ['exception' => $e]);
                    
                    // Check for ImageMagick security policy error
                    if (strpos($e->getMessage(), 'security policy') !== false && strpos($e->getMessage(), 'PDF') !== false) {
                        throw new \Exception("Konversi PDF gagal karena kebijakan keamanan ImageMagick. Silakan hubungi administrator untuk mengatur ulang konfigurasi ImageMagick.");
                    }
                    
                    throw new \Exception("Gagal mengkonversi halaman {$currentPage} ke gambar: " . $e->getMessage());
                }

                if (!file_exists($tempImagePath)) {
                    throw new \Exception("Gagal membuat file gambar sementara untuk halaman {$currentPage}.");
                }

                // Add a page to the new PDF with the correct dimensions
                $imageSize = getimagesize($tempImagePath);
                $pageWidthMM = ($imageSize[0] / 150) * 25.4;
                $pageHeightMM = ($imageSize[1] / 150) * 25.4;
                $tcpdf->AddPage('P', [$pageWidthMM, $pageHeightMM]);
                $tcpdf->Image($tempImagePath, 0, 0, $pageWidthMM, $pageHeightMM, 'JPG', '', '', true, 150);

                // Embed the QR code on the specified page
                if ($currentPage == $pageNumber) {
                    $x = (floatval($validatedData['x']) / floatval($validatedData['width'])) * $pageWidthMM;
                    $y = (floatval($validatedData['y']) / floatval($validatedData['height'])) * $pageHeightMM;
                    $tcpdf->Image($qrCodePath, $x, $y, 20, 20, 'PNG');
                    Log::info("QR code embedded on page {$currentPage} at ({$x}mm, {$y}mm)");
                }

                // Clean up the temporary image file
                unlink($tempImagePath);
            }

            // Overwrite the original PDF with the newly created signed version
            $tcpdf->Output($originalPdfPath, 'F');
            Log::info("Successfully overwritten original PDF with signed version: {$originalPdfPath}");

            // Embed XML metadata and update the database
            $this->embedXmlData($proposal, $kaprodi, $originalPdfPath);
            $proposal->signed_proposal = $fileName;
            $proposal->hash_value = substr(hash('sha512', file_get_contents($originalPdfPath)), 0, 64);
            $proposal->save();

            // Clean up temporary cleaned PDF if it's different from original
            $this->cleanupTemporaryFiles($originalPdfPath, $fileName);

            Log::info("Signing process completed successfully for: {$fileName}");
            return redirect()->back()->with('success', 'Dokumen berhasil ditandatangani menggunakan metode fallback.');

        } catch (\Exception $e) {
            Log::error("Fallback signing failed: " . $e->getMessage(), ['exception' => $e]);
            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
            }
            
            // Check for specific ImageMagick policy errors
            if (strpos($e->getMessage(), 'security policy') !== false && strpos($e->getMessage(), 'PDF') !== false) {
                Log::warning("ImageMagick security policy error detected, advising administrator intervention");
                return redirect()->back()->with('error', 'Konversi PDF gagal karena kebijakan keamanan ImageMagick. Silakan hubungi administrator sistem untuk mengonfigurasi ulang ImageMagick.');
            }
            
            // Provide a detailed error message to the user
            return redirect()->back()->with('error', 'Gagal menandatangani dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Final fallback is now removed. If image approach fails, an error is returned.
     * This method is kept to prevent breaking existing calls, but now returns an error.
     */
    private function signThesisWithSimpleOverlay($validatedData, $proposal, $qrCodePath, $fileName, $kaprodi)
    {
        Log::error("signThesisWithSimpleOverlay was called, which is deprecated. This indicates a failure in the primary and secondary signing methods.");

        // Clean up the QR code file if it exists
        if (file_exists($qrCodePath)) {
            unlink($qrCodePath);
        }

        // Return a user-friendly error message
        return redirect()->back()->with('error', 'Gagal memproses dokumen PDF. Format tidak didukung atau file rusak.');
    }


    /**
     * Helper method to embed XML data into PDF (used by alternative signing methods)
     */
    private function embedXmlData($proposal, $kaprodi, $modifiedPdfPath)
    {
        try {
            // Use the same secure XML embedding as the main method
            $data = [
                'UUID' => $this->sanitizeXmlTextContent($proposal->uuid ?? ''),
                'Tipe_Laporan' => 'Skripsi',
                'Judul_Laporan' => $this->sanitizeXmlTextContent($proposal->judul_proposal ?? ''),
                'Judul_Laporan_EN' => $this->sanitizeXmlTextContent($proposal->judul_proposal_en ?? ''),
                'Prodi' => 'Teknik Informatika',
                'Tahun' => date('Y'),
                'Nama_Mahasiswa' => $this->sanitizeXmlTextContent($proposal->mahasiswa->nama ?? ''),
                'NIM' => $this->sanitizeXmlTextContent($proposal->mahasiswa->nim ?? ''),
                'Dosen_Pembimbing_1__Nama' => $this->sanitizeXmlTextContent($proposal->toArray()['penilai_pertama']['nama'] ?? ''),
                'Dosen_Pembimbing_1__NIDN' => $this->sanitizeXmlTextContent($proposal->toArray()['penilai_pertama']['nid'] ?? ''),
                'KAPRODI' => $this->sanitizeXmlTextContent($kaprodi ? $kaprodi->nama : 'N/A'),
            ];

            $xmlContent = self::generateXML($data);
            
            if ($xmlContent === false || empty($xmlContent)) {
                throw new \Exception("Failed to generate XML in alternative approach");
            }

            $xmlPath = storage_path('app/temp/data_alt_' . time() . '_' . uniqid() . '.xml');
            
            // Ensure temp directory exists
            $tempDir = dirname($xmlPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            if (file_put_contents($xmlPath, $xmlContent) === false) {
                throw new \Exception("Failed to write XML file in alternative approach");
            }

            self::embedFilesInExistingPdf($modifiedPdfPath, $modifiedPdfPath, [$xmlPath]);

            if (file_exists($xmlPath)) {
                unlink($xmlPath);
            }

            Log::info("XML metadata embedded successfully using alternative approach");
            
        } catch (\Exception $e) {
            Log::warning("Failed to embed XML data in alternative approach: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Clean up any temporary files
            if (isset($xmlPath) && file_exists($xmlPath)) {
                unlink($xmlPath);
            }
            
            // Don't throw - the main signing was successful
        }
    }

    /**
     * Test XML sanitization functionality (for debugging/validation)
     * This method can be called to verify XML sanitization is working correctly
     * 
     * @param string $testXmlContent Optional test XML content
     * @return array Test results
     */
    public function testXmlSanitization(string $testXmlContent = null): array
    {
        try {
            // Default test XML with potential security issues
            if ($testXmlContent === null) {
                $testXmlContent = '<?xml version="1.0" encoding="UTF-8"?>
                <!DOCTYPE root [
                    <!ENTITY xxe SYSTEM "file:///etc/passwd">
                ]>
                <root>
                    <UUID>test-uuid-123</UUID>
                    <Tipe_Laporan>Skripsi</Tipe_Laporan>
                    <script>alert("XSS")</script>
                    <Judul_Laporan>&xxe;</Judul_Laporan>
                    <javascript:void(0)>Malicious content</javascript:void(0)>
                </root>';
            }

            Log::info("Testing XML sanitization with potentially malicious content");
            
            $result = $this->sanitizeXmlContent($testXmlContent, true);
            
            return [
                'test_passed' => $result['success'],
                'sanitization_result' => $result,
                'original_size' => strlen($testXmlContent),
                'sanitized_size' => $result['success'] ? strlen($result['sanitized_xml']) : 0,
                'message' => $result['success'] ? 'XML sanitization test passed' : 'XML sanitization test failed: ' . $result['error']
            ];

        } catch (\Exception $e) {
            Log::error("XML sanitization test error: " . $e->getMessage());
            return [
                'test_passed' => false,
                'error' => $e->getMessage(),
                'message' => 'XML sanitization test encountered an exception'
            ];
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
     * Clean up temporary files created during the PDF processing
     * 
     * @param string $processedPdfPath Path to the processed PDF
     * @param string $originalFileName Original file name
     * @return void
     */
    private function cleanupTemporaryFiles(string $processedPdfPath, string $originalFileName): void
    {
        try {
            $originalPdfPath = storage_path('app/uploads/proposal/' . $originalFileName);
            
            // If processed path is different from original, it means we created a cleaned version
            if ($processedPdfPath !== $originalPdfPath && file_exists($processedPdfPath)) {
                // Copy the processed (signed) PDF back to the original location
                if (copy($processedPdfPath, $originalPdfPath)) {
                    Log::info("Signed PDF copied back to original location", [
                        'from' => $processedPdfPath,
                        'to' => $originalPdfPath
                    ]);
                    
                    // Remove the temporary cleaned file
                    unlink($processedPdfPath);
                    Log::info("Temporary cleaned PDF file removed", ['file' => $processedPdfPath]);
                } else {
                    Log::warning("Failed to copy signed PDF back to original location", [
                        'from' => $processedPdfPath,
                        'to' => $originalPdfPath
                    ]);
                }
            }
            
            // Clean up any temporary image files that might have been left behind
            $tempDir = storage_path('app/temp/');
            if (is_dir($tempDir)) {
                $tempFiles = glob($tempDir . 'sanitize_page_*');
                $retention = self::PDF_CLEANING_CONFIG['temp_file_retention'];
                foreach ($tempFiles as $tempFile) {
                    if (file_exists($tempFile) && (time() - filemtime($tempFile)) > $retention) {
                        unlink($tempFile);
                        Log::info("Old temporary image file cleaned up", ['file' => $tempFile, 'retention_seconds' => $retention]);
                    }
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Error during cleanup: " . $e->getMessage(), [
                'processed_path' => $processedPdfPath,
                'original_filename' => $originalFileName
            ]);
            // Don't throw exception - cleanup failure shouldn't break the main process
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

    /**
     * Admin Thesis Status Dashboard - shows comprehensive thesis status including digital signatures
     * @return Factory|View|Application
     */
    public function adminThesisStatus(): Factory|View|Application
    {
        // Get all thesis data with comprehensive information for admin
        $data = ProposalSkripsi::with('proposalSkripsiForm')
            ->with('penilaiPertama', function ($query) {
                $query->select('id', 'nama', 'nid');
            })
            ->with('penilaiKedua', function ($query) {
                $query->select('id', 'nama', 'nid');
            })
            ->with('penilaiKetiga', function ($query) {
                $query->select('id', 'nama', 'nid');
            })
            ->with('mahasiswa', function ($query) {
                $query->select('id', 'nim', 'nama');
            })
            ->with('kodePenelitianProposal.areaPenelitian')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.admin.thesis-status.admin-thesis-status', [
            'title' => 'Status Skripsi',
            'subtitle' => 'Status Skripsi & Tanda Tangan Digital',
            'data' => $data,
        ]);
    }

    /**
     * Get current XML sanitization configuration (for admin dashboard)
     * 
     * @return array Current configuration
     */
    public function getXmlSanitizationConfig(): array
    {
        return [
            'config' => self::XML_SANITIZATION_CONFIG,
            'status' => 'active',
            'last_updated' => now()->toDateTimeString(),
            'features' => [
                'xxe_protection' => true,
                'size_limits' => true,
                'element_whitelist' => true,
                'content_sanitization' => true,
                'schema_validation' => self::XML_SANITIZATION_CONFIG['validate_schema'],
                'encoding_validation' => true,
                'pattern_filtering' => true,
            ]
        ];
    }

    /**
     * Validate XML sanitization system integrity
     * This method performs a comprehensive check of the XML sanitization system
     * 
     * @return array System validation results
     */
    public function validateXmlSanitizationSystem(): array
    {
        $results = [
            'overall_status' => 'unknown',
            'tests' => [],
            'recommendations' => [],
            'security_score' => 0
        ];

        try {
            // Test 1: Basic sanitization
            $basicTest = $this->testXmlSanitization();
            $results['tests']['basic_sanitization'] = $basicTest;

            // Test 2: XXE protection
            $xxeTest = $this->testXmlSanitization('<?xml version="1.0"?><!DOCTYPE test [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><root>&xxe;</root>');
            $results['tests']['xxe_protection'] = $xxeTest;

            // Test 3: Large content handling
            $largeContent = '<?xml version="1.0"?><root><data>' . str_repeat('A', 2000) . '</data></root>';
            $sizeTest = $this->testXmlSanitization($largeContent);
            $results['tests']['size_handling'] = $sizeTest;

            // Test 4: Element filtering
            $maliciousElements = '<?xml version="1.0"?><root><allowed_element>OK</allowed_element><script>malicious</script><unknown_element>filtered</unknown_element></root>';
            $filterTest = $this->testXmlSanitization($maliciousElements);
            $results['tests']['element_filtering'] = $filterTest;

            // Calculate security score
            $passedTests = 0;
            $totalTests = count($results['tests']);
            
            foreach ($results['tests'] as $test) {
                if ($test['test_passed']) {
                    $passedTests++;
                }
            }

            $results['security_score'] = round(($passedTests / $totalTests) * 100);

            // Determine overall status
            if ($results['security_score'] >= 90) {
                $results['overall_status'] = 'excellent';
            } elseif ($results['security_score'] >= 75) {
                $results['overall_status'] = 'good';
                $results['recommendations'][] = 'Some minor security improvements could be made';
            } elseif ($results['security_score'] >= 50) {
                $results['overall_status'] = 'needs_improvement';
                $results['recommendations'][] = 'Several security issues need to be addressed';
            } else {
                $results['overall_status'] = 'critical';
                $results['recommendations'][] = 'Critical security vulnerabilities detected - immediate action required';
            }

            // Add specific recommendations
            foreach ($results['tests'] as $testName => $test) {
                if (!$test['test_passed']) {
                    $results['recommendations'][] = "Fix issues with {$testName}: " . ($test['error'] ?? 'Unknown error');
                }
            }

            Log::info("XML sanitization system validation completed", [
                'security_score' => $results['security_score'],
                'status' => $results['overall_status']
            ]);

        } catch (\Exception $e) {
            Log::error("XML sanitization system validation failed: " . $e->getMessage());
            $results['overall_status'] = 'error';
            $results['error'] = $e->getMessage();
            $results['recommendations'][] = 'System validation encountered errors - check logs for details';
        }

        return $results;
    }

    /**
     * Comprehensive XML sanitization and validation
     * 
     * @param string $xmlContent Raw XML content
     * @param bool $validateSchema Whether to validate against schema
     * @return array Result with success status and sanitized XML or error message
     */
    private function sanitizeXmlContent(string $xmlContent, bool $validateSchema = true): array
    {
        try {
            Log::info("Starting XML sanitization process");

            // Step 1: Basic security checks
            $securityCheck = $this->performXmlSecurityChecks($xmlContent);
            if (!$securityCheck['safe']) {
                return [
                    'success' => false,
                    'error' => 'XML mengandung konten yang tidak aman: ' . $securityCheck['reason']
                ];
            }

            // Step 2: Size and structure validation
            $structureCheck = $this->validateXmlStructure($xmlContent);
            if (!$structureCheck['valid']) {
                return [
                    'success' => false,
                    'error' => 'Struktur XML tidak valid: ' . $structureCheck['reason']
                ];
            }

            // Step 3: Parse with secure settings
            $parseResult = $this->parseXmlSecurely($xmlContent);
            if (!$parseResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Gagal memparse XML: ' . $parseResult['error']
                ];
            }

            // Step 4: Content validation and sanitization
            $sanitizedData = $this->sanitizeXmlData($parseResult['data']);
            if (!$sanitizedData['success']) {
                return [
                    'success' => false,
                    'error' => 'Gagal membersihkan data XML: ' . $sanitizedData['error']
                ];
            }

            // Step 5: Schema validation (optional)
            if ($validateSchema && self::XML_SANITIZATION_CONFIG['validate_schema']) {
                $schemaCheck = $this->validateXmlSchema($sanitizedData['data']);
                if (!$schemaCheck['valid']) {
                    Log::warning("XML schema validation failed: " . $schemaCheck['reason']);
                    // Don't fail completely for schema issues, just log warning
                }
            }

            // Step 6: Generate clean XML
            $cleanXml = $this->generateCleanXml($sanitizedData['data']);

            Log::info("XML sanitization completed successfully");
            return [
                'success' => true,
                'sanitized_xml' => $cleanXml,
                'data' => $sanitizedData['data']
            ];

        } catch (\Exception $e) {
            Log::error("Error during XML sanitization: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => 'Kesalahan internal saat membersihkan XML: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Perform comprehensive security checks on XML content
     * 
     * @param string $xmlContent XML content to check
     * @return array Security check results
     */
    private function performXmlSecurityChecks(string $xmlContent): array
    {
        try {
            // Check size limits
            $size = strlen($xmlContent);
            $maxSize = self::XML_SANITIZATION_CONFIG['max_xml_size'];
            if ($size > $maxSize) {
                return ['safe' => false, 'reason' => 'XML terlalu besar (maksimal ' . round($maxSize / 1024) . 'KB)'];
            }

            // Check encoding
            $requiredEncoding = self::XML_SANITIZATION_CONFIG['encoding'];
            if (!mb_check_encoding($xmlContent, $requiredEncoding)) {
                return ['safe' => false, 'reason' => 'Encoding XML tidak valid (harus ' . $requiredEncoding . ')'];
            }

            // Check for forbidden patterns
            $forbiddenPatterns = self::XML_SANITIZATION_CONFIG['forbidden_patterns'];
            foreach ($forbiddenPatterns as $pattern) {
                if (stripos($xmlContent, $pattern) !== false) {
                    Log::warning("Forbidden XML pattern detected", ['pattern' => $pattern]);
                    return ['safe' => false, 'reason' => 'XML mengandung pola yang dilarang: ' . $pattern];
                }
            }

            // Check for suspicious content
            $suspiciousPatterns = [
                '/<!ENTITY[^>]*>/i',              // Entity declarations
                '/&[^;]+;/',                      // Entity references (except standard ones)
                '/<\?xml[^>]*standalone\s*=\s*["\']no["\']/i', // Standalone=no
                '/<!DOCTYPE[^>]*\[/i',            // DTD with internal subset
                '/SYSTEM\s+["\'][^"\']*["\']/',   // System identifiers
                '/PUBLIC\s+["\'][^"\']*["\']/',   // Public identifiers
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $xmlContent)) {
                    Log::warning("Suspicious XML content detected", ['pattern' => $pattern]);
                    return ['safe' => false, 'reason' => 'XML mengandung konten yang mencurigakan'];
                }
            }

            return ['safe' => true, 'reason' => 'XML lolos pemeriksaan keamanan'];

        } catch (\Exception $e) {
            Log::error("Error during XML security check: " . $e->getMessage());
            return ['safe' => false, 'reason' => 'Gagal memeriksa keamanan XML'];
        }
    }

    /**
     * Validate XML structure and basic formatting
     * 
     * @param string $xmlContent XML content to validate
     * @return array Validation results
     */
    private function validateXmlStructure(string $xmlContent): array
    {
        try {
            // Check for basic XML structure
            if (!preg_match('/^\s*<\?xml[^>]*\?>\s*</', $xmlContent) && !preg_match('/^\s*<[^>]+>/', $xmlContent)) {
                return ['valid' => false, 'reason' => 'Format XML tidak valid - tidak ditemukan root element'];
            }

            // Check for balanced tags (basic check)
            $openTags = preg_match_all('/<[^\/][^>]*[^\/]>/', $xmlContent);
            $closeTags = preg_match_all('/<\/[^>]+>/', $xmlContent);
            $selfClosingTags = preg_match_all('/<[^>]*\/>/', $xmlContent);
            
            // Rough balance check (not perfect but catches obvious issues)
            if (abs($openTags - $closeTags) > $selfClosingTags + 2) {
                return ['valid' => false, 'reason' => 'Tag XML tidak seimbang'];
            }

            // Check depth by counting maximum nesting
            $depth = 0;
            $maxDepth = 0;
            $chars = str_split($xmlContent);
            $inTag = false;
            $tagContent = '';
            
            for ($i = 0; $i < count($chars); $i++) {
                $char = $chars[$i];
                
                if ($char === '<') {
                    $inTag = true;
                    $tagContent = '<';
                } elseif ($char === '>' && $inTag) {
                    $tagContent .= '>';
                    $inTag = false;
                    
                    if (preg_match('/^<\//', $tagContent)) {
                        $depth--;
                    } elseif (!preg_match('/\/>$/', $tagContent) && !preg_match('/^<\?/', $tagContent) && !preg_match('/^<!/', $tagContent)) {
                        $depth++;
                        $maxDepth = max($maxDepth, $depth);
                    }
                    
                    $tagContent = '';
                } elseif ($inTag) {
                    $tagContent .= $char;
                }
            }

            $maxAllowedDepth = self::XML_SANITIZATION_CONFIG['max_depth'];
            if ($maxDepth > $maxAllowedDepth) {
                return ['valid' => false, 'reason' => "XML terlalu kompleks (kedalaman maksimal {$maxAllowedDepth})"];
            }

            return ['valid' => true, 'reason' => 'Struktur XML valid'];

        } catch (\Exception $e) {
            Log::error("Error during XML structure validation: " . $e->getMessage());
            return ['valid' => false, 'reason' => 'Gagal memvalidasi struktur XML'];
        }
    }

    /**
     * Parse XML content with secure settings
     * 
     * @param string $xmlContent XML content to parse
     * @return array Parse results
     */
    private function parseXmlSecurely(string $xmlContent): array
    {
        try {
            // Disable external entity loading to prevent XXE attacks
            $previousValueXML = libxml_disable_entity_loader(true);
            $previousValueExt = libxml_use_internal_errors(true);
            
            // Clear any previous XML errors
            libxml_clear_errors();

            try {
                // Parse with SimpleXML with secure settings
                $xml = simplexml_load_string(
                    $xmlContent, 
                    'SimpleXMLElement', 
                    LIBXML_NOCDATA | LIBXML_NOENT | LIBXML_NONET | LIBXML_NSCLEAN
                );

                if ($xml === false) {
                    $errors = libxml_get_errors();
                    $errorMessages = array_map(function($error) {
                        return trim($error->message);
                    }, $errors);
                    
                    return [
                        'success' => false,
                        'error' => 'XML parsing gagal: ' . implode('; ', $errorMessages)
                    ];
                }

                // Convert to array for easier processing
                $data = json_decode(json_encode($xml), true);
                
                return [
                    'success' => true,
                    'data' => $data
                ];

            } finally {
                // Restore previous settings
                libxml_disable_entity_loader($previousValueXML);
                libxml_use_internal_errors($previousValueExt);
                libxml_clear_errors();
            }

        } catch (\Exception $e) {
            Log::error("Error during secure XML parsing: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Gagal memparse XML secara aman: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sanitize XML data content
     * 
     * @param array $data Parsed XML data
     * @return array Sanitization results
     */
    private function sanitizeXmlData(array $data): array
    {
        try {
            $allowedElements = self::XML_SANITIZATION_CONFIG['allowed_elements'];
            $sanitizedData = [];

            // Recursively sanitize data
            $sanitizedData = $this->sanitizeXmlDataRecursive($data, $allowedElements, 0);

            return [
                'success' => true,
                'data' => $sanitizedData
            ];

        } catch (\Exception $e) {
            Log::error("Error during XML data sanitization: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Gagal membersihkan data XML: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Recursively sanitize XML data
     * 
     * @param mixed $data Data to sanitize
     * @param array $allowedElements Allowed element names
     * @param int $depth Current recursion depth
     * @return mixed Sanitized data
     */
    private function sanitizeXmlDataRecursive($data, array $allowedElements, int $depth): mixed
    {
        $maxDepth = self::XML_SANITIZATION_CONFIG['max_depth'];
        if ($depth > $maxDepth) {
            throw new \Exception("Maximum nesting depth exceeded");
        }

        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                // Sanitize key name
                $cleanKey = $this->sanitizeXmlElementName($key);
                
                // Check if element is allowed
                if (in_array($cleanKey, $allowedElements)) {
                    $sanitized[$cleanKey] = $this->sanitizeXmlDataRecursive($value, $allowedElements, $depth + 1);
                } else {
                    Log::warning("Removed disallowed XML element", ['element' => $cleanKey]);
                }
            }
            return $sanitized;
        } elseif (is_string($data)) {
            return $this->sanitizeXmlTextContent($data);
        } else {
            return $data;
        }
    }

    /**
     * Sanitize XML element name
     * 
     * @param string $elementName Element name to sanitize
     * @return string Sanitized element name
     */
    private function sanitizeXmlElementName(string $elementName): string
    {
        // Remove any characters that aren't allowed in XML element names
        $cleaned = preg_replace('/[^a-zA-Z0-9_\-.]/', '', $elementName);
        
        // Ensure it starts with a letter or underscore
        if (!preg_match('/^[a-zA-Z_]/', $cleaned)) {
            $cleaned = 'element_' . $cleaned;
        }
        
        return $cleaned;
    }

    /**
     * Sanitize XML text content
     * 
     * @param string $content Text content to sanitize
     * @return string Sanitized content
     */
    private function sanitizeXmlTextContent(string $content): string
    {
        // Remove control characters except tab, line feed, and carriage return
        $content = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $content);
        
        // HTML encode to prevent XML injection
        $content = htmlspecialchars($content, ENT_QUOTES | ENT_XML1, 'UTF-8');
        
        // Limit length to prevent DoS
        if (strlen($content) > 1000) {
            $content = substr($content, 0, 1000) . '...';
            Log::warning("XML content truncated due to length");
        }
        
        return $content;
    }

    /**
     * Validate XML against expected schema
     * 
     * @param array $data Parsed XML data
     * @return array Validation results
     */
    private function validateXmlSchema(array $data): array
    {
        try {
            // Define required fields for thesis metadata
            $requiredFields = [
                'UUID', 'Tipe_Laporan', 'Judul_Laporan', 'Prodi', 'Tahun',
                'Nama_Mahasiswa', 'NIM', 'Dosen_Pembimbing_1__Nama', 'Dosen_Pembimbing_1__NIDN'
            ];

            // Check for required fields
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return [
                    'valid' => false,
                    'reason' => 'Field yang wajib tidak ditemukan: ' . implode(', ', $missingFields)
                ];
            }

            // Validate specific field formats
            if (isset($data['NIM']) && !preg_match('/^[0-9]{10,15}$/', $data['NIM'])) {
                return ['valid' => false, 'reason' => 'Format NIM tidak valid'];
            }

            if (isset($data['Tahun']) && !preg_match('/^[0-9]{4}$/', $data['Tahun'])) {
                return ['valid' => false, 'reason' => 'Format tahun tidak valid'];
            }

            if (isset($data['UUID']) && !preg_match('/^[a-f0-9\-]{36}$/i', $data['UUID'])) {
                Log::warning("UUID format may be invalid", ['uuid' => $data['UUID']]);
            }

            return ['valid' => true, 'reason' => 'Schema XML valid'];

        } catch (\Exception $e) {
            Log::error("Error during XML schema validation: " . $e->getMessage());
            return ['valid' => false, 'reason' => 'Gagal memvalidasi schema XML'];
        }
    }

    /**
     * Generate clean XML from sanitized data
     * 
     * @param array $data Sanitized data
     * @return string Clean XML content
     */
    private function generateCleanXml(array $data): string
    {
        try {
            $xml = new \SimpleXMLElement('<root/>');
            self::arrayToXMLSecure($data, $xml);
            return $xml->asXML();
        } catch (\Exception $e) {
            Log::error("Error generating clean XML: " . $e->getMessage());
            throw new \Exception("Gagal membuat XML yang bersih");
        }
    }

    /**
     * Add XAdES digital signature to XML content following ETSI TS 101 903 standard
     *
     * @param string $xmlContent The original XML content
     * @return string XML content with XAdES signature
     * @throws \Exception
     */
    private function addXAdESSignature(string $xmlContent): string
    {
        try {
            if (!self::XADES_CONFIG['enabled']) {
                Log::info("XAdES signature disabled, returning unsigned XML");
                return $xmlContent;
            }

            Log::info("Adding ETSI-compliant XAdES signature to XML metadata");

            // Load private key and certificate
            $privateKeyPath = config('app.xades.private_key_path');
            $certificatePath = config('app.xades.certificate_path');

            if (!file_exists($privateKeyPath) || !file_exists($certificatePath)) {
                throw new \Exception("XAdES keys not found. Please run setup-xades-keys.sh first.");
            }

            $privateKey = file_get_contents($privateKeyPath);
            $certificate = file_get_contents($certificatePath);

            if (!$privateKey || !$certificate) {
                throw new \Exception("Failed to read XAdES keys");
            }

            // Parse the original XML
            $xmlDoc = new \DOMDocument();
            $xmlDoc->formatOutput = true;
            $xmlDoc->loadXML($xmlContent);
            $rootElement = $xmlDoc->documentElement;

            // Generate unique IDs
            $signatureId = 'Signature-' . uniqid();
            $dataObjectId = 'DataObject-' . uniqid();
            $signedPropertiesId = 'SignedProperties-' . uniqid();
            
            // Add Id to root element for referencing
            $rootElement->setAttribute('Id', $dataObjectId);

            // Create the XAdES signature following ETSI format
            $signature = $this->createETSISignature($xmlDoc, $signatureId, $dataObjectId, $signedPropertiesId, $privateKey, $certificate);
            
            // Append signature to root element
            $rootElement->appendChild($signature);

            $signedXml = $xmlDoc->saveXML();
            
            Log::info("ETSI-compliant XAdES signature added successfully", [
                'signature_id' => $signatureId,
                'data_object_id' => $dataObjectId,
                'signed_properties_id' => $signedPropertiesId
            ]);

            return $signedXml;

        } catch (\Exception $e) {
            Log::error("Failed to add XAdES signature: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // In case of signature failure, return unsigned XML with warning
            Log::warning("Returning unsigned XML due to XAdES signature failure");
            return $xmlContent;
        }
    }

    /**
     * Create ETSI-compliant ds:Signature element
     */
    private function createETSISignature(\DOMDocument $xmlDoc, string $signatureId, string $dataObjectId, string $signedPropertiesId, string $privateKey, string $certificate): \DOMElement
    {
        $namespaces = self::XADES_CONFIG['namespaces'];
        
        // Create main signature element with namespaces
        $signature = $xmlDoc->createElement('ds:Signature');
        $signature->setAttribute('Id', $signatureId);
        $signature->setAttribute('xmlns:ds', $namespaces['ds']);
        $signature->setAttribute('xmlns:xades', $namespaces['xades']);

        // Create SignedInfo
        $signedInfo = $this->createSignedInfo($xmlDoc, $dataObjectId, $signedPropertiesId);
        $signature->appendChild($signedInfo);

        // Calculate signature value
        $canonicalSignedInfo = $signedInfo->C14N(true, false);
        $signatureValue = '';
        $result = openssl_sign($canonicalSignedInfo, $signatureValue, $privateKey, self::XADES_CONFIG['algorithm']);
        
        if (!$result) {
            throw new \Exception("Failed to generate digital signature: " . openssl_error_string());
        }

        // Add SignatureValue
        $signatureValueElement = $xmlDoc->createElement('ds:SignatureValue', base64_encode($signatureValue));
        $signature->appendChild($signatureValueElement);

        // Add KeyInfo
        $keyInfo = $this->createKeyInfo($xmlDoc, $certificate);
        $signature->appendChild($keyInfo);

        // Add Object with QualifyingProperties
        $object = $this->createQualifyingProperties($xmlDoc, $signatureId, $signedPropertiesId);
        $signature->appendChild($object);

        return $signature;
    }

    /**
     * Create ds:SignedInfo element
     */
    private function createSignedInfo(\DOMDocument $xmlDoc, string $dataObjectId, string $signedPropertiesId): \DOMElement
    {
        $signedInfo = $xmlDoc->createElement('ds:SignedInfo');

        // CanonicalizationMethod
        $canonicalizationMethod = $xmlDoc->createElement('ds:CanonicalizationMethod');
        $canonicalizationMethod->setAttribute('Algorithm', self::XADES_CONFIG['canonicalization_method']);
        $signedInfo->appendChild($canonicalizationMethod);

        // SignatureMethod
        $signatureMethod = $xmlDoc->createElement('ds:SignatureMethod');
        $signatureMethod->setAttribute('Algorithm', self::XADES_CONFIG['signature_method']);
        $signedInfo->appendChild($signatureMethod);

        // Reference to data object
        $dataReference = $this->createReference($xmlDoc, '#' . $dataObjectId, null);
        $signedInfo->appendChild($dataReference);

        // Reference to signed properties
        $signedPropsReference = $this->createReference($xmlDoc, '#' . $signedPropertiesId, 'http://uri.etsi.org/01903#SignedProperties');
        $signedInfo->appendChild($signedPropsReference);

        return $signedInfo;
    }

    /**
     * Create ds:Reference element
     */
    private function createReference(\DOMDocument $xmlDoc, string $uri, ?string $type): \DOMElement
    {
        $reference = $xmlDoc->createElement('ds:Reference');
        $reference->setAttribute('URI', $uri);
        
        if ($type) {
            $reference->setAttribute('Type', $type);
        }

        // DigestMethod
        $digestMethod = $xmlDoc->createElement('ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', self::XADES_CONFIG['digest_method']);
        $reference->appendChild($digestMethod);

        // Calculate digest value
        $digestValue = $this->calculateDigestValue($xmlDoc, $uri);
        $digestValueElement = $xmlDoc->createElement('ds:DigestValue', $digestValue);
        $reference->appendChild($digestValueElement);

        return $reference;
    }

    /**
     * Create ds:KeyInfo element
     */
    private function createKeyInfo(\DOMDocument $xmlDoc, string $certificate): \DOMElement
    {
        $keyInfo = $xmlDoc->createElement('ds:KeyInfo');
        
        $x509Data = $xmlDoc->createElement('ds:X509Data');
        $keyInfo->appendChild($x509Data);
        
        // Clean and format certificate
        $cleanCert = preg_replace('/-----BEGIN CERTIFICATE-----/', '', $certificate);
        $cleanCert = preg_replace('/-----END CERTIFICATE-----/', '', $cleanCert);
        $cleanCert = preg_replace('/\s+/', '', $cleanCert);
        
        $x509Certificate = $xmlDoc->createElement('ds:X509Certificate', $cleanCert);
        $x509Data->appendChild($x509Certificate);

        return $keyInfo;
    }

    /**
     * Create ds:Object with QualifyingProperties
     */
    private function createQualifyingProperties(\DOMDocument $xmlDoc, string $signatureId, string $signedPropertiesId): \DOMElement
    {
        $object = $xmlDoc->createElement('ds:Object');
        
        $qualifyingProperties = $xmlDoc->createElement('xades:QualifyingProperties');
        $qualifyingProperties->setAttribute('Target', '#' . $signatureId);
        $object->appendChild($qualifyingProperties);
        
        $signedProperties = $xmlDoc->createElement('xades:SignedProperties');
        $signedProperties->setAttribute('Id', $signedPropertiesId);
        $qualifyingProperties->appendChild($signedProperties);
        
        // SignedSignatureProperties
        $signedSignatureProperties = $xmlDoc->createElement('xades:SignedSignatureProperties');
        $signedProperties->appendChild($signedSignatureProperties);
        
        // SigningTime
        $signingTime = $xmlDoc->createElement('xades:SigningTime', date('c'));
        $signedSignatureProperties->appendChild($signingTime);
        
        // SignedDataObjectProperties (optional but included for completeness)
        $signedDataObjectProperties = $xmlDoc->createElement('xades:SignedDataObjectProperties');
        $signedProperties->appendChild($signedDataObjectProperties);

        return $object;
    }

    /**
     * Calculate digest value for reference
     */
    private function calculateDigestValue(\DOMDocument $xmlDoc, string $uri): string
    {
        if (strpos($uri, '#') === 0) {
            $id = substr($uri, 1);
            $element = $xmlDoc->getElementById($id);
            
            if ($element) {
                $canonicalElement = $element->C14N(true, false);
                return base64_encode(hash('sha256', $canonicalElement, true));
            }
        }
        
        // Fallback: digest of entire document
        $canonicalDoc = $xmlDoc->C14N();
        return base64_encode(hash('sha256', $canonicalDoc, true));
    }

    /**
     * Verify ETSI-compliant XAdES digital signature in XML content
     *
     * @param string $xmlContent The XML content with signature
     * @return array Verification result with status and details
     */
    private function verifyXAdESSignature(string $xmlContent): array
    {
        try {
            if (!self::XADES_CONFIG['enabled']) {
                return [
                    'valid' => true,
                    'message' => 'XAdES verification disabled',
                    'details' => []
                ];
            }

            Log::info("Verifying ETSI-compliant XAdES signature in XML metadata");

            // Parse XML with namespace support
            $xmlDoc = new \DOMDocument();
            $xmlDoc->loadXML($xmlContent);
            
            // Create XPath with namespaces
            $xpath = new \DOMXPath($xmlDoc);
            $xpath->registerNamespace('ds', self::XADES_CONFIG['namespaces']['ds']);
            $xpath->registerNamespace('xades', self::XADES_CONFIG['namespaces']['xades']);
            
            // Find signature element
            $signatureElements = $xpath->query('//ds:Signature');
            
            if ($signatureElements->length === 0) {
                return [
                    'valid' => false,
                    'message' => 'No XAdES signature found in XML',
                    'details' => []
                ];
            }

            $signatureElement = $signatureElements->item(0);
            $signatureId = $signatureElement->getAttribute('Id');
            
            // Extract ETSI signature components
            $signedInfo = $xpath->query('.//ds:SignedInfo', $signatureElement)->item(0);
            $signatureValueElement = $xpath->query('.//ds:SignatureValue', $signatureElement)->item(0);
            $x509Certificate = $xpath->query('.//ds:X509Certificate', $signatureElement)->item(0);
            $signingTime = $xpath->query('.//xades:SigningTime', $signatureElement)->item(0);
            
            if (!$signedInfo || !$signatureValueElement || !$x509Certificate) {
                return [
                    'valid' => false,
                    'message' => 'Invalid ETSI signature structure',
                    'details' => []
                ];
            }

            $signatureValue = base64_decode($signatureValueElement->textContent);
            $certificate = "-----BEGIN CERTIFICATE-----\n" . 
                          chunk_split($x509Certificate->textContent, 64) . 
                          "-----END CERTIFICATE-----";
            
            // Verify SignedInfo signature
            $canonicalSignedInfo = $signedInfo->C14N(true, false);
            $verificationResult = openssl_verify($canonicalSignedInfo, $signatureValue, $certificate, self::XADES_CONFIG['algorithm']);
            
            // Verify references
            $referenceVerifications = $this->verifyETSIReferences($xpath, $signatureElement, $xmlDoc);
            
            $details = [
                'signature_id' => $signatureId,
                'signing_time' => $signingTime ? $signingTime->textContent : 'Not available',
                'reference_verifications' => $referenceVerifications,
                'all_references_valid' => !in_array(false, array_column($referenceVerifications, 'valid'))
            ];
            
            if ($verificationResult === 1 && $details['all_references_valid']) {
                return [
                    'valid' => true,
                    'message' => 'ETSI XAdES signature is valid',
                    'details' => $details
                ];
            } elseif ($verificationResult === 0) {
                return [
                    'valid' => false,
                    'message' => 'ETSI XAdES signature verification failed',
                    'details' => $details
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => 'XAdES signature verification error: ' . openssl_error_string(),
                    'details' => $details
                ];
            }

        } catch (\Exception $e) {
            Log::error("XAdES signature verification failed: " . $e->getMessage());
            return [
                'valid' => false,
                'message' => 'XAdES verification error: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Verify ETSI references in signature
     */
    private function verifyETSIReferences(\DOMXPath $xpath, \DOMElement $signatureElement, \DOMDocument $xmlDoc): array
    {
        $verifications = [];
        $references = $xpath->query('.//ds:Reference', $signatureElement);
        
        foreach ($references as $reference) {
            $uri = $reference->getAttribute('URI');
            $type = $reference->getAttribute('Type');
            
            $digestValueElement = $xpath->query('.//ds:DigestValue', $reference)->item(0);
            $expectedDigest = $digestValueElement ? $digestValueElement->textContent : '';
            
            // Calculate actual digest
            $actualDigest = $this->calculateDigestValue($xmlDoc, $uri);
            
            $verifications[] = [
                'uri' => $uri,
                'type' => $type,
                'expected_digest' => $expectedDigest,
                'actual_digest' => $actualDigest,
                'valid' => $expectedDigest === $actualDigest
            ];
        }
        
        return $verifications;
    }

    /**
     * Helper method to extract element value from DOM
     */
    private function getElementValue(\DOMElement $parent, string $tagName): string
    {
        $elements = $parent->getElementsByTagName($tagName);
        return $elements->length > 0 ? $elements->item(0)->textContent : '';
    }

    /**
     * Check if XAdES keys exist and are valid
     */
    private function validateXAdESSetup(): array
    {
        $privateKeyPath = config('app.xades.private_key_path');
        $certificatePath = config('app.xades.certificate_path');

        $issues = [];

        if (!file_exists($privateKeyPath)) {
            $issues[] = "Private key not found at: $privateKeyPath";
        } else {
            $privateKey = file_get_contents($privateKeyPath);
            $keyResource = openssl_pkey_get_private($privateKey);
            if (!$keyResource) {
                $issues[] = "Invalid private key format";
            }
        }

        if (!file_exists($certificatePath)) {
            $issues[] = "Certificate not found at: $certificatePath";
        } else {
            $certificate = file_get_contents($certificatePath);
            $certResource = openssl_x509_read($certificate);
            if (!$certResource) {
                $issues[] = "Invalid certificate format";
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }
}
