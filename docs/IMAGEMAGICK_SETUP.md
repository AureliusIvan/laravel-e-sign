# ImageMagick Configuration for PDF Processing

## Issue
The error `attempt to perform an operation not allowed by the security policy 'PDF'` occurs when ImageMagick's security policy blocks PDF operations.

## Solution

### For Docker Environment (Recommended)
The Dockerfile has been updated to automatically fix this issue. When rebuilding the container:
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Manual Fix for Running Container
If you need to fix this in a running container:
```bash
# Check current policy
docker exec -it laravel_app grep -A 5 -B 5 "PDF" /etc/ImageMagick-6/policy.xml

# Fix the policy
docker exec -it laravel_app sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/' /etc/ImageMagick-6/policy.xml

# Verify the fix
docker exec -it laravel_app grep -A 5 -B 5 "PDF" /etc/ImageMagick-6/policy.xml
```

### Testing the Fix
Test PDF processing with:
```bash
docker exec -it laravel_app php -r "
require_once 'vendor/autoload.php';
try {
    \$pdf = new Spatie\PdfToImage\Pdf('storage/sample.pdf');
    echo 'Success! PDF has ' . \$pdf->pageCount() . ' pages.' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

### Manual Fix for Host System
If running outside Docker:
```bash
# Find the policy file
find /etc -name "policy.xml" 2>/dev/null

# Update the policy (replace path as needed)
sudo sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/' /etc/ImageMagick-6/policy.xml
```

## Security Considerations
- Only enable PDF read/write if needed for your application
- Consider limiting PDF processing to specific directories
- Monitor for potential security vulnerabilities in PDF processing

## Related Files
- `Dockerfile` - Contains the automatic fix
- `app/Http/Controllers/SignatureController.php` - Enhanced error handling 