<?php
// Simple CORS test script

header('Content-Type: application/json');

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

echo json_encode([
    'status' => 'success',
    'message' => 'CORS test successful',
    'timestamp' => date('c'),
    'origin' => $_SERVER['HTTP_ORIGIN'] ?? 'unknown'
]);