<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$db = 'studies_dream';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE TABLE IF NOT EXISTS app_downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    url VARCHAR(255),
    type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS ext_mock_tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    link VARCHAR(255),
    is_free BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$mock_cols = [
    'type' => "VARCHAR(20) DEFAULT 'link'",
    'html_content' => "TEXT",
    'mrp' => "DECIMAL(10,2) DEFAULT 0",
    'price' => "DECIMAL(10,2) DEFAULT 0",
    'quiz_type' => "ENUM('manual', 'embed', 'gform') DEFAULT 'manual'",
    'question' => "TEXT",
    'option_a' => "VARCHAR(255)",
    'option_b' => "VARCHAR(255)",
    'option_c' => "VARCHAR(255)",
    'option_d' => "VARCHAR(255)",
    'correct_answer' => "VARCHAR(10)",
    'html_code' => "TEXT",
    'gform_url' => "VARCHAR(255)"
];
foreach($mock_cols as $c => $t) {
    try { $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN $c $t"); } catch(Exception $e) {}
}

$conn->query("CREATE TABLE IF NOT EXISTS live_classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    youtube_link VARCHAR(255),
    is_active BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$cols = [
    'video_type' => "VARCHAR(20) DEFAULT 'youtube'",
    'video_file' => "VARCHAR(255)",
    'course_id' => "INT DEFAULT 0",
    'is_free' => "BOOLEAN DEFAULT 1",
    'is_active' => "BOOLEAN DEFAULT 0",
    'thumbnail' => "VARCHAR(255)"
];
foreach($cols as $c => $t) {
    try {
        $conn->query("ALTER TABLE live_classes ADD COLUMN $c $t");
    } catch(Exception $e) {}
}

$conn->query("CREATE TABLE IF NOT EXISTS ebooks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    mrp DECIMAL(10,2) DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0,
    is_free BOOLEAN DEFAULT 0,
    description TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS ebook_folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ebook_id INT,
    title VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS ebook_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folder_id INT,
    title VARCHAR(255),
    file_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS app_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    app_name VARCHAR(255),
    logo_url VARCHAR(255),
    header_color text,
    footer_color text,
    facebook_url VARCHAR(255),
    youtube_url VARCHAR(255),
    instagram_url VARCHAR(255),
    whatsapp_url VARCHAR(255)
)");

$res_app = $conn->query("SELECT * FROM app_settings LIMIT 1");
if(!$res_app || $res_app->num_rows == 0) {
    if ($conn->query("SHOW TABLES LIKE 'app_settings'")->num_rows > 0) {
        $conn->query("INSERT INTO app_settings (app_name, header_color, footer_color) VALUES ('Studies Dream', '#ffffff', '#1f2937')");
    }
}

try {
    $conn->query("CREATE TABLE IF NOT EXISTS mock_test_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        test_id INT NOT NULL,
        score INT DEFAULT 0,
        total INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $conn->query("CREATE TABLE IF NOT EXISTS mock_test_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        test_id INT,
        question TEXT,
        opt_a VARCHAR(255),
        opt_b VARCHAR(255),
        opt_c VARCHAR(255),
        opt_d VARCHAR(255),
        correct_opt VARCHAR(10)
    )");
    
    $conn->query("ALTER TABLE ext_current_affairs ADD COLUMN video_url VARCHAR(255) NULL");
} catch(Exception $e) {}

$conn->query("UPDATE courses SET is_free=0 WHERE price > 0");
$conn->query("UPDATE ebooks SET is_free=0 WHERE price > 0");
$conn->query("UPDATE ext_mock_tests SET is_free=0 WHERE price > 0");
$conn->query("UPDATE ext_current_affairs SET is_free=0 WHERE price > 0");

$conn->query("UPDATE courses SET is_free=1 WHERE price = 0");
$conn->query("UPDATE ebooks SET is_free=1 WHERE price = 0");
$conn->query("UPDATE ext_mock_tests SET is_free=1 WHERE price = 0");
$conn->query("UPDATE ext_current_affairs SET is_free=1 WHERE price = 0");

try {
    $conn->query("ALTER TABLE ext_current_affairs ADD COLUMN video_file VARCHAR(255) NULL");
    $conn->query("ALTER TABLE ext_current_affairs ADD COLUMN thumbnail VARCHAR(255) NULL");
    $conn->query("ALTER TABLE ext_current_affairs ADD COLUMN price DECIMAL(10,2) DEFAULT 0");
    $conn->query("ALTER TABLE ext_current_affairs ADD COLUMN is_free BOOLEAN DEFAULT 1");
} catch(Exception $e) {}

try {
    $conn->query("ALTER TABLE ext_current_affairs ADD COLUMN description TEXT NULL");
} catch(Exception $e) {}

try {
    $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN type VARCHAR(50) DEFAULT 'link'");
    $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN html_content TEXT NULL");
    $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN mrp DECIMAL(10,2) DEFAULT 0");
    $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN price DECIMAL(10,2) DEFAULT 0");
    $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN duration INT DEFAULT 0");
    $conn->query("ALTER TABLE ext_mock_tests ADD COLUMN subject VARCHAR(255) NULL");
} catch(Exception $e) {}

try {
    $conn->query("ALTER TABLE users ADD COLUMN last_read_notif_id INT DEFAULT 0");
} catch(Exception $e) {}

try {
    $conn->query("ALTER TABLE orders ADD COLUMN ebook_id INT NULL");
    $conn->query("ALTER TABLE orders ADD COLUMN mock_test_id INT NULL");
} catch(Exception $e) {}
?>
