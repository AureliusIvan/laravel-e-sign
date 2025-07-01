<?php
/**
 * Secure XML Implementation for Thesis Metadata
 * 
 * This class demonstrates how to implement the security recommendations
 * from the XML injection analysis to improve the SignatureController.php
 */

class SecureXMLThesisMetadata
{
    private const MAX_STRING_LENGTH = 500;
    private const MAX_TITLE_LENGTH = 1000;
    private const SCHEMA_PATH = __DIR__ . '/../thesis-metadata.xsd';
    
    private static $validationRules = [
        'UUID' => '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
        'Tipe_Laporan' => '/^(Skripsi|Tesis|Disertasi)$/',
        'Prodi' => '/^(Teknik Informatika|Sistem Informasi|Teknik Komputer)$/',
        'Tahun' => '/^20[0-9]{2}$/',
        'NIM' => '/^[0-9]{11}$/',
        'Dosen_Pembimbing_1__NIDN' => '/^[0-9]{6,10}$/',
    ];

    /**
     * Secure XML generation with comprehensive validation and sanitization
     */
    public static function generateSecureXML(array $data): string
    {
        // Step 1: Input validation
        self::validateInputData($data);
        
        // Step 2: Sanitization
        $sanitizedData = self::sanitizeData($data);
        
        // Step 3: XML generation with secure settings
        $xml = self::createSecureXMLDocument($sanitizedData);
        
        // Step 4: Schema validation
        self::validateAgainstSchema($xml);
        
        // Step 5: Security audit logging
        self::logXMLGeneration($data);
        
        return $xml;
    }

    /**
     * Validate input data against predefined rules
     */
    private static function validateInputData(array $data): void
    {
        $required = ['UUID', 'Tipe_Laporan', 'Judul_Laporan', 'Prodi', 'Tahun', 'Nama_Mahasiswa', 'NIM'];
        
        // Check required fields
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new InvalidArgumentException("Required field missing: {$field}");
            }
        }
        
        // Validate against regex patterns
        foreach (self::$validationRules as $field => $pattern) {
            if (isset($data[$field]) && !preg_match($pattern, $data[$field])) {
                throw new InvalidArgumentException("Invalid format for field: {$field}");
            }
        }
        
        // Length validation
        self::validateFieldLengths($data);
    }

    /**
     * Validate field lengths to prevent buffer overflow and DoS
     */
    private static function validateFieldLengths(array $data): void
    {
        $lengthLimits = [
            'Judul_Laporan' => self::MAX_TITLE_LENGTH,
            'Judul_Laporan_EN' => self::MAX_TITLE_LENGTH,
            'Nama_Mahasiswa' => self::MAX_STRING_LENGTH,
            'Dosen_Pembimbing_1__Nama' => self::MAX_STRING_LENGTH,
            'KAPRODI' => self::MAX_STRING_LENGTH,
        ];
        
        foreach ($lengthLimits as $field => $maxLength) {
            if (isset($data[$field]) && strlen($data[$field]) > $maxLength) {
                throw new InvalidArgumentException("Field {$field} exceeds maximum length of {$maxLength}");
            }
        }
    }

    /**
     * Comprehensive data sanitization
     */
    private static function sanitizeData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeData($value);
            } else {
                // Multi-layer sanitization
                $sanitized[$key] = self::sanitizeValue($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Advanced value sanitization with multiple protection layers
     */
    private static function sanitizeValue($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        // Layer 1: Remove null bytes and control characters
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Layer 2: Normalize unicode
        if (function_exists('normalizer_normalize')) {
            $value = normalizer_normalize($value, Normalizer::FORM_C);
        }
        
        // Layer 3: HTML/XML entity encoding with strict flags
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_XML1 | ENT_SUBSTITUTE, 'UTF-8');
        
        // Layer 4: Additional XML-specific escaping
        $value = str_replace([']]>', '<!--', '-->'], [']]&gt;', '&lt;!--', '--&gt;'], $value);
        
        // Layer 5: Trim and length limit
        $value = trim($value);
        $value = substr($value, 0, self::MAX_STRING_LENGTH);
        
        return $value;
    }

    /**
     * Create XML document with secure configuration
     */
    private static function createSecureXMLDocument(array $data): string
    {
        // Use DOMDocument for better control
        $dom = new DOMDocument('1.0', 'UTF-8');
        
        // Security configurations
        $dom->resolveExternals = false;
        $dom->substituteEntities = false;
        $dom->recover = false;
        $dom->strictErrorChecking = true;
        
        // Create root element with namespace
        $root = $dom->createElementNS('http://umn.ac.id/thesis-metadata', 'root');
        $dom->appendChild($root);
        
        // Add data elements
        self::addSecureDataElements($dom, $root, $data);
        
        // Format output
        $dom->formatOutput = true;
        
        return $dom->saveXML();
    }

    /**
     * Add data elements with secure text node creation
     */
    private static function addSecureDataElements(DOMDocument $dom, DOMElement $parent, array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $dom->createElement($key);
                $parent->appendChild($child);
                self::addSecureDataElements($dom, $child, $value);
            } else {
                // Create element and text node separately for security
                $element = $dom->createElement($key);
                $textNode = $dom->createTextNode($value);
                $element->appendChild($textNode);
                $parent->appendChild($element);
            }
        }
    }

    /**
     * Validate generated XML against XSD schema
     */
    private static function validateAgainstSchema(string $xmlContent): void
    {
        if (!file_exists(self::SCHEMA_PATH)) {
            throw new RuntimeException('XML Schema file not found: ' . self::SCHEMA_PATH);
        }
        
        // Configure secure XML parsing
        $previousSetting = libxml_use_internal_errors(true);
        $previousEntityLoader = libxml_disable_entity_loader(true);
        
        try {
            $dom = new DOMDocument();
            $dom->resolveExternals = false;
            $dom->substituteEntities = false;
            
            if (!$dom->loadXML($xmlContent)) {
                $errors = libxml_get_errors();
                throw new InvalidArgumentException('Invalid XML: ' . self::formatLibxmlErrors($errors));
            }
            
            if (!$dom->schemaValidate(self::SCHEMA_PATH)) {
                $errors = libxml_get_errors();
                throw new InvalidArgumentException('XML does not conform to schema: ' . self::formatLibxmlErrors($errors));
            }
            
        } finally {
            libxml_use_internal_errors($previousSetting);
            libxml_disable_entity_loader($previousEntityLoader);
            libxml_clear_errors();
        }
    }

    /**
     * Format libxml errors for user-friendly display
     */
    private static function formatLibxmlErrors(array $errors): string
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[] = "Line {$error->line}: {$error->message}";
        }
        return implode('; ', $formatted);
    }

    /**
     * Secure XML parsing for verification
     */
    public static function parseSecureXML(string $xmlContent): array
    {
        // Configure secure parsing
        $previousEntityLoader = libxml_disable_entity_loader(true);
        $previousUseErrors = libxml_use_internal_errors(true);
        
        try {
            $dom = new DOMDocument();
            
            // Security settings
            $dom->resolveExternals = false;
            $dom->substituteEntities = false;
            $dom->recover = false;
            
            if (!$dom->loadXML($xmlContent, LIBXML_NONET | LIBXML_NOENT | LIBXML_NOBLANKS)) {
                $errors = libxml_get_errors();
                throw new InvalidArgumentException('Invalid XML: ' . self::formatLibxmlErrors($errors));
            }
            
            // Validate against schema
            if (file_exists(self::SCHEMA_PATH)) {
                if (!$dom->schemaValidate(self::SCHEMA_PATH)) {
                    throw new InvalidArgumentException('XML does not conform to expected schema');
                }
            }
            
            // Extract data safely
            return self::extractDataFromDOM($dom);
            
        } finally {
            libxml_disable_entity_loader($previousEntityLoader);
            libxml_use_internal_errors($previousUseErrors);
            libxml_clear_errors();
        }
    }

    /**
     * Extract data from DOM with XPath for security
     */
    private static function extractDataFromDOM(DOMDocument $dom): array
    {
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('tm', 'http://umn.ac.id/thesis-metadata');
        
        $data = [];
        $fields = [
            'UUID', 'Tipe_Laporan', 'Judul_Laporan', 'Judul_Laporan_EN',
            'Prodi', 'Tahun', 'Nama_Mahasiswa', 'NIM',
            'Dosen_Pembimbing_1__Nama', 'Dosen_Pembimbing_1__NIDN', 'KAPRODI'
        ];
        
        foreach ($fields as $field) {
            $nodes = $xpath->query("//tm:{$field}");
            if ($nodes && $nodes->length > 0) {
                $data[$field] = $nodes->item(0)->textContent;
            }
        }
        
        return $data;
    }

    /**
     * Security audit logging
     */
    private static function logXMLGeneration(array $data): void
    {
        $logData = [
            'action' => 'xml_generation',
            'timestamp' => date('Y-m-d H:i:s'),
            'uuid' => $data['UUID'] ?? 'unknown',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data_hash' => hash('sha256', serialize($data))
        ];
        
        // In a real implementation, this would use Laravel's Log facade
        error_log('SECURITY_AUDIT: ' . json_encode($logData));
    }

    /**
     * Demonstrate the secure implementation
     */
    public static function demonstrateSecureImplementation(): void
    {
        echo "<h2>Secure XML Implementation Demonstration</h2>\n";
        
        // Test data
        $testData = [
            'UUID' => '550e8400-e29b-41d4-a716-446655440000',
            'Tipe_Laporan' => 'Skripsi',
            'Judul_Laporan' => 'Sistem Informasi Manajemen Tugas Akhir Berbasis Web',
            'Judul_Laporan_EN' => 'Web-Based Thesis Management Information System',
            'Prodi' => 'Teknik Informatika',
            'Tahun' => '2024',
            'Nama_Mahasiswa' => 'John Doe',
            'NIM' => '00000000001',
            'Dosen_Pembimbing_1__Nama' => 'Dr. Jane Smith',
            'Dosen_Pembimbing_1__NIDN' => '1234567890',
            'KAPRODI' => 'Prof. Bob Johnson'
        ];
        
        try {
            echo "<h3>✅ Secure XML Generation</h3>\n";
            $secureXML = self::generateSecureXML($testData);
            echo "<pre>" . htmlspecialchars($secureXML) . "</pre>\n";
            
            echo "<h3>✅ Secure XML Parsing</h3>\n";
            $parsedData = self::parseSecureXML($secureXML);
            echo "<pre>" . htmlspecialchars(print_r($parsedData, true)) . "</pre>\n";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
        
        // Test with malicious data
        echo "<h3>🛡️ Testing with Malicious Data</h3>\n";
        $maliciousData = $testData;
        $maliciousData['Judul_Laporan'] = 'Title</Judul_Laporan><script>alert("XSS")</script><Judul_Laporan>';
        
        try {
            $secureXML = self::generateSecureXML($maliciousData);
            echo "<p style='color: green;'>✅ Malicious input was successfully sanitized</p>\n";
            echo "<pre>" . htmlspecialchars($secureXML) . "</pre>\n";
        } catch (Exception $e) {
            echo "<p style='color: orange;'>⚠️ Malicious input rejected: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
    }
}

// Run demonstration if executed directly
if (php_sapi_name() === 'cli' || (isset($_GET['demo']) && $_GET['demo'] === 'true')) {
    echo "<!DOCTYPE html>\n";
    echo "<html><head><title>Secure XML Implementation Demo</title></head><body>\n";
    
    SecureXMLThesisMetadata::demonstrateSecureImplementation();
    
    echo "</body></html>\n";
} else {
    echo "Add ?demo=true to the URL to run the demonstration.\n";
}
?> 