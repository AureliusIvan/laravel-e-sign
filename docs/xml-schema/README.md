# XML Schema and Security Analysis for Thesis Metadata System

## Overview

This directory contains XML schema definitions and security analysis for the thesis metadata system used in the digital signature functionality of the SignatureController.php.

## Files Structure

```
docs/xml-schema/
├── README.md                           # This documentation
├── thesis-metadata.xsd                 # XML Schema Definition
└── examples/
    ├── legitimate-thesis-metadata.xml  # Valid XML example
    ├── xml-injection-attacks.xml      # Attack examples
    └── xml-injection-demo.php         # PHP demonstration script
```

## XML Schema Overview

### Legitimate XML Structure

Based on the `SignatureController.php` implementation, the system generates XML metadata with the following structure:

```xml
<root xmlns="http://umn.ac.id/thesis-metadata">
    <UUID>550e8400-e29b-41d4-a716-446655440000</UUID>
    <Tipe_Laporan>Skripsi</Tipe_Laporan>
    <Judul_Laporan>Thesis Title</Judul_Laporan>
    <Judul_Laporan_EN>English Title</Judul_Laporan_EN>
    <Prodi>Teknik Informatika</Prodi>
    <Tahun>2024</Tahun>
    <Nama_Mahasiswa>Student Name</Nama_Mahasiswa>
    <NIM>00000000001</NIM>
    <Dosen_Pembimbing_1__Nama>Supervisor Name</Dosen_Pembimbing_1__Nama>
    <Dosen_Pembimbing_1__NIDN>000001</Dosen_Pembimbing_1__NIDN>
    <KAPRODI>Head of Program Name</KAPRODI>
</root>
```

### Data Flow

1. **Source**: Data comes from database tables (`proposal_skripsi`, `mahasiswa`, `dosen`)
2. **Processing**: PHP array → `generateXML()` → `arrayToXML()` with `htmlspecialchars()`
3. **Output**: XML embedded in PDF as metadata for verification
4. **Verification**: XML extracted from PDF and validated against hash

## Current Security Implementation

### Protection Mechanisms ✅

1. **Input Sanitization**: Uses `htmlspecialchars($value)` in `arrayToXML()` method
2. **PDF Embedding**: XML is embedded in PDF, not directly exposed
3. **Hash Verification**: Document integrity protected by SHA-512 hash
4. **Controlled Data Sources**: Data comes from validated database fields

### Current Code (SignatureController.php):

```php
private static function arrayToXML(array $data, \SimpleXMLElement &$xml): void
{
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $child = $xml->addChild($key);
            self::arrayToXML($value, $child);
        } else {
            // PROTECTION: htmlspecialchars() prevents basic XML injection
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
}
```

## Potential Vulnerabilities

### 1. XML External Entity (XXE) Attacks

**Risk Level**: 🟡 Medium  
**Attack Vector**: If XML parser processes external entities

```xml
<!DOCTYPE root [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<root>
    <Judul_Laporan>Title &xxe;</Judul_Laporan>
</root>
```

**Current Status**: The generation process doesn't create DTDs, but verification parsing could be vulnerable.

### 2. XML Bomb (Billion Laughs)

**Risk Level**: 🟡 Medium  
**Attack Vector**: Denial of Service through recursive entity expansion

```xml
<!DOCTYPE root [
  <!ENTITY lol "lol">
  <!ENTITY lol2 "&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;&lol;">
  <!ENTITY lol3 "&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;&lol2;">
]>
```

### 3. Database-to-XML Injection Chain

**Risk Level**: 🟢 Low (Currently Protected)  
**Attack Vector**: Malicious data in database → XML injection

```php
// If database contains:
$maliciousTitle = 'Normal Title</Judul_Laporan><admin_access>true</admin_access><Judul_Laporan>';

// Current protection prevents this:
htmlspecialchars($maliciousTitle) 
// Result: "Normal Title&lt;/Judul_Laporan&gt;&lt;admin_access&gt;true&lt;/admin_access&gt;&lt;Judul_Laporan&gt;"
```

### 4. XML Structure Manipulation

**Risk Level**: 🟢 Low (Currently Protected)  
**Attack Vector**: Breaking XML structure to inject elements

**Example Attack**:
```
Student Name: John</Nama_Mahasiswa><backdoor>admin</backdoor><Nama_Mahasiswa>Doe
```

**Protection**: `htmlspecialchars()` encodes `<` and `>` characters.

## Security Recommendations

### ✅ Immediate (Low Risk)

1. **XML Schema Validation**
   ```php
   // Add schema validation
   $doc = new DOMDocument();
   $doc->loadXML($xmlContent);
   if (!$doc->schemaValidate('thesis-metadata.xsd')) {
       throw new InvalidArgumentException('XML does not conform to schema');
   }
   ```

2. **XXE Protection**
   ```php
   // Disable external entity loading
   libxml_disable_entity_loader(true);
   
   // Or configure parser explicitly
   $doc = new DOMDocument();
   $doc->resolveExternals = false;
   $doc->substituteEntities = false;
   ```

### 🔶 Medium Priority

3. **Enhanced Input Validation**
   ```php
   private static function validateXMLData(array $data): bool
   {
       $schema = [
           'UUID' => '/^[0-9a-f-]{36}$/i',
           'NIM' => '/^[0-9]{11}$/',
           'Tahun' => '/^20[0-9]{2}$/',
           // ... more validations
       ];
       
       foreach ($schema as $field => $pattern) {
           if (isset($data[$field]) && !preg_match($pattern, $data[$field])) {
               return false;
           }
       }
       return true;
   }
   ```

4. **Content Length Limits**
   ```php
   private static function sanitizeValue($value): string
   {
       $value = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
       return substr($value, 0, 500); // Limit length
   }
   ```

### 🔴 Long-term Improvements

5. **XML Digital Signatures**
   - Implement XML-DSig for additional integrity protection
   - Use W3C XML Signature standards

6. **Audit Logging**
   ```php
   Log::info('XML metadata generated', [
       'proposal_id' => $proposal->id,
       'user_id' => Auth::id(),
       'xml_hash' => hash('sha256', $xmlContent),
       'timestamp' => now()
   ]);
   ```

## Testing the Vulnerabilities

### Running the Demo

1. **Web Interface**:
   ```
   http://localhost/docs/xml-schema/examples/xml-injection-demo.php?demo=true
   ```

2. **Command Line**:
   ```bash
   php docs/xml-schema/examples/xml-injection-demo.php
   ```

### Test Cases Included

- ✅ Legitimate XML generation
- ❌ Basic XML injection attempts
- ❌ CDATA injection
- ❌ Unicode/Entity bypass attempts
- ⚠️ XXE vulnerability testing
- ✅ Database-to-XML injection simulation

## Compliance and Standards

### XML Standards Compliance
- **XML 1.0**: ✅ Compatible
- **XML Schema (XSD)**: ✅ Defined
- **XML Namespaces**: ✅ Implemented
- **UTF-8 Encoding**: ✅ Used

### Security Standards
- **OWASP XML Security**: 🔶 Partially compliant
- **XML Encryption**: ❌ Not implemented
- **XML Digital Signature**: ❌ Not implemented

## Risk Assessment Summary

| Attack Vector | Risk Level | Current Protection | Recommendation |
|---------------|------------|-------------------|----------------|
| XML Injection | 🟢 Low | htmlspecialchars() | Schema validation |
| XXE Attacks | 🟡 Medium | None explicit | Disable entities |
| XML Bomb | 🟡 Medium | None | Parser limits |
| CDATA Injection | 🟢 Low | htmlspecialchars() | Additional validation |
| Namespace Injection | 🟢 Low | Fixed namespace | Schema validation |

## Conclusion

The current implementation provides **basic but adequate protection** against most XML injection attacks through the use of `htmlspecialchars()`. However, implementing the recommended improvements would provide **defense in depth** and better protection against sophisticated attacks.

The most critical improvements are:
1. **XXE protection** in XML parsing
2. **Schema validation** for generated XML
3. **Enhanced logging** for security monitoring

## Contact

For security-related questions or to report vulnerabilities, please contact the development team through the appropriate channels. 