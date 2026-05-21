<?php
require_once 'common/config.php';
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    
    // Get the maximum notification id
    $res = $conn->query("SELECT MAX(id) as max_id FROM app_notifications");
    $max_id = $res && $res->num_rows > 0 ? (int)$res->fetch_assoc()['max_id'] : 0;
    
    if ($max_id > 0) {
        $conn->query("UPDATE users SET last_read_notif_id = $max_id WHERE id = $user_id");
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'not logged in']);
}
?>
