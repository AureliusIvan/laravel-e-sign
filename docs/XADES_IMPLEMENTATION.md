# XAdES Digital Signature Implementation

This document describes the XAdES (XML Advanced Electronic Signatures) implementation in the PHP Sign System for digitally signing thesis documents.

## Overview

The XAdES implementation provides:
- **Basic XAdES-BES level signatures** for XML metadata
- **Automatic signature generation** during PDF signing process
- **Signature verification** during document validation
- **Self-signed certificate system** (can be upgraded to CA-signed)
- **Comprehensive security validation**

## Features

### ✅ Implemented Features
- XAdES-BES level digital signatures
- Private key and certificate generation
- Automatic XML signing during thesis processing
- Signature verification during document validation
- Secure key storage with proper permissions
- Configuration management via environment variables
- Artisan commands for key management and verification
- Comprehensive security testing

### 🔧 Security Features
- RSA 2048-bit cryptographic keys
- SHA-256 signature algorithm
- XML canonicalization for consistent signing
- Certificate validity checking
- Private key protection (600 permissions)
- XAdES signature verification with integrity checks

## Installation & Setup

### Prerequisites
- PHP with OpenSSL extension enabled
- Laravel framework
- File system write permissions for key storage

### 1. Environment Configuration

Add to your `.env` file:
```bash
# XAdES Digital Signature Configuration
XADES_ENABLED=true
```

### 2. Generate Cryptographic Keys

Choose one of the following methods:

#### Option A: Using Artisan Command (Recommended)
```bash
php artisan xades:generate-keys
```

#### Option B: Using Shell Script
```bash
chmod +x setup-xades-keys.sh
./setup-xades-keys.sh
```

### 3. Verify Setup

Run the comprehensive verification:
```bash
php artisan xades:verify-setup
```

This will check:
- Configuration validity
- OpenSSL extension
- Key and certificate integrity
- File permissions
- XML sanitization system
- XAdES signature functionality

## Usage

### Automatic Integration

The XAdES system is automatically integrated into the existing PDF signing workflow:

1. **During PDF Signing**: When a thesis is signed, the XML metadata is automatically digitally signed with XAdES before being embedded in the PDF.

2. **During Verification**: When a PDF is uploaded for verification, the XAdES signature is automatically verified along with the hash verification.

### Manual Testing

You can test the XAdES functionality programmatically:

```php
use App\Http\Controllers\SignatureController;

$controller = new SignatureController();

// Test XML sanitization system
$sanitizationResult = $controller->validateXmlSanitizationSystem();

// Get XAdES configuration
$config = $controller->getXmlSanitizationConfig();
```

## Configuration

### Default Configuration

The XAdES system is configured in `config/app.php`:

```php
'xades' => [
    'enabled' => env('XADES_ENABLED', true),
    'private_key_path' => storage_path('app/keys/system_private.key'),
    'certificate_path' => storage_path('app/keys/system_cert.crt'),
    'key_size' => 2048,
    'certificate_days' => 3650, // 10 years
    'organization' => 'Universitas Multimedia Nusantara',
    'country' => 'ID',
    'state' => 'Banten',
    'city' => 'Tangerang',
    'algorithm' => OPENSSL_ALGO_SHA256,
],
```

### Customization

You can customize the configuration by:

1. **Environment Variables**: Set `XADES_ENABLED=false` to disable XAdES
2. **Configuration File**: Modify `config/app.php` for organization details
3. **Certificate Validity**: Adjust `certificate_days` for certificate lifespan

## File Structure

```
storage/
├── app/
│   └── keys/                   # XAdES cryptographic keys
│       ├── system_private.key  # Private key (600 permissions)
│       └── system_cert.crt     # Public certificate (644 permissions)
```

## Security Considerations

### ✅ Security Measures Implemented

1. **Strong Cryptography**:
   - RSA 2048-bit keys minimum
   - SHA-256 signature algorithm
   - Secure random number generation

2. **Key Protection**:
   - Private keys stored with 600 permissions (owner read/write only)
   - Keys directory with 700 permissions
   - Automatic permission validation

3. **XML Security**:
   - XXE (XML External Entity) attack prevention
   - Content sanitization and validation
   - Size and complexity limits
   - Schema validation

4. **Signature Integrity**:
   - XML canonicalization for consistent signatures
   - Digest value verification
   - Certificate validation
   - Signature verification with original content

### ⚠️ Security Notes

1. **Self-Signed Certificates**: The default implementation uses self-signed certificates. For production environments with higher security requirements, consider using CA-signed certificates.

2. **Key Backup**: Ensure regular backup of the private key and certificate. Loss of the private key means inability to generate compatible signatures.

3. **Certificate Expiration**: Monitor certificate expiration dates. The system will warn when certificates expire within 30 days.

4. **Access Control**: Ensure only authorized personnel have access to the server where private keys are stored.

## Troubleshooting

### Common Issues

#### 1. OpenSSL Extension Not Found
```
Error: OpenSSL extension is not loaded
Solution: Install and enable the OpenSSL PHP extension
```

#### 2. Permission Denied
```
Error: Failed to create keys directory
Solution: Ensure web server has write permissions to storage directory
```

#### 3. Keys Not Found
```
Error: Private key not found
Solution: Run 'php artisan xades:generate-keys' to generate keys
```

#### 4. Signature Verification Failed
```
Error: XAdES signature verification failed
Solution: Check if XML content has been modified or keys are corrupted
```

### Verification Steps

1. **Check Configuration**:
   ```bash
   php artisan xades:verify-setup
   ```

2. **Regenerate Keys** (if corrupted):
   ```bash
   php artisan xades:generate-keys --force
   ```

3. **Check File Permissions**:
   ```bash
   ls -la storage/app/keys/
   # Should show: -rw------- for private key, -rw-r--r-- for certificate
   ```

4. **Test Functionality**:
   ```bash
   php artisan tinker
   >>> $controller = new \App\Http\Controllers\SignatureController();
   >>> $result = $controller->validateXmlSanitizationSystem();
   >>> print_r($result);
   ```

## Advanced Configuration

### Using CA-Signed Certificates

For production environments, you can replace the self-signed certificate with a CA-signed certificate:

1. Generate a Certificate Signing Request (CSR):
   ```bash
   openssl req -new -key storage/app/keys/system_private.key -out request.csr
   ```

2. Submit the CSR to a Certificate Authority

3. Replace the self-signed certificate with the CA-signed certificate:
   ```bash
   cp ca_signed_certificate.crt storage/app/keys/system_cert.crt
   chmod 644 storage/app/keys/system_cert.crt
   ```

4. Verify the new setup:
   ```bash
   php artisan xades:verify-setup
   ```

### Certificate Renewal

When certificates are about to expire:

1. **Generate new keys** (if needed):
   ```bash
   php artisan xades:generate-keys --force
   ```

2. **Test the new setup**:
   ```bash
   php artisan xades:verify-setup
   ```

3. **Update any external systems** that depend on the certificate

## Integration with Existing System

The XAdES implementation is seamlessly integrated with the existing PDF signing system:

### Signing Process Flow

1. User initiates PDF signing
2. System validates PDF security
3. QR code is generated and embedded
4. **XML metadata is generated**
5. **XAdES signature is added to XML** ← NEW
6. **Signed XML is embedded in PDF** ← ENHANCED
7. PDF hash is calculated and stored
8. Process completion notification

### Verification Process Flow

1. User uploads PDF for verification
2. **XML metadata is extracted from PDF**
3. **XAdES signature is verified** ← NEW
4. XML content is sanitized and parsed
5. PDF hash is calculated and compared
6. **Verification results include XAdES status** ← NEW
7. Results are displayed to user

## API Reference

### Configuration Methods

```php
// Get XAdES configuration
$config = config('app.xades');

// Check if XAdES is enabled
$enabled = config('app.xades.enabled');
```

### Controller Methods

```php
use App\Http\Controllers\SignatureController;

$controller = new SignatureController();

// Validate XML sanitization system
$result = $controller->validateXmlSanitizationSystem();

// Get sanitization configuration
$config = $controller->getXmlSanitizationConfig();
```

### Artisan Commands

```bash
# Generate XAdES keys
php artisan xades:generate-keys [--force]

# Verify XAdES setup
php artisan xades:verify-setup
```

## Compliance & Standards

This implementation follows:

- **XAdES-BES** (Basic Electronic Signature) standard
- **XML-DSIG** (XML Digital Signature) specification
- **RSA PKCS#1** signature standard
- **SHA-256** cryptographic hash standard
- **XML Canonicalization** for consistent signature generation

## Support & Maintenance

### Regular Maintenance Tasks

1. **Monthly**: Check certificate expiration status
2. **Quarterly**: Run full system verification
3. **Yearly**: Consider certificate renewal
4. **As needed**: Update cryptographic parameters based on security recommendations

### Monitoring

The system automatically logs:
- XAdES signature generation events
- Signature verification results
- Key generation and validation events
- Security violation attempts
- System configuration changes

Check logs in `storage/logs/laravel.log` for XAdES-related events.

## Version History

- **v1.0.0**: Initial XAdES implementation with basic signature support
- **v1.0.1**: Added comprehensive verification and testing
- **v1.0.2**: Enhanced security validation and error handling

## Contact & Support

For issues related to XAdES implementation:
1. Check this documentation
2. Run `php artisan xades:verify-setup` for diagnostics
3. Review Laravel logs for error details
4. Contact system administrator if issues persist 