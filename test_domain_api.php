<?php
/**
 * Test script for ResellerClub/Resell.biz Domain API
 *
 * Usage: Access via browser: http://localhost/test_domain_api.php
 */

// ============================================
// STEP 1: Update these with YOUR credentials
// ============================================
$RESELLER_ID = '1256356';  // Replace with your actual Reseller ID
$API_KEY = '6lY8CY2bnstSAqL04lr2y9oovt8CljT9';  // Replace with your actual API Key

// ============================================
// STEP 2: Choose environment
// ============================================
$USE_PRODUCTION = false;  // Set to TRUE for production, FALSE for test

if ($USE_PRODUCTION) {
    $CHECK_API = 'https://httpapi.com/api/domains/available.json?';
    $SUGGEST_API = 'https://httpapi.com/api/domains/v5/suggest-names.json?';
} else {
    $CHECK_API = 'https://test.httpapi.com/api/domains/available.json?';
    $SUGGEST_API = 'https://test.httpapi.com/api/domains/v5/suggest-names.json?';
}

// ============================================
// Test domain availability
// ============================================
$testDomain = 'example';
$testTld = 'com';

$checkUrl = $CHECK_API . 'auth-userid=' . $RESELLER_ID . '&api-key=' . $API_KEY . '&domain-name=' . $testDomain . '&tlds=' . $testTld;

echo "<h2>Domain API Test</h2>";
echo "<p><strong>Environment:</strong> " . ($USE_PRODUCTION ? 'PRODUCTION' : 'TEST') . "</p>";
echo "<p><strong>Testing Domain:</strong> {$testDomain}.{$testTld}</p>";
echo "<hr>";

echo "<h3>Request URL:</h3>";
echo "<pre>" . htmlspecialchars($checkUrl) . "</pre>";
echo "<hr>";

// Make cURL request
$ch = curl_init();
$headers = array(
    'Accept: application/json',
    'Content-Type: application/json',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
);

curl_setopt($ch, CURLOPT_URL, $checkUrl);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<h3>Response:</h3>";
echo "<p><strong>HTTP Code:</strong> " . $httpCode . "</p>";

if ($curlError) {
    echo "<p style='color: red;'><strong>cURL Error:</strong> " . htmlspecialchars($curlError) . "</p>";
}

if ($httpCode == 200) {
    echo "<p style='color: green;'><strong>✓ Success!</strong> API is working correctly.</p>";
    echo "<pre>" . htmlspecialchars(json_encode(json_decode($response), JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "<p style='color: red;'><strong>✗ Failed!</strong> HTTP " . $httpCode . "</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<hr>";
echo "<h3>Troubleshooting:</h3>";
echo "<ul>";
echo "<li>If you see '<strong>Sorry, you have been blocked</strong>': Your IP is not whitelisted in ResellerClub</li>";
echo "<li>If you see '<strong>HTTP 403</strong>': Check your credentials or IP whitelist</li>";
echo "<li>If you see '<strong>HTTP 401</strong>': Invalid API credentials</li>";
echo "<li>If you see '<strong>cURL error 60</strong>': SSL certificate issue</li>";
echo "</ul>";

echo "<h3>Your Server Info:</h3>";
echo "<ul>";
echo "<li><strong>Server IP:</strong> " . htmlspecialchars($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "</li>";
echo "<li><strong>Your Public IP:</strong> <a href='https://api.ipify.org' target='_blank'>Check here</a></li>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>cURL Enabled:</strong> " . (function_exists('curl_version') ? 'Yes' : 'No') . "</li>";
echo "</ul>";

if (function_exists('curl_version')) {
    $curlVersion = curl_version();
    echo "<p><strong>cURL Version:</strong> " . $curlVersion['version'] . "</p>";
    echo "<p><strong>SSL Version:</strong> " . $curlVersion['ssl_version'] . "</p>";
}
?>
