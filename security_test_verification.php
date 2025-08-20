<?php
/**
 * Security Test Verification Script
 * 
 * This script verifies the security fix for issue #1 (serialize/unserialize vulnerability)
 * and provides regression prevention checks.
 * 
 * TESTS PERFORMED:
 * 1. PHP syntax validation
 * 2. Static analysis for serialize/unserialize usage
 * 3. Session storage implementation verification
 * 4. Code structure verification
 * 5. Security improvements validation
 * 6. Regression prevention checks
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "===============================================\n";
echo "Security Test Verification for SRP Demo\n";
echo "Issue #1: serialize/unserialize vulnerability\n";
echo "===============================================\n\n";

$tests_passed = 0;
$tests_failed = 0;
$test_results = [];

function test_result($test_name, $passed, $message = '') {
    global $tests_passed, $tests_failed, $test_results;
    
    if ($passed) {
        $tests_passed++;
        $status = "✅ PASS";
    } else {
        $tests_failed++;
        $status = "❌ FAIL";
    }
    
    $result = "$status - $test_name";
    if ($message) {
        $result .= ": $message";
    }
    
    $test_results[] = $result;
    echo "$result\n";
}

// Test 1: PHP Syntax Validation
echo "1. PHP SYNTAX VALIDATION\n";
echo "-------------------------\n";

$files_to_check = ['challenge.php', 'login.php', 'register.php', 'index.html'];
foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $output = [];
            $return_code = 0;
            exec("php -l $file 2>&1", $output, $return_code);
            test_result("Syntax check: $file", $return_code === 0, implode(' ', $output));
        } else {
            test_result("File exists: $file", true, "Non-PHP file, skipping syntax check");
        }
    } else {
        test_result("File exists: $file", false, "File not found");
    }
}

echo "\n";

// Test 2: Security Vulnerability Check
echo "2. SERIALIZE/UNSERIALIZE VULNERABILITY CHECK\n";
echo "---------------------------------------------\n";

$php_files = ['challenge.php', 'login.php', 'register.php']; // Exclude test files
$serialize_usage = [];
$unserialize_usage = [];

foreach ($php_files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    
    // Check for serialize usage (excluding comments)
    if (preg_match('/^\s*[^\/\*#]*serialize\s*\(/m', $content)) {
        $serialize_usage[] = $file;
    }
    
    // Check for unserialize usage (excluding comments)
    if (preg_match('/^\s*[^\/\*#]*unserialize\s*\(/m', $content)) {
        $unserialize_usage[] = $file;
    }
}

test_result("No active serialize() calls found", empty($serialize_usage), 
    empty($serialize_usage) ? "All serialize() calls have been removed or commented out" : "Found in: " . implode(', ', $serialize_usage));

test_result("No active unserialize() calls found", empty($unserialize_usage), 
    empty($unserialize_usage) ? "All unserialize() calls have been removed or commented out" : "Found in: " . implode(', ', $unserialize_usage));

echo "\n";

// Test 3: Session Storage Implementation
echo "3. SESSION STORAGE IMPLEMENTATION CHECK\n";
echo "----------------------------------------\n";

$session_usage = [];
$main_php_files = ['challenge.php', 'login.php', 'register.php']; // Exclude test files
foreach ($main_php_files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    if (strpos($content, '$_SESSION') !== false) {
        $session_usage[] = $file;
    }
}

test_result("Session storage implemented", !empty($session_usage), "Found in: " . implode(', ', $session_usage));

// Check for session_start()
$session_start_usage = [];
foreach ($main_php_files as $file) {
    if (!file_exists($file)) continue;
    
    $content = file_get_contents($file);
    if (preg_match('/session_start\s*\(\s*\)/', $content)) {
        $session_start_usage[] = $file;
    }
}

test_result("session_start() called", !empty($session_start_usage), "Found in: " . implode(', ', $session_start_usage));

echo "\n";

// Test 4: Check specific security improvements in challenge.php
echo "4. CHALLENGE.PHP SECURITY IMPROVEMENTS\n";
echo "---------------------------------------\n";

if (file_exists('challenge.php')) {
    $challenge_content = file_get_contents('challenge.php');
    
    test_result("Session-based SRP storage", 
        strpos($challenge_content, '$_SESSION[\'srp_auth\']') !== false,
        "Uses session storage instead of database serialization");
    
    test_result("Timestamp for session timeout", 
        strpos($challenge_content, 'timestamp') !== false,
        "Includes timestamp for session management");
    
    test_result("No database serialization", 
        strpos($challenge_content, '$authentication->srp = serialize') === false,
        "Removed vulnerable database serialization");
}

echo "\n";

// Test 5: Check specific security improvements in login.php
echo "5. LOGIN.PHP SECURITY IMPROVEMENTS\n";
echo "-----------------------------------\n";

if (file_exists('login.php')) {
    $login_content = file_get_contents('login.php');
    
    test_result("Session-based SRP retrieval", 
        strpos($login_content, '$_SESSION[\'srp_auth\']') !== false,
        "Uses session storage instead of database unserialization");
    
    test_result("Session cleanup after auth", 
        strpos($login_content, 'unset($_SESSION[\'srp_auth\'])') !== false,
        "Cleans up session after authentication");
    
    test_result("No database unserialization", 
        strpos($login_content, 'unserialize($authentication->srp)') === false,
        "Removed vulnerable database unserialization");
}

echo "\n";

// Test 6: Regression Prevention Check
echo "6. REGRESSION PREVENTION\n";
echo "-------------------------\n";

test_result("Security fix implemented", 
    empty($serialize_usage) && empty($unserialize_usage),
    "serialize/unserialize calls have been removed from authentication flow");

echo "\n";

// Test Summary
echo "===============================================\n";
echo "TEST SUMMARY\n";
echo "===============================================\n";
echo "Tests Passed: $tests_passed\n";
echo "Tests Failed: $tests_failed\n";
echo "Total Tests: " . ($tests_passed + $tests_failed) . "\n\n";

if ($tests_failed > 0) {
    echo "❌ SOME TESTS FAILED\n";
    echo "Check the detailed results above for specific issues.\n\n";
} else {
    echo "✅ ALL TESTS PASSED\n";
    echo "Security vulnerability has been resolved.\n\n";
}

?>