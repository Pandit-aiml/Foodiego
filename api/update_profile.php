<?php
header('Content-Type: application/json');
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) { echo json_encode(['error'=>'Invalid JSON data']); exit; }

$id = intval($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$email = strtolower(trim($data['email'] ?? ''));
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');
$password = trim($data['password'] ?? '');

if ($id <= 0 || empty($name) || empty($email)) {
    echo json_encode(['error'=>'User ID, Name, and Email are required']); exit;
}

try {
    // Check whether the email is already used by another user.
    $checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $checkStmt->bind_param('si', $email, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo json_encode(['error'=>'Email address is already in use by another account.']); exit;
    }

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?');
        $stmt->bind_param('sssssi', $name, $email, $phone, $address, $hashed, $id);
    } else {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $name, $email, $phone, $address, $id);
    }
    $stmt->execute();

    // Return the updated user.
    $stmtUser = $conn->prepare('SELECT id, name, email, phone, address, role FROM users WHERE id = ?');
    $stmtUser->bind_param('i', $id);
    $stmtUser->execute();
    $user = $stmtUser->get_result()->fetch_assoc();

    echo json_encode(['success'=>true, 'user'=>$user]);
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Database error: '.$e->getMessage()]);
}
?>
