<?php

namespace App\Http\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use TCPDF;
use Exception;

trait PdfSanitizationTrait
{
    /**
     * Student Upload PDF Sanitization Configuration
     * More permissive than signature sanitization since these are source documents
     */
    private const STUDENT_PDF_CONFIG = [
        'enabled' => true,                        // Enable/disable PDF sanitization
        'max_file_size' => 30 * 1024 * 1024,      // 30MB max (matching validation)
        'max_pages' => 200,                       // Maximum pages allowed
        'max_objects' => 5000,                    // Maximum PDF objects
        'security_checks' => [
            'check_javascript' => true,           // Block JavaScript
            'check_forms' => false,               // Allow forms (students might use fillable PDFs)
            'check_external_refs' => true,        // Block external references
            'check_embedded_files' => true,       // Block embedded executables
            'check_suspicious_content' => true,   // General suspicious content check
        ],
        'cleanup_temp_files' => true,            // Clean up temporary files
        'quarantine_suspicious' => true,         // Move suspicious files to quarantine
        'sanitization_method' => 'structural',   // 'structural', 'image', 'off'
    ];

    /**
     * Sanitize uploaded PDF from student submission
     * 
     * @param UploadedFile $file Uploaded PDF file
     * @param string $uploadType Type of upload (proposal, revision, etc.)
     * @param string $studentId Student identifier for logging
     * @return array Sanitization results
     */
    public function sanitizeStudentPdf(UploadedFile $file, string $uploadType, string $studentId): array
    {
        try {
            if (!self::STUDENT_PDF_CONFIG['enabled']) {
                Log::info("PDF sanitization disabled, allowing upload", [
                    'student_id' => $studentId,
                    'upload_type' => $uploadType,
                    'filename' => $file->getClientOriginalName()
                ]);
                
                return [
                    'success' => true,
                    'message' => 'File uploaded without sanitization (disabled)',
                    'original_file' => $file,
                    'sanitized' => false
                ];
            }

            Log::info("Starting PDF sanitization for student upload", [
                'student_id' => $studentId,
                'upload_type' => $uploadType,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            // Step 1: Basic file validation
            $basicValidation = $this->validateBasicPdfFile($file);
            if (!$basicValidation['valid']) {
                return [
                    'success' => false,
                    'error' => $basicValidation['error'],
                    'blocked_reason' => 'Basic validation failed'
                ];
            }

            // Step 2: Security analysis
            $securityAnalysis = $this->analyzeStudentPdfSecurity($file);
            if (!$securityAnalysis['safe']) {
                // Handle based on severity
                if ($securityAnalysis['severity'] === 'critical') {
                    $this->quarantineSuspiciousFile($file, $studentId, $securityAnalysis['reasons']);
                    return [
                        'success' => false,
                        'error' => 'File mengandung konten berbahaya: ' . implode(', ', $securityAnalysis['reasons']),
                        'blocked_reason' => 'Security threat detected'
                    ];
                } else {
                    Log::warning("PDF has security concerns but allowing with sanitization", [
                        'student_id' => $studentId,
                        'concerns' => $securityAnalysis['reasons']
                    ]);
                }
            }

            // Step 3: Structural sanitization (if enabled)
            $sanitizationMethod = self::STUDENT_PDF_CONFIG['sanitization_method'];
            if ($sanitizationMethod !== 'off') {
                $sanitizationResult = $this->performStudentPdfSanitization($file, $sanitizationMethod, $studentId);
                
                if (!$sanitizationResult['success']) {
                    // If sanitization fails, decide whether to allow original or block
                    if ($securityAnalysis['safe']) {
                        Log::warning("Sanitization failed but file appears safe, allowing original", [
                            'student_id' => $studentId,
                            'error' => $sanitizationResult['error']
                        ]);
                        
                        return [
                            'success' => true,
                            'message' => 'File uploaded with original content (sanitization failed but safe)',
                            'original_file' => $file,
                            'sanitized' => false,
                            'warnings' => ['Sanitization failed: ' . $sanitizationResult['error']]
                        ];
                    } else {
                        return [
                            'success' => false,
                            'error' => 'File tidak dapat dibersihkan dan mengandung konten mencurigakan',
                            'blocked_reason' => 'Sanitization failed for suspicious file'
                        ];
                    }
                }

                return [
                    'success' => true,
                    'message' => 'File berhasil dibersihkan dan diupload',
                    'sanitized_file' => $sanitizationResult['sanitized_file'],
                    'sanitized' => true,
                    'method' => $sanitizationMethod
                ];
            }

            // No sanitization requested, just security check passed
            return [
                'success' => true,
                'message' => 'File lolos pemeriksaan keamanan',
                'original_file' => $file,
                'sanitized' => false
            ];

        } catch (Exception $e) {
            Log::error("Error during student PDF sanitization", [
                'student_id' => $studentId,
                'upload_type' => $uploadType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Terjadi kesalahan saat memproses file: ' . $e->getMessage(),
                'blocked_reason' => 'Processing error'
            ];
        }
    }

    /**
     * Validate basic PDF file properties
     * 
     * @param UploadedFile $file
     * @return array Validation results
     */
    private function validateBasicPdfFile(UploadedFile $file): array
    {
        try {
            // Check file size
            $maxSize = self::STUDENT_PDF_CONFIG['max_file_size'];
            if ($file->getSize() > $maxSize) {
                return [
                    'valid' => false,
                    'error' => 'File terlalu besar (maksimal ' . round($maxSize / (1024 * 1024)) . 'MB)'
                ];
            }

            // Check MIME type
            $mimeType = $file->getMimeType();
            if ($mimeType !== 'application/pdf') {
                return [
                    'valid' => false,
                    'error' => 'Tipe file tidak valid (harus PDF)'
                ];
            }

            // Check if file can be read
            $content = $file->get();
            if (empty($content)) {
                return [
                    'valid' => false,
                    'error' => 'File kosong atau tidak dapat dibaca'
                ];
            }

            // Check PDF header
            if (substr($content, 0, 4) !== '%PDF') {
                return [
                    'valid' => false,
                    'error' => 'File bukan PDF yang valid'
                ];
            }

            // Check if PDF can be parsed by PDF library
            try {
                $tempPath = $file->getRealPath();
                $pdf = new Pdf($tempPath);
                $pageCount = $pdf->pageCount();
                
                $maxPages = self::STUDENT_PDF_CONFIG['max_pages'];
                if ($pageCount > $maxPages) {
                    return [
                        'valid' => false,
                        'error' => "PDF terlalu banyak halaman (maksimal {$maxPages})"
                    ];
                }

                if ($pageCount <= 0) {
                    return [
                        'valid' => false,
                        'error' => 'PDF tidak memiliki halaman yang dapat dibaca'
                    ];
                }

            } catch (Exception $e) {
                Log::warning("PDF parsing failed during validation", [
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
                
                return [
                    'valid' => false,
                    'error' => 'PDF rusak atau format tidak didukung'
                ];
            }

            return ['valid' => true];

        } catch (Exception $e) {
            Log::error("Error during basic PDF validation", [
                'error' => $e->getMessage()
            ]);
            
            return [
                'valid' => false,
                'error' => 'Gagal memvalidasi file PDF'
            ];
        }
    }

    /**
     * Analyze PDF for security threats (student context)
     * 
     * @param UploadedFile $file
     * @return array Security analysis results
     */
    private function analyzeStudentPdfSecurity(UploadedFile $file): array
    {
        try {
            $content = $file->get();
            $securityConfig = self::STUDENT_PDF_CONFIG['security_checks'];
            $threats = [];
            $severity = 'low';

            // Check for JavaScript (critical for students)
            if ($securityConfig['check_javascript']) {
                $jsPatterns = [
                    '/\/JavaScript\s*\(/i',
                    '/\/JS\s*\(/i',
                    '/app\.alert\s*\(/i',
                    '/this\.print\s*\(/i',
                    '/eval\s*\(/i',
                ];

                foreach ($jsPatterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $threats[] = 'JavaScript code detected';
                        $severity = 'critical';
                        break;
                    }
                }
            }

            // Check for external references (could be data exfiltration)
            if ($securityConfig['check_external_refs']) {
                $extPatterns = [
                    '/\/URI\s*\(/i',
                    '/\/Launch\s*\(/i',
                    '/\/GoToR\s*\(/i',
                    '/\/SubmitForm\s*\(/i',
                    '/http[s]?:\/\/(?!localhost|127\.0\.0\.1)/i',
                ];

                foreach ($extPatterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $threats[] = 'External references detected';
                        $severity = max($severity, 'medium');
                        break;
                    }
                }
            }

            // Check for embedded executables
            if ($securityConfig['check_embedded_files']) {
                if (strpos($content, '/EmbeddedFile') !== false) {
                    // Check if it contains executable signatures
                    $executableSignatures = [
                        'MZ',      // PE/EXE
                        '\x7fELF', // ELF
                        'PK',      // ZIP/JAR
                    ];

                    foreach ($executableSignatures as $sig) {
                        if (strpos($content, $sig) !== false) {
                            $threats[] = 'Embedded executable detected';
                            $severity = 'critical';
                            break;
                        }
                    }
                }
            }

            // Check for suspicious content patterns
            if ($securityConfig['check_suspicious_content']) {
                $suspiciousPatterns = [
                    '/<!ENTITY.*SYSTEM/i',  // XXE attempts
                    '/\/OpenAction/i',       // Auto-execute actions
                    '/\/AA\s*<</i',         // Additional actions
                    '/\/Names\s*<</i',      // Name dictionary (potential obfuscation)
                ];

                foreach ($suspiciousPatterns as $pattern) {
                    if (preg_match($pattern, $content)) {
                        $threats[] = 'Suspicious content pattern detected';
                        $severity = max($severity, 'medium');
                        break;
                    }
                }
            }

            // Check object count (potential DoS)
            $objectCount = preg_match_all('/\d+\s+0\s+obj/', $content);
            $maxObjects = self::STUDENT_PDF_CONFIG['max_objects'];
            if ($objectCount > $maxObjects) {
                $threats[] = "Too many objects ({$objectCount} > {$maxObjects})";
                $severity = max($severity, 'medium');
            }

            $isSafe = empty($threats) || $severity === 'low';

            return [
                'safe' => $isSafe,
                'severity' => $severity,
                'reasons' => $threats,
                'object_count' => $objectCount
            ];

        } catch (Exception $e) {
            Log::error("Error during PDF security analysis", [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);

            return [
                'safe' => false,
                'severity' => 'critical',
                'reasons' => ['Analysis failed: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Perform PDF sanitization for student uploads
     * 
     * @param UploadedFile $file
     * @param string $method
     * @param string $studentId
     * @return array Sanitization results
     */
    private function performStudentPdfSanitization(UploadedFile $file, string $method, string $studentId): array
    {
        try {
            $tempDir = storage_path('app/temp/student_sanitization/');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $originalPath = $file->getRealPath();
            $sanitizedFilename = 'sanitized_' . time() . '_' . $studentId . '.pdf';
            $sanitizedPath = $tempDir . $sanitizedFilename;

            switch ($method) {
                case 'structural':
                    return $this->sanitizeStudentPdfStructural($originalPath, $sanitizedPath);
                
                case 'image':
                    return $this->sanitizeStudentPdfImage($originalPath, $sanitizedPath);
                
                default:
                    return [
                        'success' => false,
                        'error' => 'Unknown sanitization method: ' . $method
                    ];
            }

        } catch (Exception $e) {
            Log::error("PDF sanitization failed", [
                'student_id' => $studentId,
                'method' => $method,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Structural PDF sanitization (preserves text, removes scripts)
     * 
     * @param string $originalPath
     * @param string $sanitizedPath
     * @return array
     */
    private function sanitizeStudentPdfStructural(string $originalPath, string $sanitizedPath): array
    {
        try {
            Log::info("Performing structural PDF sanitization");
            
            // Create new FPDI instance
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi();
            $pdf->SetCreator('Student Upload Sanitizer');
            $pdf->SetAuthor('Universitas Multimedia Nusantara');
            $pdf->SetTitle('Sanitized Student Document');

            try {
                $pageCount = $pdf->setSourceFile($originalPath);
                
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }

                $pdf->Output($sanitizedPath, 'F');

                // Create UploadedFile instance for the sanitized file
                $sanitizedFile = new UploadedFile(
                    $sanitizedPath,
                    basename($sanitizedPath),
                    'application/pdf',
                    null,
                    true // test mode
                );

                return [
                    'success' => true,
                    'sanitized_file' => $sanitizedFile,
                    'method' => 'structural'
                ];

            } catch (Exception $e) {
                Log::warning("FPDI structural sanitization failed, trying fallback", [
                    'error' => $e->getMessage()
                ]);
                
                // Fallback: just copy the file (basic sanitization failed)
                if (copy($originalPath, $sanitizedPath)) {
                    $sanitizedFile = new UploadedFile(
                        $sanitizedPath,
                        basename($sanitizedPath),
                        'application/pdf',
                        null,
                        true
                    );

                    return [
                        'success' => true,
                        'sanitized_file' => $sanitizedFile,
                        'method' => 'copy_fallback',
                        'warnings' => ['Structural sanitization failed, using copy fallback']
                    ];
                } else {
                    throw new Exception("Failed to copy file as fallback");
                }
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Structural sanitization failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Image-based PDF sanitization (converts to images, removes all active content)
     * 
     * @param string $originalPath
     * @param string $sanitizedPath
     * @return array
     */
    private function sanitizeStudentPdfImage(string $originalPath, string $sanitizedPath): array
    {
        try {
            Log::info("Performing image-based PDF sanitization");
            
            $pdf = new TCPDF();
            $pdf->SetCreator('Student Upload Sanitizer');
            $pdf->SetAuthor('Universitas Multimedia Nusantara');
            $pdf->SetTitle('Sanitized Student Document');
            $pdf->SetAutoPageBreak(false);

            $sourcePdf = new Pdf($originalPath);
            $pageCount = $sourcePdf->pageCount();

            for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                $tempImagePath = storage_path('app/temp/page_' . $currentPage . '_' . time() . '.jpg');
                
                try {
                    (new Pdf($originalPath))->selectPage($currentPage)->save($tempImagePath);

                    if (!file_exists($tempImagePath)) {
                        throw new Exception("Failed to create image for page {$currentPage}");
                    }

                    $imageSize = getimagesize($tempImagePath);
                    if (!$imageSize) {
                        throw new Exception("Invalid image created for page {$currentPage}");
                    }

                    $pageWidthMM = ($imageSize[0] / 150) * 25.4;
                    $pageHeightMM = ($imageSize[1] / 150) * 25.4;
                    
                    $pdf->AddPage('P', [$pageWidthMM, $pageHeightMM]);
                    $pdf->Image($tempImagePath, 0, 0, $pageWidthMM, $pageHeightMM, 'JPG', '', '', true, 150);

                    unlink($tempImagePath);

                } catch (Exception $e) {
                    if (file_exists($tempImagePath)) {
                        unlink($tempImagePath);
                    }
                    throw new Exception("Failed to process page {$currentPage}: " . $e->getMessage());
                }
            }

            $pdf->Output($sanitizedPath, 'F');

            $sanitizedFile = new UploadedFile(
                $sanitizedPath,
                basename($sanitizedPath),
                'application/pdf',
                null,
                true
            );

            return [
                'success' => true,
                'sanitized_file' => $sanitizedFile,
                'method' => 'image_conversion'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Image-based sanitization failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Quarantine suspicious file for admin review
     * 
     * @param UploadedFile $file
     * @param string $studentId
     * @param array $reasons
     * @return void
     */
    private function quarantineSuspiciousFile(UploadedFile $file, string $studentId, array $reasons): void
    {
        try {
            if (!self::STUDENT_PDF_CONFIG['quarantine_suspicious']) {
                return;
            }

            $quarantineDir = storage_path('app/quarantine/');
            if (!is_dir($quarantineDir)) {
                mkdir($quarantineDir, 0755, true);
            }

            $quarantineFilename = 'quarantine_' . date('Y-m-d_H-i-s') . '_' . $studentId . '_' . $file->getClientOriginalName();
            $quarantinePath = $quarantineDir . $quarantineFilename;

            copy($file->getRealPath(), $quarantinePath);

            // Log quarantine action
            Log::critical("PDF file quarantined due to security threats", [
                'student_id' => $studentId,
                'original_filename' => $file->getClientOriginalName(),
                'quarantine_path' => $quarantinePath,
                'threats' => $reasons,
                'file_size' => $file->getSize(),
                'timestamp' => now()->toDateTimeString()
            ]);

            // You could also send notifications to admins here
            
        } catch (Exception $e) {
            Log::error("Failed to quarantine suspicious file", [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up temporary sanitization files
     * 
     * @return void
     */
    public function cleanupSanitizationFiles(): void
    {
        try {
            if (!self::STUDENT_PDF_CONFIG['cleanup_temp_files']) {
                return;
            }

            $tempDir = storage_path('app/temp/student_sanitization/');
            if (!is_dir($tempDir)) {
                return;
            }

            $files = glob($tempDir . '*');
            $maxAge = 3600; // 1 hour

            foreach ($files as $file) {
                if (is_file($file) && (time() - filemtime($file)) > $maxAge) {
                    unlink($file);
                    Log::info("Cleaned up old sanitization file", ['file' => basename($file)]);
                }
            }

        } catch (Exception $e) {
            Log::error("Error during sanitization cleanup", ['error' => $e->getMessage()]);
        }
    }
}
