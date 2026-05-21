<?php
require_once 'config.php';
$stmt = $conn->prepare("SELECT * FROM app_settings LIMIT 1");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    $app_settings = $res->fetch_assoc();
} else {
    $app_settings = [];
}

$app_name = $app_settings['app_name'] ?? 'Studies Dream';
$header_color = $app_settings['header_color'] ?? '#ffffff';
$logo_url = !empty($app_settings['logo_url']) ? 'uploads/logos/' . $app_settings['logo_url'] : '';

// Count notifications
$notif_count = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
    $u_res = $conn->query("SELECT last_read_notif_id FROM users WHERE id=$user_id");
    $last_read = $u_res ? (int)$u_res->fetch_assoc()['last_read_notif_id'] : 0;
    
    $notif_res = $conn->query("SELECT COUNT(*) as cnt FROM app_notifications WHERE id > $last_read");
    $notif_count = $notif_res ? $notif_res->fetch_assoc()['cnt'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($app_name); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/offline_dl.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0284C7',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            -webkit-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
            padding-bottom: 60px; /* Space for bottom nav */
            padding-top: 60px;
            background-color: #f3f4f6; /* light grey bg */
        }
        /* Hide scrollbar for clean app look */
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    </style>
</head>
<body class="text-gray-800" oncontextmenu="return false;">
    <!-- Top Header -->
    <header style="background-color: <?php echo htmlspecialchars($header_color); ?>;" class="fixed top-0 left-0 right-0 h-[60px] flex items-center justify-between px-3 z-50 shadow-sm border-b border-gray-100">
        <div class="flex items-center gap-3 w-1/3">
            <button onclick="toggleSidebar()" class="text-2xl text-gray-800 p-2"><i class="fas fa-bars"></i></button>
        </div>
        
        <div class="flex-1 flex justify-center w-1/3">
            <?php if($logo_url): ?>
                <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="App Logo" class="rounded-full object-cover" style="width: 45px; height: 45px;">
            <?php else: ?>
                <h1 class="text-[19px] font-medium tracking-wide text-gray-800 truncate max-w-[200px]"><?php echo htmlspecialchars($app_name); ?></h1>
            <?php endif; ?>
        </div>
        
        <div class="flex items-center justify-end gap-4 text-[22px] text-gray-800 w-1/3">
            <button onclick="document.getElementById('search-bar').classList.toggle('hidden')"><i class="fas fa-search pt-1"></i></button>
            <a href="notifications.php" onclick="markNotifRead(event)" class="relative mr-1 p-2">
                <i class="fas fa-bell"></i>
                <?php if($notif_count > 0): ?>
                <span id="notif-badge" class="absolute top-1 right-0 bg-red-600 text-white text-[10px] rounded-full h-[18px] min-w-[18px] flex items-center justify-center px-1 font-bold border-2 border-white"><?php echo $notif_count > 99 ? '99+' : $notif_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </header>

    <script>
    function markNotifRead(e) {
        let badge = document.getElementById('notif-badge');
        if (badge) {
            badge.style.display = 'none'; // dynamically disappear without reload
            
            // Send AJAX request in the background
            fetch('mark_read.php')
                .then(r => r.json())
                .catch(err => console.error(err));
        }
        // Allow navigation to proceed naturally
    }
    </script>

    <!-- Hidden Search Bar -->
    <div id="search-bar" class="hidden fixed top-[60px] left-0 right-0 bg-white p-3 shadow-md z-40 border-t border-gray-100">
        <form action="course.php" method="GET" class="flex gap-2">
            <input type="text" name="q" placeholder="Search courses..." class="w-full bg-gray-100 rounded-lg p-2 outline-none">
            <button type="submit" class="bg-primary text-white px-4 rounded-lg font-bold">Search</button>
        </form>
    </div>

    <?php include 'sidebar.php'; ?>
    
    <div id="main-content" class="min-h-screen pb-24">
