<?php
header('Content-Type: application/json');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST method required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

$stmt = $conn->prepare('SELECT id, name, email, password, phone, address FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    unset($user['password']);
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password.']);
}
?>
