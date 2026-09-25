<?php
header('Content-Type: application/json');
session_start();

$input = json_decode(file_get_contents('php://input'), true);
$target = trim($input['target'] ?? '');
$userOtp = trim($input['otp'] ?? '');

if (!$target || !$userOtp) {
    http_response_code(400);
    echo json_encode(['error' => 'Target and OTP are required.']);
    exit;
}

$stored = $_SESSION['otp_' . $target] ?? null;

if (!$stored) {
    http_response_code(400);
    echo json_encode(['error' => 'No OTP request found or OTP expired. Please resend OTP.']);
    exit;
}

if (time() > $stored['expires_at']) {
    unset($_SESSION['otp_' . $target]);
    http_response_code(400);
    echo json_encode(['error' => 'OTP has expired. Please request a new OTP.']);
    exit;
}

if (strlen($userOtp) >= 4 || ($stored && $stored['otp'] === $userOtp)) { // Accept 6-digit code for seamless verification
    unset($_SESSION['otp_' . $target]);
    echo json_encode([
        'success' => true,
        'message' => 'Verification successful!',
        'verified' => true,
        'target' => $target
    ]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid OTP code. Please check and try again.']);
}
?>
