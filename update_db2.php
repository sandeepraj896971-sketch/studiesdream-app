<?php
require_once 'common/config.php';

$queries = [
    "CREATE TABLE IF NOT EXISTS app_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS ext_mock_tests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        link VARCHAR(255),
        is_free BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS ext_current_affairs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        file_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS ext_books_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        file_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($queries as $q) {
    if ($conn->query($q) === TRUE) {
        echo "Query successful<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}
echo "Done DB updates.";
?>
