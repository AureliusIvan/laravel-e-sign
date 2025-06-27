# ImageMagick Setup and Troubleshooting

## Overview
This application uses ImageMagick for PDF processing in the document signing feature. PDF processing errors like "Dereference of free object" indicate ImageMagick security policy restrictions.

## Common Issues and Solutions

### 1. ImageMagick Security Policy Error
**Error symptoms:**
- "Dereference of free object 140, next object number as offset failed"
- "Error: Couldn't get page info"
- "Error: page not found"
- "security policy" errors in logs

**Solution:**
Update ImageMagick policy configuration to allow PDF processing.

### 2. Docker Environment Setup

#### For Development (Docker)
Add this to your `Dockerfile` or run in container:

```bash
# Fix ImageMagick policy for PDF processing
RUN sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/g' /etc/ImageMagick-6/policy.xml || \
    sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/g' /etc/ImageMagick-7/policy.xml || true

# Alternative: completely remove PDF restriction
RUN sed -i '/PDF/d' /etc/ImageMagick-*/policy.xml || true
```

#### Quick Fix (Run in container)
```bash
# Enter the Laravel container
docker exec -it laravel_app bash

# Check current policy
cat /etc/ImageMagick-*/policy.xml | grep PDF

# Fix the policy (choose one method)
# Method 1: Enable PDF rights
sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/g' /etc/ImageMagick-*/policy.xml

# Method 2: Remove PDF restrictions entirely
sed -i '/PDF/d' /etc/ImageMagick-*/policy.xml

# Verify the fix
cat /etc/ImageMagick-*/policy.xml | grep PDF
```

### 3. System Dependencies
Ensure required packages are installed:

```bash
# In your Dockerfile or container
RUN apt-get update && apt-get install -y \
    imagemagick \
    ghostscript \
    libmagickwand-dev \
    poppler-utils

# Install PHP ImageMagick extension
RUN docker-php-ext-install imagick
```

### 4. Alternative: Makefile Command
Add this to your `Makefile`:

```makefile
fix-imagemagick:
	@echo "Fixing ImageMagick policy for PDF processing..."
	@docker exec laravel_app bash -c "sed -i 's/<policy domain=\"coder\" rights=\"none\" pattern=\"PDF\" \/>/<policy domain=\"coder\" rights=\"read|write\" pattern=\"PDF\" \/>/g' /etc/ImageMagick-*/policy.xml || true"
	@docker exec laravel_app bash -c "sed -i '/pattern=\"PDF\"/d' /etc/ImageMagick-*/policy.xml || true"
	@echo "ImageMagick policy updated. Please test PDF signing functionality."
```

Then run: `make fix-imagemagick`

### 5. Debugging Commands

```bash
# Check ImageMagick version and configuration
docker exec laravel_app identify -version

# Test PDF conversion
docker exec laravel_app convert /path/to/test.pdf /tmp/test.jpg

# Check policy file locations
docker exec laravel_app find /etc -name "policy.xml" -type f

# View current PDF policy
docker exec laravel_app grep -A5 -B5 PDF /etc/ImageMagick-*/policy.xml
```

### 6. Environment Variables
You can also set these environment variables in your `.env` file:

```bash
# Disable ImageMagick security policy (development only)
MAGICK_SECURITY_POLICY=/dev/null
```

### 7. Application Fallback
The application has built-in fallback mechanisms:
1. **Primary**: FPDI library for PDF manipulation
2. **Secondary**: ImageMagick-based image conversion
3. **Fallback**: Error handling with user-friendly messages

### 8. Production Considerations
- **Security**: Only enable PDF processing for trusted files
- **Performance**: Consider file size limits and processing timeouts
- **Storage**: Ensure adequate disk space for temporary files
- **Monitoring**: Log PDF processing performance and failures

### 9. Testing the Fix
After applying the fix, test with:
1. Upload a test PDF in the signing interface
2. Try to sign the document
3. Check application logs for any remaining errors

### 10. Common Error Messages and Solutions

| Error Message | Cause | Solution |
|---------------|-------|----------|
| "security policy" | ImageMagick blocking PDF | Update policy.xml |
| "Dereference of free object" | Corrupt PDF or policy issue | Check PDF integrity + fix policy |
| "page not found" | PDF parsing failure | Use image-based fallback |
| "Failed to generate QR code" | Missing dependencies | Install required packages |

## Troubleshooting Checklist
- [ ] ImageMagick is installed
- [ ] Ghostscript is installed  
- [ ] PDF policy is enabled in policy.xml
- [ ] Temporary directory has write permissions
- [ ] PDF file is not corrupted
- [ ] File size is within limits (< 50MB)
- [ ] Check application logs for specific errors 