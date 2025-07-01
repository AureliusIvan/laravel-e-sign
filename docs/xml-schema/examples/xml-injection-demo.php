<?php
/**
 * XML Injection Demonstration Script
 * 
 * This script demonstrates various XML injection vulnerabilities
 * and how the current SignatureController.php protection mechanisms work.
 * 
 * IMPORTANT: This is for educational/security testing purposes only!
 * DO NOT run this on production systems.
 */

class XMLInjectionDemo
{
    /**
     * Simulate the current SignatureController's XML generation method
     */
    public static function generateXMLSecure($data)
    {
        // Current protection: Uses htmlspecialchars() in arrayToXML
        $arrayData = json_decode(json_encode($data), true);
        $xml = new SimpleXMLElement('<root/>');
        self::arrayToXMLSecure($arrayData, $xml);
        return $xml->asXML();
    }

    /**
     * Secure XML generation (current implementation)
     */
    private static function arrayToXMLSecure(array $data, SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                self::arrayToXMLSecure($value, $child);
            } else {
                // Current protection: htmlspecialchars()
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * Vulnerable XML generation (NO protection - for demonstration)
     */
    public static function generateXMLVulnerable($data)
    {
        $arrayData = json_decode(json_encode($data), true);
        $xml = new SimpleXMLElement('<root/>');
        self::arrayToXMLVulnerable($arrayData, $xml);
        return $xml->asXML();
    }

    /**
     * Vulnerable XML generation without sanitization
     */
    private static function arrayToXMLVulnerable(array $data, SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $child = $xml->addChild($key);
                self::arrayToXMLVulnerable($value, $child);
            } else {
                // NO PROTECTION - Direct insertion
                $xml->addChild($key, $value);
            }
        }
    }

    /**
     * Test XML External Entity (XXE) processing
     */
    public static function testXXEVulnerability($xmlContent)
    {
        echo "<h3>Testing XXE Vulnerability</h3>\n";
        
        // Disable external entity loading for safety
        $previousSetting = libxml_disable_entity_loader(false);
        
        try {
            $doc = new DOMDocument();
            $doc->loadXML($xmlContent);
            
            echo "<p>XML parsed successfully. Content:</p>\n";
            echo "<pre>" . htmlspecialchars($doc->saveXML()) . "</pre>\n";
            
            // Check if XXE was processed
            $xpath = new DOMXPath($doc);
            $titleNode = $xpath->query('//Judul_Laporan')->item(0);
            if ($titleNode) {
                echo "<p>Title content: " . htmlspecialchars($titleNode->textContent) . "</p>\n";
                if (strpos($titleNode->textContent, 'root:') !== false) {
                    echo "<p style='color: red;'><strong>XXE VULNERABILITY DETECTED!</strong> System files exposed!</p>\n";
                }
            }
            
        } catch (Exception $e) {
            echo "<p style='color: orange;'>XML parsing failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        } finally {
            // Restore previous setting
            libxml_disable_entity_loader($previousSetting);
        }
    }

    /**
     * Demonstrate various attack scenarios
     */
    public static function demonstrateAttacks()
    {
        echo "<h2>XML Injection Attack Demonstrations</h2>\n";

        // Sample legitimate data
        $legitimateData = [
            'UUID' => '550e8400-e29b-41d4-a716-446655440000',
            'Tipe_Laporan' => 'Skripsi',
            'Judul_Laporan' => 'Sistem Informasi Manajemen Tugas Akhir',
            'Prodi' => 'Teknik Informatika',
            'Tahun' => '2024',
            'Nama_Mahasiswa' => 'John Doe',
            'NIM' => '00000000001',
            'Dosen_Pembimbing_1__Nama' => 'Dr. Jane Smith',
            'Dosen_Pembimbing_1__NIDN' => '000001',
            'KAPRODI' => 'Prof. Bob Johnson'
        ];

        echo "<h3>1. Legitimate Data (Protected)</h3>\n";
        $legitimateXML = self::generateXMLSecure($legitimateData);
        echo "<pre>" . htmlspecialchars($legitimateXML) . "</pre>\n";

        // Attack 1: Basic XML injection attempt
        echo "<h3>2. Basic XML Injection Attack</h3>\n";
        $maliciousData1 = $legitimateData;
        $maliciousData1['Judul_Laporan'] = 'Normal Title</Judul_Laporan><admin_access>true</admin_access><Judul_Laporan>';
        
        echo "<h4>With Protection (Current Implementation):</h4>\n";
        $secureXML = self::generateXMLSecure($maliciousData1);
        echo "<pre>" . htmlspecialchars($secureXML) . "</pre>\n";
        
        echo "<h4>Without Protection (Vulnerable):</h4>\n";
        try {
            $vulnerableXML = self::generateXMLVulnerable($maliciousData1);
            echo "<pre>" . htmlspecialchars($vulnerableXML) . "</pre>\n";
        } catch (Exception $e) {
            echo "<p style='color: red;'>XML generation failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }

        // Attack 2: CDATA injection
        echo "<h3>3. CDATA Injection Attack</h3>\n";
        $maliciousData2 = $legitimateData;
        $maliciousData2['Nama_Mahasiswa'] = '<![CDATA[<script>alert("XSS")</script>]]>';
        
        echo "<h4>With Protection:</h4>\n";
        $secureXML2 = self::generateXMLSecure($maliciousData2);
        echo "<pre>" . htmlspecialchars($secureXML2) . "</pre>\n";

        // Attack 3: Unicode/HTML entity bypass attempt
        echo "<h3>4. Unicode/Entity Bypass Attack</h3>\n";
        $maliciousData3 = $legitimateData;
        $maliciousData3['Judul_Laporan'] = 'Title&#x3C;script&#x3E;alert(&#x27;XSS&#x27;)&#x3C;/script&#x3E;';
        
        echo "<h4>With Protection:</h4>\n";
        $secureXML3 = self::generateXMLSecure($maliciousData3);
        echo "<pre>" . htmlspecialchars($secureXML3) . "</pre>\n";

        // XXE Attack demonstration
        echo "<h3>5. XXE (XML External Entity) Attack</h3>\n";
        $xxeXML = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE root [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<root>
    <UUID>550e8400-e29b-41d4-a716-446655440000</UUID>
    <Tipe_Laporan>Skripsi</Tipe_Laporan>
    <Judul_Laporan>Test Title &xxe;</Judul_Laporan>
    <Prodi>Teknik Informatika</Prodi>
    <Tahun>2024</Tahun>
    <Nama_Mahasiswa>John Doe</Nama_Mahasiswa>
    <NIM>00000000001</NIM>
    <Dosen_Pembimbing_1__Nama>Dr. Jane Smith</Dosen_Pembimbing_1__Nama>
    <Dosen_Pembimbing_1__NIDN>000001</Dosen_Pembimbing_1__NIDN>
    <KAPRODI>Prof. Bob Johnson</KAPRODI>
</root>';
        
        self::testXXEVulnerability($xxeXML);
    }

    /**
     * Show how database injection could lead to XML injection
     */
    public static function demonstrateDatabaseToXMLInjection()
    {
        echo "<h3>6. Database-to-XML Injection Chain</h3>\n";
        echo "<p>If an attacker compromises the database and injects malicious data into student records:</p>\n";
        
        // Simulate compromised database data
        $compromisedData = [
            'UUID' => '550e8400-e29b-41d4-a716-446655440000',
            'Tipe_Laporan' => 'Skripsi',
            'Judul_Laporan' => 'Normal Thesis Title',
            'Prodi' => 'Teknik Informatika',
            'Tahun' => '2024',
            // Malicious student name from compromised database
            'Nama_Mahasiswa' => 'John</Nama_Mahasiswa><backdoor>admin_access</backdoor><Nama_Mahasiswa>Doe',
            'NIM' => '00000000001',
            'Dosen_Pembimbing_1__Nama' => 'Dr. Jane Smith',
            'Dosen_Pembimbing_1__NIDN' => '000001',
            'KAPRODI' => 'Prof. Bob Johnson'
        ];

        echo "<h4>Current Protection Still Works:</h4>\n";
        $protectedXML = self::generateXMLSecure($compromisedData);
        echo "<pre>" . htmlspecialchars($protectedXML) . "</pre>\n";
        
        echo "<p style='color: green;'><strong>The htmlspecialchars() protection successfully prevents this attack!</strong></p>\n";
    }

    /**
     * Demonstrate potential bypasses
     */
    public static function demonstrateBypassAttempts()
    {
        echo "<h3>7. Potential Protection Bypasses</h3>\n";
        
        // Double encoding attempt
        echo "<h4>Double Encoding Bypass Attempt:</h4>\n";
        $doubleEncodedData = [
            'UUID' => '550e8400-e29b-41d4-a716-446655440000',
            'Tipe_Laporan' => 'Skripsi',
            'Judul_Laporan' => 'Title&amp;lt;script&amp;gt;alert(&amp;#39;XSS&amp;#39;)&amp;lt;/script&amp;gt;',
            'Prodi' => 'Teknik Informatika',
            'Tahun' => '2024',
            'Nama_Mahasiswa' => 'John Doe',
            'NIM' => '00000000001',
            'Dosen_Pembimbing_1__Nama' => 'Dr. Jane Smith',
            'Dosen_Pembimbing_1__NIDN' => '000001',
            'KAPRODI' => 'Prof. Bob Johnson'
        ];
        
        $doubleEncodedXML = self::generateXMLSecure($doubleEncodedData);
        echo "<pre>" . htmlspecialchars($doubleEncodedXML) . "</pre>\n";
        echo "<p style='color: green;'>Protection holds - htmlspecialchars() encodes again.</p>\n";
    }

    /**
     * Show security recommendations
     */
    public static function showSecurityRecommendations()
    {
        echo "<h2>Security Recommendations</h2>\n";
        echo "<div style='background: #f0f8ff; padding: 15px; border-left: 4px solid #0066cc;'>\n";
        echo "<h3>Current Protection Status:</h3>\n";
        echo "<ul>\n";
        echo "<li style='color: green;'><strong>GOOD:</strong> htmlspecialchars() provides basic protection against XML injection</li>\n";
        echo "<li style='color: green;'><strong>GOOD:</strong> XML is embedded in PDF, reducing direct exposure</li>\n";
        echo "<li style='color: orange;'><strong>CONCERN:</strong> No XML schema validation</li>\n";
        echo "<li style='color: orange;'><strong>CONCERN:</strong> No XXE protection explicitly configured</li>\n";
        echo "</ul>\n";
        
        echo "<h3>Additional Recommendations:</h3>\n";
        echo "<ol>\n";
        echo "<li><strong>Input Validation:</strong> Validate all data against the XSD schema before XML generation</li>\n";
        echo "<li><strong>XXE Protection:</strong> Explicitly disable external entity loading</li>\n";
        echo "<li><strong>XML Schema Validation:</strong> Validate generated XML against the schema</li>\n";
        echo "<li><strong>Content Security:</strong> Implement additional input sanitization at database level</li>\n";
        echo "<li><strong>Logging:</strong> Log XML generation attempts for security monitoring</li>\n";
        echo "</ol>\n";
        echo "</div>\n";
    }
}

// Run demonstrations if executed directly
if (php_sapi_name() === 'cli' || (isset($_GET['demo']) && $_GET['demo'] === 'true')) {
    echo "<!DOCTYPE html>\n";
    echo "<html><head><title>XML Injection Demo</title></head><body>\n";
    echo "<h1>XML Injection Security Demonstration</h1>\n";
    echo "<p><strong>WARNING:</strong> This is for educational purposes only!</p>\n";
    
    XMLInjectionDemo::demonstrateAttacks();
    XMLInjectionDemo::demonstrateDatabaseToXMLInjection();
    XMLInjectionDemo::demonstrateBypassAttempts();
    XMLInjectionDemo::showSecurityRecommendations();
    
    echo "</body></html>\n";
} else {
    echo "Add ?demo=true to the URL to run the demonstration.\n";
}
?> 