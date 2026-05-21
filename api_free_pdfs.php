<?php
require_once 'common/config.php';
header('Content-Type: application/json');

$query = "
    SELECT n.id, n.title, n.gdrive_link, ch.title as chapter_title, c.title as course_title, c.image as course_image
    FROM notes n
    JOIN chapters ch ON n.chapter_id = ch.id
    JOIN courses c ON ch.course_id = c.id
    WHERE c.is_free = 1
    ORDER BY n.id DESC
";

$result = $conn->query($query);
$pdfs = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pdfs[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $pdfs]);
?>
