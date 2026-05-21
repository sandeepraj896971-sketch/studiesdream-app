<?php
// fetch_pdf.php
// Proxy PDF files so they can be downloaded by JS without CORS issues
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Unauthorized');
}

$url = isset($_GET['url']) ? $_GET['url'] : '';
if (empty($url)) {
    http_response_code(400);
    die('No URL provided');
}

// Allow PDF.js library to safely fetch from same origin
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/pdf');

// Convert internal paths to absolute internally
if (strpos($url, 'uploads/') === 0 || strpos($url, '../uploads/') === 0) {
    if (strpos($url, '../') === 0) {
        $path = realpath(__DIR__ . '/' . substr($url, 3));
    } else {
        $path = realpath(__DIR__ . '/' . $url);
    }
    
    // Prevent directory traversal attacks
    $base_dir = realpath(__DIR__ . '/uploads');
    if ($path === false || strpos($path, $base_dir) !== 0) {
        http_response_code(403);
        die('Forbidden');
    }
    
    if (file_exists($path)) {
        header('Content-Disposition: inline; filename="document.pdf"');
        readfile($path);
        exit;
    } else {
        http_response_code(404);
        die('File not found');
    }
}

// Google Drive proxy logic
if (strpos($url, 'drive.google.com') !== false) {
    // Attempt to parse out the ID and use google's direct download link
    preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches);
    $fileID = $matches[1] ?? '';
    
    if (!$fileID) {
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
        $fileID = $query['id'] ?? '';
    }
    
    if ($fileID) {
        $downloadUrl = "https://drive.google.com/uc?export=download&id=" . $fileID;
        
        $context = stream_context_create([
            'http' => [
                'follow_location' => true,
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
            ]
        ]);
        
        $content = @file_get_contents($downloadUrl, false, $context);
        if($content) {
            echo $content;
            exit;
        }
    }
}

// Default fallback fetching
$context = stream_context_create([
    'http' => [
        'follow_location' => true,
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
    ]
]);

$content = @file_get_contents($url, false, $context);
if ($content) {
    echo $content;
} else {
    http_response_code(404);
    die("Unable to fetch PDF");
}
