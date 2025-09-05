<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate required fields
if (empty($name) || empty($phone) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate phone number (basic validation)
$cleanPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
if (!preg_match('/^[\+]?[1-9][\d]{9,15}$/', $cleanPhone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
    exit;
}

// Sanitize data
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

// Prepare CSV data
$timestamp = date('Y-m-d H:i:s');
$csvData = [
    $timestamp,
    $name,
    $phone,
    $email
];

// CSV file path
$csvFile = 'form_submissions.csv';

// Check if CSV file exists, if not create header
if (!file_exists($csvFile)) {
    $header = ['Timestamp', 'Name', 'Phone', 'Email'];
    $fp = fopen($csvFile, 'w');
    fputcsv($fp, $header);
    fclose($fp);
}

// Append data to CSV file
$fp = fopen($csvFile, 'a');
if ($fp === false) {
    echo json_encode(['success' => false, 'message' => 'Unable to save data']);
    exit;
}

// Lock file for writing
if (flock($fp, LOCK_EX)) {
    fputcsv($fp, $csvData);
    flock($fp, LOCK_UN);
    fclose($fp);
    
    // Send success response
    echo json_encode(['success' => true, 'message' => 'Data saved successfully']);
} else {
    fclose($fp);
    echo json_encode(['success' => false, 'message' => 'Unable to save data']);
}
?>
