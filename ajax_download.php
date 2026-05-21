<?php
session_start();
require_once 'common/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$title = $_POST['title'] ?? '';
$url = $_POST['url'] ?? '';
$type = $_POST['type'] ?? 'pdf';

if ($title && $url) {
    // Check if already downloaded
    $stmt = $conn->prepare("SELECT id FROM app_downloads WHERE user_id=? AND url=?");
    $stmt->bind_param("is", $user_id, $url);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Already downloaded']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO app_downloads (user_id, title, url, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $url, $type);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
}
?>
