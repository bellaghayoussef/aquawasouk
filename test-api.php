#!/usr/bin/env php
<?php

/**
 * Aquado API Test Script
 * 
 * This script tests basic API endpoints to ensure everything is working.
 * Run: php test-api.php
 */

$baseUrl = 'http://localhost/api/v1';
$results = [];

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║           AQUADO API - Quick Test Script                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Test 1: Check API is accessible
echo "Test 1: Checking API accessibility...\n";
$ch = curl_init("$baseUrl/products");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "✓ API is accessible (HTTP $httpCode)\n\n";
    $results['api_accessible'] = true;
} else {
    echo "✗ API not accessible (HTTP $httpCode)\n\n";
    $results['api_accessible'] = false;
}

// Test 2: Register a test user
echo "Test 2: Registering test user...\n";
$testEmail = 'test_' . time() . '@example.com';
$registerData = [
    'name' => 'Test User',
    'email' => $testEmail,
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'role' => 'customer'
];

$ch = curl_init("$baseUrl/register");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($registerData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$accessToken = null;
if ($httpCode === 201) {
    $data = json_decode($response, true);
    if (isset($data['data']['access_token'])) {
        $accessToken = $data['data']['access_token'];
        echo "✓ User registered successfully\n";
        echo "  Email: $testEmail\n";
        echo "  Token: " . substr($accessToken, 0, 20) . "...\n\n";
        $results['registration'] = true;
    } else {
        echo "✗ Registration failed - no token received\n\n";
        $results['registration'] = false;
    }
} else {
    echo "✗ Registration failed (HTTP $httpCode)\n";
    echo "  Response: " . substr($response, 0, 200) . "...\n\n";
    $results['registration'] = false;
}

// Test 3: Get user profile with token
if ($accessToken) {
    echo "Test 3: Getting user profile (authenticated request)...\n";
    $ch = curl_init("$baseUrl/user");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "✓ User profile retrieved successfully\n";
        echo "  Name: " . ($data['data']['name'] ?? 'N/A') . "\n";
        echo "  Email: " . ($data['data']['email'] ?? 'N/A') . "\n\n";
        $results['authenticated_request'] = true;
    } else {
        echo "✗ Failed to get user profile (HTTP $httpCode)\n\n";
        $results['authenticated_request'] = false;
    }

    // Test 4: Get cart
    echo "Test 4: Getting user cart...\n";
    $ch = curl_init("$baseUrl/cart");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Accept: application/json'
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        echo "✓ Cart retrieved successfully\n\n";
        $results['cart'] = true;
    } else {
        echo "✗ Failed to get cart (HTTP $httpCode)\n\n";
        $results['cart'] = false;
    }
}

// Test 5: Get products (public endpoint)
echo "Test 5: Getting products list...\n";
$ch = curl_init("$baseUrl/products?per_page=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $productCount = count($data['data']['data'] ?? []);
    echo "✓ Products retrieved successfully\n";
    echo "  Found $productCount products\n\n";
    $results['products'] = true;
} else {
    echo "✗ Failed to get products (HTTP $httpCode)\n\n";
    $results['products'] = false;
}

// Test 6: Get categories (public endpoint)
echo "Test 6: Getting categories...\n";
$ch = curl_init("$baseUrl/categories");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    $categoryCount = count($data['data'] ?? []);
    echo "✓ Categories retrieved successfully\n";
    echo "  Found $categoryCount categories\n\n";
    $results['categories'] = true;
} else {
    echo "✗ Failed to get categories (HTTP $httpCode)\n\n";
    $results['categories'] = false;
}

// Test 7: Get vendors (public endpoint)
echo "Test 7: Getting vendors...\n";
$ch = curl_init("$baseUrl/vendors");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "✓ Vendors retrieved successfully\n\n";
    $results['vendors'] = true;
} else {
    echo "✗ Failed to get vendors (HTTP $httpCode)\n\n";
    $results['vendors'] = false;
}

// Summary
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║                     TEST SUMMARY                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;

foreach ($results as $test => $result) {
    $status = $result ? '✓ PASS' : '✗ FAIL';
    $testName = str_pad(ucwords(str_replace('_', ' ', $test)), 30);
    echo "$testName : $status\n";
    
    if ($result) {
        $passed++;
    } else {
        $failed++;
    }
}

$total = $passed + $failed;
echo "\n";
echo "Total Tests: $total\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED! Your API is working correctly.\n\n";
    echo "Next steps:\n";
    echo "  1. Import Postman collection: Aquado_API_Postman_Collection.json\n";
    echo "  2. Read API_DOCUMENTATION.md for complete API reference\n";
    echo "  3. Check API_SETUP_GUIDE.md for integration examples\n";
    exit(0);
} else {
    echo "⚠️  Some tests failed. Please check the errors above.\n\n";
    echo "Troubleshooting:\n";
    echo "  1. Ensure Laravel server is running\n";
    echo "  2. Check database connection in .env\n";
    echo "  3. Run: php artisan config:clear\n";
    echo "  4. Run: php artisan route:clear\n";
    exit(1);
}
