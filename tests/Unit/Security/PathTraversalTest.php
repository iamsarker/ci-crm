<?php
/**
 * Path Traversal Security Tests
 *
 * Tests for path traversal vulnerability prevention
 */

use PHPUnit\Framework\TestCase;

class PathTraversalTest extends TestCase
{
    /**
     * Test that basename strips directory traversal attempts
     */
    public function testBasenameStripsDirectoryTraversal()
    {
        $maliciousPath = '../../etc/passwd';
        $safePath = basename($maliciousPath);

        $this->assertEquals('passwd', $safePath);
    }

    /**
     * Test that basename handles Windows path traversal
     */
    public function testBasenameStripsWindowsTraversal()
    {
        $maliciousPath = '..\\..\\windows\\system32\\config\\sam';
        $safePath = basename($maliciousPath);

        $this->assertEquals('sam', $safePath);
    }

    /**
     * Test that basename handles mixed path separators
     */
    public function testBasenameStripsMixedTraversal()
    {
        $maliciousPath = '../..\\..//etc/passwd';
        $safePath = basename($maliciousPath);

        $this->assertEquals('passwd', $safePath);
    }

    /**
     * Test that basename preserves valid filename
     */
    public function testBasenamePreservesValidFilename()
    {
        $validPath = 'company_logo.png';
        $safePath = basename($validPath);

        $this->assertEquals('company_logo.png', $safePath);
    }

    /**
     * Test that basename handles URL encoded traversal
     */
    public function testBasenameHandlesUrlEncodedTraversal()
    {
        $maliciousPath = urldecode('%2e%2e%2f%2e%2e%2fetc%2fpasswd');
        $safePath = basename($maliciousPath);

        $this->assertEquals('passwd', $safePath);
    }

    /**
     * Test that basename handles null bytes
     */
    public function testBasenameHandlesNullBytes()
    {
        $maliciousPath = "image.png\x00.php";
        $safePath = basename($maliciousPath);

        // PHP 7+ handles null bytes differently
        $this->assertStringNotContainsString("\x00", $safePath);
    }

    /**
     * Test safe file path construction
     */
    public function testSafeFilePathConstruction()
    {
        $uploadDir = '/var/www/uploads';
        $subDir = 'mics';
        $filename = '../../etc/passwd';

        $safePath = $uploadDir . '/' . $subDir . '/' . basename($filename);

        $this->assertEquals('/var/www/uploads/mics/passwd', $safePath);
        $this->assertStringNotContainsString('..', $safePath);
    }

    /**
     * Test empty filename handling
     */
    public function testEmptyFilenameHandling()
    {
        $filename = '';
        $safeFilename = basename($filename);

        $this->assertEquals('', $safeFilename);
    }

    /**
     * Test filename with special characters
     */
    public function testFilenameWithSpecialCharacters()
    {
        $filename = 'logo (1).png';
        $safeFilename = basename($filename);

        $this->assertEquals('logo (1).png', $safeFilename);
    }

    /**
     * Test that realpath resolves traversal
     */
    public function testRealpathResolvesTraversal()
    {
        // Create a temp directory structure for testing
        $baseDir = sys_get_temp_dir() . '/test_traversal_' . uniqid();
        $subDir = $baseDir . '/uploads';
        @mkdir($subDir, 0777, true);

        $attemptedPath = $subDir . '/../../etc/passwd';
        $resolvedPath = realpath($attemptedPath);

        // realpath returns false for non-existent paths
        $this->assertFalse($resolvedPath);

        // Cleanup
        @rmdir($subDir);
        @rmdir($baseDir);
    }

    /**
     * Test file extension validation
     */
    public function testFileExtensionValidation()
    {
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $filename = 'logo.php';

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $isValid = in_array($extension, $allowedExtensions);

        $this->assertFalse($isValid);
    }

    /**
     * Test that valid image extensions pass
     */
    public function testValidImageExtension()
    {
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $filename = 'logo.PNG';

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $isValid = in_array($extension, $allowedExtensions);

        $this->assertTrue($isValid);
    }

    /**
     * Test double extension handling
     */
    public function testDoubleExtensionHandling()
    {
        $filename = 'malicious.php.jpg';

        // Check if filename contains dangerous patterns
        $dangerousPatterns = array('.php', '.exe', '.sh', '.bat', '.js');
        $containsDangerous = false;

        foreach ($dangerousPatterns as $pattern) {
            if (stripos($filename, $pattern) !== false) {
                $containsDangerous = true;
                break;
            }
        }

        $this->assertTrue($containsDangerous);
    }
}
