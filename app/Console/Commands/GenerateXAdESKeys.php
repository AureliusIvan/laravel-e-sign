<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateXAdESKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xades:generate-keys {--force : Force regeneration of existing keys}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate XAdES private key and certificate for digital signatures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('XAdES Key Generation for Digital Signatures');
        $this->info('==========================================');

        $config = config('app.xades');
        
        if (!$config['enabled']) {
            $this->error('XAdES is disabled in configuration. Set XADES_ENABLED=true in .env');
            return 1;
        }

        $privateKeyPath = $config['private_key_path'];
        $certificatePath = $config['certificate_path'];
        $keysDir = dirname($privateKeyPath);

        // Create keys directory
        if (!is_dir($keysDir)) {
            $this->info("Creating keys directory: {$keysDir}");
            if (!mkdir($keysDir, 0700, true)) {
                $this->error("Failed to create keys directory: {$keysDir}");
                return 1;
            }
        }

        // Check if keys exist
        if (file_exists($privateKeyPath) && file_exists($certificatePath)) {
            if (!$this->option('force')) {
                if (!$this->confirm('Keys already exist. Do you want to regenerate them?', false)) {
                    $this->info('Keeping existing keys.');
                    return 0;
                }
            }
            $this->info('Regenerating keys...');
        }

        try {
            // Generate private key
            $this->info('Generating 2048-bit RSA private key...');
            $privateKey = $this->generatePrivateKey($config['key_size']);
            
            if (!file_put_contents($privateKeyPath, $privateKey)) {
                throw new \Exception("Failed to write private key to: {$privateKeyPath}");
            }
            chmod($privateKeyPath, 0600);

            // Generate certificate
            $this->info('Generating self-signed certificate...');
            $certificate = $this->generateCertificate($privateKey, $config);
            
            if (!file_put_contents($certificatePath, $certificate)) {
                throw new \Exception("Failed to write certificate to: {$certificatePath}");
            }
            chmod($certificatePath, 0644);

            // Verify generated files
            $this->info('Verifying generated keys...');
            $this->verifyKeys($privateKeyPath, $certificatePath);

            // Display certificate info
            $this->displayCertificateInfo($certificatePath);

            $this->info('');
            $this->info('✅ XAdES keys generated successfully!');
            $this->info("Private Key: {$privateKeyPath}");
            $this->info("Certificate: {$certificatePath}");
            $this->info('');
            $this->warn('IMPORTANT SECURITY NOTES:');
            $this->warn('• Keep the private key secure and never share it');
            $this->warn('• The certificate is self-signed and valid for ' . $config['certificate_days'] . ' days');
            $this->warn('• For production use, consider using a certificate from a trusted CA');

            Log::info('XAdES keys generated successfully via Artisan command');

        } catch (\Exception $e) {
            $this->error('Failed to generate XAdES keys: ' . $e->getMessage());
            Log::error('XAdES key generation failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Generate a private key
     */
    private function generatePrivateKey(int $keySize): string
    {
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => $keySize,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $privkey = openssl_pkey_new($config);
        if (!$privkey) {
            throw new \Exception('Failed to generate private key: ' . openssl_error_string());
        }

        if (!openssl_pkey_export($privkey, $pkeyout)) {
            throw new \Exception('Failed to export private key: ' . openssl_error_string());
        }

        return $pkeyout;
    }

    /**
     * Generate a self-signed certificate
     */
    private function generateCertificate(string $privateKey, array $config): string
    {
        $dn = [
            "countryName" => $config['country'],
            "stateOrProvinceName" => $config['state'],
            "localityName" => $config['city'],
            "organizationName" => $config['organization'],
            "commonName" => "UMN Digital Signature System"
        ];

        $privkey = openssl_pkey_get_private($privateKey);
        if (!$privkey) {
            throw new \Exception('Failed to read private key: ' . openssl_error_string());
        }

        $csr = openssl_csr_new($dn, $privkey, [
            'digest_alg' => 'sha256',
            'x509_extensions' => 'v3_ca',
            'req_extensions' => 'v3_req'
        ]);

        if (!$csr) {
            throw new \Exception('Failed to create certificate signing request: ' . openssl_error_string());
        }

        $cert = openssl_csr_sign($csr, null, $privkey, $config['certificate_days'], [
            'digest_alg' => 'sha256',
            'x509_extensions' => 'v3_ca'
        ]);

        if (!$cert) {
            throw new \Exception('Failed to sign certificate: ' . openssl_error_string());
        }

        if (!openssl_x509_export($cert, $certout)) {
            throw new \Exception('Failed to export certificate: ' . openssl_error_string());
        }

        return $certout;
    }

    /**
     * Verify the generated keys
     */
    private function verifyKeys(string $privateKeyPath, string $certificatePath): void
    {
        // Verify private key
        $privateKey = file_get_contents($privateKeyPath);
        $keyResource = openssl_pkey_get_private($privateKey);
        if (!$keyResource) {
            throw new \Exception('Generated private key is invalid');
        }

        // Verify certificate
        $certificate = file_get_contents($certificatePath);
        $certResource = openssl_x509_read($certificate);
        if (!$certResource) {
            throw new \Exception('Generated certificate is invalid');
        }

        // Verify that certificate matches private key
        if (!openssl_x509_check_private_key($certResource, $keyResource)) {
            throw new \Exception('Certificate does not match private key');
        }

        $this->info('✓ Keys verified successfully');
    }

    /**
     * Display certificate information
     */
    private function displayCertificateInfo(string $certificatePath): void
    {
        $certificate = file_get_contents($certificatePath);
        $certInfo = openssl_x509_parse($certificate);
        
        if ($certInfo) {
            $this->info('');
            $this->info('Certificate Information:');
            $this->info('Subject: ' . $certInfo['name']);
            $this->info('Valid From: ' . date('Y-m-d H:i:s', $certInfo['validFrom_time_t']));
            $this->info('Valid To: ' . date('Y-m-d H:i:s', $certInfo['validTo_time_t']));
            $this->info('Serial Number: ' . $certInfo['serialNumber']);
        }
    }
} 