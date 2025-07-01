# XAdES Quick Start Guide

## What is XAdES?

XAdES (XML Advanced Electronic Signatures) adds **ETSI-compliant** cryptographic digital signatures to the XML metadata embedded in your thesis PDFs. This provides:

- **Document Integrity**: Proves the XML metadata hasn't been modified
- **Authentication**: Verifies the signature comes from your system
- **Non-repudiation**: Creates legal proof of document origin
- **ETSI Standard Compliance**: Follows TS 101 903 specification

## 🚀 Quick Setup (3 Steps)

### 1. Enable XAdES
Add to your `.env` file:
```bash
XADES_ENABLED=true
```

### 2. Generate Keys

**For Docker Environment** (recommended):
```bash
# Laravel command in Docker container
docker exec laravel_app php artisan xades:generate-keys

# Or shell script in container  
docker exec laravel_app ./setup-xades-keys.sh
```

**For Direct PHP Installation**:
```bash
# Laravel command (recommended)
php artisan xades:generate-keys

# Or shell script
./setup-xades-keys.sh
```

### 3. Verify Setup
```bash
# Docker environment
docker exec laravel_app php artisan xades:verify-setup

# Direct PHP
php artisan xades:verify-setup
```

✅ **XAdES is now automatically integrated into your PDF signing system!**

## 🔍 What Changes?

### Before XAdES
```
PDF Signing: PDF → Add QR → Embed XML → Save
Verification: PDF → Extract XML → Verify Hash
```

### After XAdES  
```
PDF Signing: PDF → Add QR → Generate XML → Sign XML → Embed Signed XML → Save
Verification: PDF → Extract XML → Verify XAdES → Verify Hash
```

## 🧪 Testing

### Test the entire system:
```bash
php artisan xades:verify-setup
```

### Test in code:
```php
use App\Http\Controllers\SignatureController;

$controller = new SignatureController();
$result = $controller->validateXmlSanitizationSystem();
echo "Security Score: " . $result['security_score'] . "%";
```

## 📁 Files Created

After setup, you'll have:
```
storage/app/keys/
├── system_private.key  (RSA private key - keep secure!)
└── system_cert.crt     (Public certificate)
```

## ⚠️ Important Notes

1. **Backup your keys!** If you lose `system_private.key`, you can't verify old signatures.

2. **Secure permissions**: The setup automatically sets correct permissions:
   - Private key: `600` (owner read/write only)
   - Certificate: `644` (world readable)
   - Keys directory: `700` (owner access only)

3. **Self-signed certificates**: Default setup uses self-signed certificates (valid for 10 years). For production, consider CA-signed certificates.

## 🔧 Configuration Options

In `config/app.php`, you can customize:
```php
'xades' => [
    'enabled' => env('XADES_ENABLED', true),
    'key_size' => 2048,                    // Key strength
    'certificate_days' => 3650,            // Certificate validity (10 years)
    'organization' => 'Your Organization', // Certificate details
    // ... other options
],
```

## 🛠️ Troubleshooting

### 🚨 Common PDF Processing Issues

**Problem**: `"File PDF rusak atau tidak valid: File PDF mengandung referensi melingkar"`
- **Cause**: Academic PDFs often have complex structures flagged as "circular references"
- **Solution**: Enable fast signing mode
```bash
# For Docker environment
docker exec laravel_app bash -c 'echo "FAST_SIGNING_MODE=true" >> .env'

# For direct PHP installation  
echo "FAST_SIGNING_MODE=true" >> .env
```

**Problem**: `"504 Gateway Timeout"` during signing
- **Cause**: Large PDFs take too long to process
- **Solution**: Same as above - enable fast signing mode

**Problem**: `"File mengandung konten berbahaya"` 
- **Cause**: Academic PDFs flagged by security checks
- **Solution**: Adjust security settings
```bash
# Add to .env file
ALLOW_UNCLEANED_PDFS=true
```

### 📋 General Issues

| Problem | Solution |
|---------|----------|
| "OpenSSL extension not found" | Install PHP OpenSSL extension |
| "Permission denied" | Ensure web server can write to `storage/` |
| "Keys not found" | Run `php artisan xades:generate-keys` |
| "Verification failed" | Check if XML was modified or keys corrupted |

## 📊 Verification Results

After implementing XAdES, your verification page will show:
- ✅ **Hash Verification**: Document integrity check
- ✅ **XAdES Verification**: Digital signature validation
- 📝 **Signature Details**: Signing time, certificate info

## 🔄 Maintenance

### Regular Tasks:
- **Monthly**: Check certificate expiration with `php artisan xades:verify-setup`
- **Before expiry**: Regenerate keys with `php artisan xades:generate-keys --force`

### Monitoring:
Check `storage/logs/laravel.log` for XAdES events:
```
[timestamp] INFO: XAdES signature added successfully
[timestamp] INFO: XAdES signature verified successfully
```

## 💡 Pro Tips

1. **Test first**: Always run `php artisan xades:verify-setup` after setup
2. **Backup strategy**: Include `storage/app/keys/` in your backup routine
3. **Production upgrade**: Consider using CA-signed certificates for production environments
4. **Performance**: XAdES adds minimal overhead (~100ms per signature)

## 🎯 Next Steps

Once XAdES is working:
1. Test the full signing workflow
2. Verify that signed documents include XAdES signatures
3. Test document verification with signed PDFs
4. Monitor logs for any issues
5. Set up regular key backup procedures

## 📞 Need Help?

1. Run: `php artisan xades:verify-setup` for diagnostics
2. Check: `storage/logs/laravel.log` for error details  
3. Review: `docs/XADES_IMPLEMENTATION.md` for detailed documentation

---

**Ready to go!** Your thesis signing system now includes XAdES digital signatures for enhanced security and legal compliance. 