<?php
header('Content-Type: application/json; charset=utf-8');
require 'config.php';

// Customer live-order endpoint.
// Returns only orders belonging to the logged-in customer.
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid user_id is required.']);
    exit;
}

$stmt = $conn->prepare(
    'SELECT id, customer_name, phone, address, total,
            payment_method, payment_status, status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY id DESC'
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare order query.']);
    exit;
}

$stmt->bind_param('i', $userId);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load customer orders.']);
    exit;
}

$result = $stmt->get_result();
$orders = [];

while ($row = $result->fetch_assoc()) {
    $row['id'] = (int)$row['id'];
    $row['total'] = (float)$row['total'];
    $orders[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($orders, JSON_UNESCAPED_UNICODE);
?>
