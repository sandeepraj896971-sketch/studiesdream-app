<?php
// install.php
$host = '127.0.0.1';
$user = 'root';
$pass = 'root';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS studies_dream";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

$conn->select_db('studies_dream');

// Tables creation
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        phone VARCHAR(20),
        email VARCHAR(255),
        password VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255),
        password VARCHAR(255)
    )",
    "CREATE TABLE IF NOT EXISTS banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255),
        link VARCHAR(255)
    )",
    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        mrp DECIMAL(10,2),
        price DECIMAL(10,2),
        is_free BOOLEAN DEFAULT 0,
        description TEXT,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS chapters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT,
        title VARCHAR(255)
    )",
    "CREATE TABLE IF NOT EXISTS videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chapter_id INT,
        title VARCHAR(255),
        video_type ENUM('mp4', 'youtube'),
        video_url VARCHAR(255)
    )",
    "CREATE TABLE IF NOT EXISTS notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chapter_id INT,
        title VARCHAR(255),
        gdrive_link VARCHAR(255)
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        course_id INT,
        amount DECIMAL(10,2),
        status VARCHAR(50),
        razorpay_order_id VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        app_name VARCHAR(255),
        razorpay_key VARCHAR(255),
        razorpay_secret VARCHAR(255),
        support_email VARCHAR(255),
        support_phone VARCHAR(20),
        youtube_link VARCHAR(255),
        telegram_link VARCHAR(255),
        instagram_link VARCHAR(255),
        facebook_link VARCHAR(255)
    )",
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
    )",
    "CREATE TABLE IF NOT EXISTS app_downloads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title VARCHAR(255),
        url VARCHAR(255),
        type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Insert admin
$admin_check = "SELECT * FROM admin";
$res = $conn->query($admin_check);
if ($res->num_rows == 0) {
    $pass = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin (username, password) VALUES ('admin', '$pass')");
    echo "Default admin inserted.<br>";
}

// Ensure settings exist
$settings_check = "SELECT * FROM settings";
$res_set = $conn->query($settings_check);
if ($res_set->num_rows == 0) {
    $conn->query("INSERT INTO settings (app_name) VALUES ('Studies Dream')");
    echo "Default settings inserted.<br>";
}

// Create folders
$dirs = ['uploads/banners', 'uploads/courses', 'uploads/videos'];
foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
}

echo "<h3>Setup Complete!</h3>";
echo "<a href='admin/login.php'>Go to Admin Login</a>";
?>
