<?php

/**
 * Email Availability Check REST API
 * 
 * This API endpoint checks if an email address is already registered in the system.
 * It accepts POST requests with JSON payload containing the email to check.
 * 
 * @author Mohamed Ijas
 * 
 */

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include required files
require_once '../../includes/config.php';
require_once '../../classes/Database.php';

/**
 * Send JSON response and exit
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
function sendResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check if email exists in database
 * @param PDO $pdo Database connection
 * @param string $email Email to check
 * @return bool True if email exists
 */
function emailExists($pdo, $email)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0;
}

// Main API logic
try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse([
            'status' => 'error',
            'message' => 'Only POST requests are allowed'
        ], 405);
    }

    // Get and decode JSON input
    $jsonInput = file_get_contents('php://input');
    $input = json_decode($jsonInput, true);

    // Check for JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendResponse([
            'status' => 'error',
            'message' => 'Invalid JSON format'
        ], 400);
    }

    // Validate input data
    if (!isset($input['email'])) {
        sendResponse([
            'status' => 'error',
            'message' => 'Email parameter is required'
        ], 400);
    }

    $email = trim($input['email']);

    // Check if email is empty
    if (empty($email)) {
        sendResponse([
            'status' => 'error',
            'message' => 'Email cannot be empty'
        ], 400);
    }

    // Validate email format
    if (!isValidEmail($email)) {
        sendResponse([
            'status' => 'error',
            'message' => 'Invalid email format'
        ], 400);
    }

    // Initialize database connection
    $db = new Database();
    $pdo = $db->getConnection();

    // Check if email exists in database
    $exists = emailExists($pdo, $email);

    // Send success response
    sendResponse([
        'status' => 'success',
        'exists' => $exists,
        'message' => $exists ? 'Email already exists' : 'Email is available',
        'email' => $email
    ], 200);
} catch (PDOException $e) {
    // Database error
    error_log("Database error in check_email.php: " . $e->getMessage());
    sendResponse([
        'status' => 'error',
        'message' => 'Database connection error'
    ], 500);
} catch (Exception $e) {
    // General error
    error_log("Error in check_email.php: " . $e->getMessage());
    sendResponse([
        'status' => 'error',
        'message' => 'Internal server error'
    ], 500);
}
