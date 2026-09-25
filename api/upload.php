<?php
header('Content-Type: application/json');

$targetDir = "../uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Handle Base64 Upload
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['image_data'])) {
    $imgData = $data['image_data'];
    if (preg_match('/^data:image\/(\w+);base64,/', $imgData, $type)) {
        $imgData = substr($imgData, strpos($imgData, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif, webp

        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png', 'webp'])) {
            echo json_encode(['error' => 'Invalid image type']);
            exit;
        }

        $imgData = base64_decode($imgData);
        if ($imgData === false) {
            echo json_encode(['error' => 'Base64 decode failed']);
            exit;
        }

        $fileName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $type;
        $filePath = $targetDir . $fileName;

        file_put_contents($filePath, $imgData);
        echo json_encode(['success' => true, 'url' => 'uploads/' . $fileName]);
        exit;
    }
}

// Handle Standard Multipart File Upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = $_FILES['image']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = 'file_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
        $destPath = $targetDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            echo json_encode(['success' => true, 'url' => 'uploads/' . $newFileName]);
            exit;
        }
    }
}

echo json_encode(['error' => 'No valid image file received']);
?>
