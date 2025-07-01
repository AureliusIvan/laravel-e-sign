#!/bin/bash

# XAdES Key Generation Script for PHP Sign System
# This script generates a private key and self-signed certificate for XAdES digital signatures

set -e

echo "=== XAdES Key Generation Setup ==="
echo "Generating private key and certificate for digital signatures..."

# Define paths
KEYS_DIR="storage/app/keys"
PRIVATE_KEY_PATH="$KEYS_DIR/system_private.key"
CERTIFICATE_PATH="$KEYS_DIR/system_cert.crt"

# Create keys directory if it doesn't exist
if [ ! -d "$KEYS_DIR" ]; then
    echo "Creating keys directory: $KEYS_DIR"
    mkdir -p "$KEYS_DIR"
    chmod 700 "$KEYS_DIR"
fi

# Check if keys already exist
if [ -f "$PRIVATE_KEY_PATH" ] && [ -f "$CERTIFICATE_PATH" ]; then
    echo "Keys already exist. Do you want to regenerate them? (y/N)"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        echo "Keeping existing keys."
        exit 0
    fi
    echo "Regenerating keys..."
fi

# Generate private key
echo "Generating 2048-bit RSA private key..."
openssl genrsa -out "$PRIVATE_KEY_PATH" 2048

# Set secure permissions for private key
chmod 600 "$PRIVATE_KEY_PATH"

# Generate certificate signing request (CSR) configuration
CSR_CONFIG=$(mktemp)
cat > "$CSR_CONFIG" << EOF
[req]
default_bits = 2048
prompt = no
distinguished_name = dn
req_extensions = v3_req

[dn]
CN=UMN Digital Signature System
O=Universitas Multimedia Nusantara
C=ID
ST=Banten
L=Tangerang

[v3_req]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
extendedKeyUsage = emailProtection, timeStamping
EOF

# Generate self-signed certificate (valid for 10 years)
echo "Generating self-signed certificate (valid for 10 years)..."
openssl req -new -x509 -key "$PRIVATE_KEY_PATH" -out "$CERTIFICATE_PATH" -days 3650 -config "$CSR_CONFIG" -extensions v3_req

# Set permissions for certificate
chmod 644 "$CERTIFICATE_PATH"

# Clean up temporary config file
rm "$CSR_CONFIG"

# Verify the generated files
echo "Verifying generated keys..."

if [ ! -f "$PRIVATE_KEY_PATH" ]; then
    echo "ERROR: Private key was not generated successfully"
    exit 1
fi

if [ ! -f "$CERTIFICATE_PATH" ]; then
    echo "ERROR: Certificate was not generated successfully"
    exit 1
fi

# Test private key validity
if ! openssl rsa -in "$PRIVATE_KEY_PATH" -check -noout > /dev/null 2>&1; then
    echo "ERROR: Generated private key is invalid"
    exit 1
fi

# Test certificate validity
if ! openssl x509 -in "$CERTIFICATE_PATH" -text -noout > /dev/null 2>&1; then
    echo "ERROR: Generated certificate is invalid"
    exit 1
fi

# Display certificate information
echo ""
echo "=== Certificate Information ==="
openssl x509 -in "$CERTIFICATE_PATH" -text -noout | grep -E "(Subject:|Not Before:|Not After:|Public Key Algorithm:|Signature Algorithm:)"

echo ""
echo "=== Key Generation Complete ==="
echo "Private Key: $PRIVATE_KEY_PATH"
echo "Certificate: $CERTIFICATE_PATH"
echo ""
echo "IMPORTANT SECURITY NOTES:"
echo "1. The private key is stored with 600 permissions (owner read/write only)"
echo "2. Keep the private key secure and never share it"
echo "3. The certificate is self-signed and valid for 10 years"
echo "4. For production use, consider using a certificate from a trusted CA"
echo ""
echo "The XAdES digital signature system is now ready for use." 