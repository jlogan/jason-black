<?php
// Simple test script to verify PHP is working on your live server
echo "<h2>Server Test Results</h2>";

// Test 1: PHP Version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Test 2: File Writing Permissions
$testFile = 'test_write.txt';
if (file_put_contents($testFile, 'Test content') !== false) {
    echo "<p><strong>File Writing:</strong> ✅ Success - Can write files</p>";
    unlink($testFile); // Clean up test file
} else {
    echo "<p><strong>File Writing:</strong> ❌ Failed - Cannot write files</p>";
}

// Test 3: Directory Permissions
if (is_writable('.')) {
    echo "<p><strong>Directory Permissions:</strong> ✅ Success - Directory is writable</p>";
} else {
    echo "<p><strong>Directory Permissions:</strong> ❌ Failed - Directory is not writable</p>";
}

// Test 4: Required PHP Functions
$requiredFunctions = ['json_encode', 'file_put_contents', 'fopen', 'fputcsv'];
$missingFunctions = [];
foreach ($requiredFunctions as $func) {
    if (!function_exists($func)) {
        $missingFunctions[] = $func;
    }
}

if (empty($missingFunctions)) {
    echo "<p><strong>Required Functions:</strong> ✅ All required functions available</p>";
} else {
    echo "<p><strong>Required Functions:</strong> ❌ Missing: " . implode(', ', $missingFunctions) . "</p>";
}

echo "<hr>";
echo "<p><em>If all tests pass, your form should work correctly!</em></p>";
echo "<p><a href='index.html'>← Back to Main Site</a></p>";
?>
