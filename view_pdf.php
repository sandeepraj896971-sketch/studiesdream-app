<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$url = $_GET['url'] ?? '';
$title = $_GET['title'] ?? 'PDF Document';

if (!$url) {
    die("Invalid URL.");
}

// Check if already downloaded
require_once 'common/config.php';
$user_id = $_SESSION['user_id'];
$is_downloaded = false;
$stmt = $conn->prepare("SELECT id FROM app_downloads WHERE user_id=? AND url=?");
$stmt->bind_param("is", $user_id, $url);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $is_downloaded = true;
}

// Convert google drive url safely to preview format if needed
$embedUrl = $url;
if(strpos($url, 'drive.google.com/file/d/') !== false) {
    if(preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $match)) {
        $embedUrl = "https://drive.google.com/file/d/" . $match[1] . "/preview";
    }
} else if (strpos($url, 'http') !== 0) {
    $embedUrl = $url . "#toolbar=0";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background-color: #000; }
    </style>
</head>
<body class="flex flex-col h-screen oncontextmenu="return false;">
    
    <!-- Header -->
    <header class="bg-primary text-white h-[60px] flex items-center px-4 justify-between shrink-0 shadow-md z-50">
        <div class="flex items-center gap-3 overflow-hidden">
            <button onclick="history.back()" class="text-white text-xl p-2 shrink-0"><i class="fas fa-arrow-left"></i></button>
            <h1 class="text-lg font-medium truncate"><?php echo htmlspecialchars($title); ?></h1>
        </div>
        <div class="shrink-0 flex items-center">
            <!-- Download disabled by admin request -->
        </div>
    </header>

    <!-- Content -->
    <div class="flex-1 w-full bg-white relative">
        <iframe src="<?php echo htmlspecialchars($embedUrl); ?>" class="w-full h-full border-none" allowfullscreen></iframe>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-4 py-2 rounded shadow-lg text-sm transition-opacity duration-300 opacity-0 pointer-events-none z-50">
        Saved to Downloads
    </div>

    <script>
        // Use PHP values directly where needed. For the fetch call:
        const pdfTitle = <?php echo json_encode($title); ?>;
        const pdfUrl = <?php echo json_encode($url); ?>;
        const pdfType = 'pdf';

        function saveDownload() {
            const btn = document.getElementById('downloadBtn');
            const icon = btn.querySelector('i');
            
            // Show loading
            icon.className = 'fas fa-spinner fa-spin text-white';
            
            $.post('ajax_download.php', {
                title: pdfTitle,
                url: pdfUrl,
                type: pdfType
            }, function(response) {
                try {
                    const res = JSON.parse(response);
                    if(res.success) {
                        icon.className = 'fas fa-check-circle text-green-300';
                        btn.onclick = null; // disable further clicks
                        btn.style.cursor = 'default';
                        showToast("Saved to your App Downloads");
                    } else {
                        icon.className = 'fas fa-exclamation-circle text-red-500';
                        showToast(res.message || "Failed to save");
                        setTimeout(() => icon.className = 'fas fa-download text-white', 2000);
                    }
                } catch(e) {
                    icon.className = 'fas fa-exclamation-circle text-red-500';
                    showToast("Error processing request");
                    setTimeout(() => icon.className = 'fas fa-download text-white', 2000);
                }
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.classList.remove('opacity-0');
            toast.classList.add('opacity-100');
            setTimeout(() => {
                toast.classList.remove('opacity-100');
                toast.classList.add('opacity-0');
            }, 3000);
        }
    </script>
</body>
</html>
