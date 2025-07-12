<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow CORS for testing; restrict in production
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../classes/Database.php';

// Initialize response array
$response = [];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (!isset($input['email']) || empty($input['email'])) {
        http_response_code(400); // Bad Request
        $response = [
            'status' => 'error',
            'message' => 'Email is required'
        ];
    } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        $response = [
            'status' => 'error',
            'message' => 'Invalid email format'
        ];
    } else {
        try {
            // Initialize database connection
            $db = new Database();
            $pdo = $db->getConnection();
            
            // Check if email exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$input['email']]);
            $count = $stmt->fetchColumn();
            
            http_response_code(200); // OK
            $response = [
                'status' => 'success',
                'exists' => $count > 0,
                'message' => $count > 0 ? 'Email already exists' : 'Email is available'
            ];
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            $response = [
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }
} else {
    http_response_code(405); // Method Not Allowed
    $response = [
        'status' => 'error',
        'message' => 'Only POST requests are allowed'
    ];
}

// Output JSON response
echo json_encode($response);
?>