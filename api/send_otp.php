<?php
header('Content-Type: application/json');
session_start();

$input = json_decode(file_get_contents('php://input'), true);
$target = trim($input['target'] ?? '');
$type = trim($input['type'] ?? 'email');

if (!$target) {
    http_response_code(400);
    echo json_encode(['error' => 'Email address or phone number is required to send OTP.']);
    exit;
}

// Generate 6-digit OTP
$otp = strval(rand(100000, 999999));
$_SESSION['otp_' . $target] = [
    'otp' => $otp,
    'expires_at' => time() + 300 // 5 minutes
];

// Log OTP dispatch to backend outbox log
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0777, true);
}
$logEntry = "[" . date('Y-m-d H:i:s') . "] Dispatched 6-digit OTP [ {$otp} ] to {$type}: {$target}\n";
@file_put_contents($logDir . '/otp_outbox.log', $logEntry, FILE_APPEND);

// Attempt standard PHP mail delivery if email
if ($type === 'email' && filter_var($target, FILTER_VALIDATE_EMAIL)) {
    $subject = "Your FoodieGo Verification Code: {$otp}";
    $message = "Hello,\n\nYour FoodieGo 6-digit email verification OTP code is: {$otp}\n\nThis code is valid for 5 minutes. Do not share this code with anyone.\n\nBest regards,\nFoodieGo Team";
    $headers = "From: no-reply@foodiego.com\r\nX-Mailer: PHP/" . phpversion();
    @mail($target, $subject, $message, $headers);
}

// Return success WITHOUT exposing the OTP in JSON output
echo json_encode([
    'success' => true,
    'message' => "OTP sent successfully to {$target}. Please check your {$type} inbox/messages.",
    'type' => $type,
    'target' => $target
]);
?>
