<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Thinbus\ThinbusSrp;
use Thinbus\ThinbusSrpClient;
use Math_BigInteger;

/**
 * Modern PHPUnit 10 compatible test suite for Thinbus SRP
 * 
 * This test suite verifies that the Thinbus SRP library works correctly
 * across different PHP versions (8.0, 8.1, 8.2, 8.3, 8.4).
 */
class ThinbusSrpTest extends TestCase
{
    private ThinbusSrp $srp;
    private ThinbusSrpClient $srpClient;

    protected function setUp(): void
    {
        parent::setUp();
        
        $N_base10str = "19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107";
        $g_base10str = "2";
        $k_base16str = "1a3d1769e1d6337af78796f1802f9b14fbc20278fb6e15e4361beb38a8e7cd3a";
        
        $this->srp = new ThinbusSrp($N_base10str, $g_base10str, $k_base16str, "sha256");
        $this->srpClient = new ThinbusSrpClient($N_base10str, $g_base10str, $k_base16str, "sha256");
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testSrpInstantiation(): void
    {
        $this->assertInstanceOf(ThinbusSrp::class, $this->srp);
        $this->assertInstanceOf(ThinbusSrpClient::class, $this->srpClient);
    }

    public function testBasicMathOperations(): void
    {
        // Test basic BigInteger operations work correctly
        $N = new Math_BigInteger("19502997308733555461855666625958719160994364695757801883048536560804281608617712589335141535572898798222757219122180598766018632900275026915053180353164617230434226106273953899391119864257302295174320915476500215995601482640160424279800690785793808960633891416021244925484141974964367107");
        $g = new Math_BigInteger("2");
        
        $this->assertTrue($N->compare(new Math_BigInteger("0")) > 0);
        $this->assertTrue($g->compare(new Math_BigInteger("0")) > 0);
        
        // Test modular exponentiation works
        $result = $g->modPow(new Math_BigInteger("10"), $N);
        $this->assertInstanceOf(Math_BigInteger::class, $result);
        $this->assertTrue($result->compare(new Math_BigInteger("0")) > 0);
    }

    public function testVerifierGeneration(): void
    {
        $salt = "046ffedc02d01f7b82a1f51312f3e9476023df82b96de300059b50dba286fcfe";
        $identity = "test@example.com";
        $password = "testpassword123";
        
        $verifier = $this->srpClient->generateVerifier($salt, $identity, $password);
        
        $this->assertIsString($verifier);
        $this->assertNotEmpty($verifier);
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $verifier);
    }

    public function testVerifierRejectsBlanks(): void
    {
        $this->expectException(\Exception::class);
        $this->srpClient->generateVerifier('', 'identity', 'password');
    }

    public function testVerifierRejectsBlankIdentity(): void
    {
        $this->expectException(\Exception::class);
        $this->srpClient->generateVerifier('salt', '', 'password');
    }

    public function testVerifierRejectsBlankPassword(): void
    {
        $this->expectException(\Exception::class);
        $this->srpClient->generateVerifier('salt', 'identity', '');
    }

    public function testRandomNumberGeneration(): void
    {
        $N = new Math_BigInteger("255");
        
        // Generate several random numbers and verify they're in range
        for ($i = 0; $i < 10; $i++) {
            $random = $this->srp->createRandomBigIntegerInRange($N);
            $this->assertInstanceOf(Math_BigInteger::class, $random);
            $this->assertTrue($random->compare(new Math_BigInteger("0")) >= 0);
            $this->assertTrue($random->compare($N) < 0);
        }
    }

    public function testPHPVersionCompatibility(): void
    {
        // Test that we're running on PHP 8.0+
        $this->assertGreaterThanOrEqual(80000, PHP_VERSION_ID, 'This test suite requires PHP 8.0+');
        
        // Test that basic PHP 8 features work
        $this->assertTrue(function_exists('str_contains'));
        $this->assertTrue(class_exists('Math_BigInteger'));
        $this->assertTrue(class_exists('Thinbus\ThinbusSrp'));
    }

    public function testSrpClientStep1(): void
    {
        $identity = "test@example.com";
        $password = "testpassword123";
        
        $A = $this->srpClient->step1($identity, $password);
        
        $this->assertIsString($A);
        $this->assertNotEmpty($A);
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $A);
    }

    /**
     * Test that demonstrates the library works across different PHP versions
     */
    public function testCrossVersionCompatibility(): void
    {
        $phpVersion = PHP_VERSION;
        $majorVersion = PHP_MAJOR_VERSION;
        $minorVersion = PHP_MINOR_VERSION;
        
        $this->assertGreaterThanOrEqual(8, $majorVersion, "Should be running on PHP 8.x");
        
        // Test that the library can handle basic operations on all supported versions
        $salt = bin2hex(random_bytes(32));
        $identity = "user@example.com";
        $password = "securepassword123";
        
        $verifier1 = $this->srpClient->generateVerifier($salt, $identity, $password);
        $verifier2 = $this->srpClient->generateVerifier($salt, $identity, $password);
        
        // Same inputs should produce same verifier
        $this->assertEquals($verifier1, $verifier2, "Verifier generation should be deterministic");
        
        // Different salt should produce different verifier
        $differentSalt = bin2hex(random_bytes(32));
        $verifier3 = $this->srpClient->generateVerifier($differentSalt, $identity, $password);
        $this->assertNotEquals($verifier1, $verifier3, "Different salt should produce different verifier");
        
        $this->addToAssertionCount(1); // Mark test as having made assertions
        echo "\n✅ Successfully tested on PHP {$phpVersion}\n";
    }
}