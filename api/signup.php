<?php
header('Content-Type: application/json');
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST method required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$password = $input['password'] ?? '';
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Name, email, and password are required.']);
    exit;
}

// Check existing user
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    http_response_code(400);
    echo json_encode(['error' => 'Email address is already registered.']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $name, $email, $hashedPassword, $phone, $address);

if ($stmt->execute()) {
    $userId = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to register user.']);
}
?>
