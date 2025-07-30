<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';

header('Content-Type: application/json');
$db = new Database();
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

$stmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute([$email]);
$is_taken = $stmt->fetchColumn() > 0;

echo json_encode([
    'message' => $is_taken ? 'Email is already taken.' : 'Email is available.'
]);
?>