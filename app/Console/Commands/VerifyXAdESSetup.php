<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SignatureController;
use Illuminate\Support\Facades\Log;

class VerifyXAdESSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xades:verify-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify XAdES setup and run comprehensive tests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('XAdES Setup Verification');
        $this->info('========================');

        $overallStatus = true;
        $issueCount = 0;

        // Check configuration
        $this->info('1. Checking Configuration...');
        $configResult = $this->checkConfiguration();
        $this->displayResult($configResult);
        if (!$configResult['status']) {
            $overallStatus = false;
            $issueCount += count($configResult['issues']);
        }

        // Check OpenSSL extension
        $this->info('2. Checking OpenSSL Extension...');
        $opensslResult = $this->checkOpenSSL();
        $this->displayResult($opensslResult);
        if (!$opensslResult['status']) {
            $overallStatus = false;
            $issueCount++;
        }

        // Check keys and certificates
        $this->info('3. Checking Keys and Certificates...');
        $keysResult = $this->checkKeys();
        $this->displayResult($keysResult);
        if (!$keysResult['status']) {
            $overallStatus = false;
            $issueCount += count($keysResult['issues']);
        }

        // Check file permissions
        $this->info('4. Checking File Permissions...');
        $permissionsResult = $this->checkPermissions();
        $this->displayResult($permissionsResult);
        if (!$permissionsResult['status']) {
            $overallStatus = false;
            $issueCount += count($permissionsResult['issues']);
        }

        // Test XML sanitization system
        $this->info('5. Testing XML Sanitization System...');
        $sanitizationResult = $this->testXmlSanitization();
        $this->displayResult($sanitizationResult);
        if (!$sanitizationResult['status']) {
            $overallStatus = false;
            $issueCount++;
        }

        // Test XAdES signature generation and verification
        $this->info('6. Testing XAdES Signature Functions...');
        $signatureResult = $this->testXAdESSignature();
        $this->displayResult($signatureResult);
        if (!$signatureResult['status']) {
            $overallStatus = false;
            $issueCount++;
        }

        // Overall status
        $this->info('');
        $this->info('Overall Status:');
        $this->info('===============');
        
        if ($overallStatus) {
            $this->info('✅ XAdES system is properly configured and ready for use!');
            Log::info('XAdES setup verification completed successfully');
            return 0;
        } else {
            $this->error("❌ XAdES system has {$issueCount} issue(s) that need to be resolved.");
            $this->warn('Please address the issues above before using the XAdES system.');
            Log::warning("XAdES setup verification failed with {$issueCount} issues");
            return 1;
        }
    }

    /**
     * Check XAdES configuration
     */
    private function checkConfiguration(): array
    {
        $issues = [];
        $config = config('app.xades');

        if (!$config) {
            return [
                'status' => false,
                'message' => 'XAdES configuration not found',
                'issues' => ['XAdES configuration missing from config/app.php']
            ];
        }

        if (!$config['enabled']) {
            $issues[] = 'XAdES is disabled (set XADES_ENABLED=true in .env)';
        }

        if (empty($config['private_key_path'])) {
            $issues[] = 'Private key path not configured';
        }

        if (empty($config['certificate_path'])) {
            $issues[] = 'Certificate path not configured';
        }

        if ($config['key_size'] < 2048) {
            $issues[] = 'Key size should be at least 2048 bits for security';
        }

        return [
            'status' => empty($issues),
            'message' => empty($issues) ? 'Configuration is valid' : 'Configuration issues found',
            'issues' => $issues
        ];
    }

    /**
     * Check OpenSSL extension
     */
    private function checkOpenSSL(): array
    {
        if (!extension_loaded('openssl')) {
            return [
                'status' => false,
                'message' => 'OpenSSL extension is not loaded',
                'issues' => ['Install and enable the OpenSSL PHP extension']
            ];
        }

        // Test basic OpenSSL functionality
        $testData = 'test data for openssl verification';
        $testKey = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 1024, // Small key for quick testing
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if (!$testKey) {
            return [
                'status' => false,
                'message' => 'OpenSSL key generation failed',
                'issues' => ['OpenSSL functionality test failed']
            ];
        }

        return [
            'status' => true,
            'message' => 'OpenSSL extension is working correctly',
            'issues' => []
        ];
    }

    /**
     * Check keys and certificates
     */
    private function checkKeys(): array
    {
        $config = config('app.xades');
        $issues = [];

        $privateKeyPath = $config['private_key_path'];
        $certificatePath = $config['certificate_path'];

        // Check if files exist
        if (!file_exists($privateKeyPath)) {
            $issues[] = "Private key not found at: {$privateKeyPath}";
        } else {
            // Validate private key
            $privateKey = file_get_contents($privateKeyPath);
            $keyResource = openssl_pkey_get_private($privateKey);
            if (!$keyResource) {
                $issues[] = 'Private key is invalid or corrupted';
            }
        }

        if (!file_exists($certificatePath)) {
            $issues[] = "Certificate not found at: {$certificatePath}";
        } else {
            // Validate certificate
            $certificate = file_get_contents($certificatePath);
            $certResource = openssl_x509_read($certificate);
            if (!$certResource) {
                $issues[] = 'Certificate is invalid or corrupted';
            } else {
                // Check certificate expiration
                $certInfo = openssl_x509_parse($certificate);
                if ($certInfo && $certInfo['validTo_time_t'] < time()) {
                    $issues[] = 'Certificate has expired';
                } elseif ($certInfo && $certInfo['validTo_time_t'] < (time() + 30 * 24 * 3600)) {
                    $this->warn('⚠️  Certificate will expire within 30 days');
                }
            }
        }

        // Check key-certificate pair if both exist
        if (file_exists($privateKeyPath) && file_exists($certificatePath) && empty($issues)) {
            $privateKey = file_get_contents($privateKeyPath);
            $certificate = file_get_contents($certificatePath);
            $keyResource = openssl_pkey_get_private($privateKey);
            $certResource = openssl_x509_read($certificate);
            
            if ($keyResource && $certResource) {
                if (!openssl_x509_check_private_key($certResource, $keyResource)) {
                    $issues[] = 'Private key does not match certificate';
                }
            }
        }

        if (!empty($issues)) {
            $issues[] = 'Run "php artisan xades:generate-keys" to generate new keys';
        }

        return [
            'status' => empty($issues),
            'message' => empty($issues) ? 'Keys and certificates are valid' : 'Key/certificate issues found',
            'issues' => $issues
        ];
    }

    /**
     * Check file permissions
     */
    private function checkPermissions(): array
    {
        $config = config('app.xades');
        $issues = [];

        $privateKeyPath = $config['private_key_path'];
        $certificatePath = $config['certificate_path'];
        $keysDir = dirname($privateKeyPath);

        // Check directory permissions
        if (is_dir($keysDir)) {
            $dirPerms = fileperms($keysDir) & 0777;
            if ($dirPerms !== 0700) {
                $issues[] = "Keys directory has insecure permissions: " . decoct($dirPerms) . " (should be 700)";
            }
        }

        // Check private key permissions
        if (file_exists($privateKeyPath)) {
            $keyPerms = fileperms($privateKeyPath) & 0777;
            if ($keyPerms !== 0600) {
                $issues[] = "Private key has insecure permissions: " . decoct($keyPerms) . " (should be 600)";
            }
        }

        return [
            'status' => empty($issues),
            'message' => empty($issues) ? 'File permissions are secure' : 'Permission issues found',
            'issues' => $issues
        ];
    }

    /**
     * Test XML sanitization system
     */
    private function testXmlSanitization(): array
    {
        try {
            $controller = new SignatureController();
            $result = $controller->validateXmlSanitizationSystem();
            
            return [
                'status' => $result['overall_status'] === 'excellent' || $result['overall_status'] === 'good',
                'message' => "XML sanitization system: {$result['overall_status']} (score: {$result['security_score']}%)",
                'issues' => $result['recommendations'] ?? []
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'XML sanitization test failed',
                'issues' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Test XAdES signature functionality
     */
    private function testXAdESSignature(): array
    {
        try {
            $config = config('app.xades');
            
            if (!$config['enabled']) {
                return [
                    'status' => false,
                    'message' => 'XAdES is disabled',
                    'issues' => ['Enable XAdES in configuration']
                ];
            }

            if (!file_exists($config['private_key_path']) || !file_exists($config['certificate_path'])) {
                return [
                    'status' => false,
                    'message' => 'XAdES keys not found',
                    'issues' => ['Generate XAdES keys first']
                ];
            }

            // Test XML generation and signing
            $testData = [
                'UUID' => 'test-uuid-' . time(),
                'Tipe_Laporan' => 'Test',
                'Judul_Laporan' => 'Test Document',
                'NIM' => '1234567890',
                'Nama_Mahasiswa' => 'Test Student'
            ];

            $controller = new SignatureController();
            $xmlContent = $controller::generateXML($testData);
            
            if (!$xmlContent) {
                throw new \Exception('Failed to generate test XML');
            }

            // Test signature addition (using reflection to access private method)
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('addXAdESSignature');
            $method->setAccessible(true);
            
            $signedXml = $method->invoke($controller, $xmlContent);
            
            if (strpos($signedXml, '<Signature') === false) {
                throw new \Exception('XAdES signature was not added to XML');
            }

            // Test signature verification
            $verifyMethod = $reflection->getMethod('verifyXAdESSignature');
            $verifyMethod->setAccessible(true);
            
            $verificationResult = $verifyMethod->invoke($controller, $signedXml);
            
            if (!$verificationResult['valid']) {
                throw new \Exception('XAdES signature verification failed: ' . $verificationResult['message']);
            }

            return [
                'status' => true,
                'message' => 'XAdES signature generation and verification working correctly',
                'issues' => []
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'XAdES signature test failed',
                'issues' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Display test result
     */
    private function displayResult(array $result): void
    {
        if ($result['status']) {
            $this->info('   ✅ ' . $result['message']);
        } else {
            $this->error('   ❌ ' . $result['message']);
            if (!empty($result['issues'])) {
                foreach ($result['issues'] as $issue) {
                    $this->warn('      • ' . $issue);
                }
            }
        }
        $this->info('');
    }
} 