<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    die("Invalid Test ID.");
}

require_once 'common/config.php';
$stmt = $conn->prepare("SELECT * FROM ext_mock_tests WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die("Mock Test not found.");
}

$test = $res->fetch_assoc();

$is_enrolled = false;
$user_id = $_SESSION['user_id'];
if($test['is_free']) {
    $is_enrolled = true;
} else {
    $check = $conn->query("SELECT * FROM orders WHERE user_id=$user_id AND mock_test_id=$id AND status='success'");
    if($check && $check->num_rows > 0) {
        $is_enrolled = true;
    }
}

if(!$is_enrolled) {
    header("Location: buy_mock_test.php?id=$id");
    exit;
}

if ($test['quiz_type'] === 'manual' || $test['type'] === 'internal') {
    header("Location: take_mock_test.php?id=$id");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($test['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background-color: #fafafa; }
        /* Make sure the iframe takes full screen */
        .html-wrapper iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body class="flex flex-col h-screen" oncontextmenu="return false;">
    
    <!-- Header -->
    <header class="bg-primary text-white h-[60px] flex items-center px-4 justify-between shrink-0 shadow-md z-50">
        <div class="flex items-center gap-3 overflow-hidden">
            <button onclick="history.back()" class="text-white text-xl p-2 shrink-0"><i class="fas fa-arrow-left"></i></button>
            <h1 class="text-lg font-medium truncate"><?php echo htmlspecialchars($test['title']); ?></h1>
        </div>
        <div class="shrink-0 flex items-center">
            <span class="bg-white/20 text-xs px-2 py-1 rounded font-bold uppercase">
                <?php echo $test['is_free'] ? 'Free' : 'Paid'; ?>
            </span>
        </div>
    </header>

    <!-- Content -->
    <div class="flex-1 w-full bg-white relative html-wrapper">
        <?php if ($test['quiz_type'] === 'embed' || $test['type'] === 'html' || strpos($test['type'] ?? '', 'embed') !== false): ?>
            <?php echo !empty($test['html_code']) ? $test['html_code'] : $test['html_content']; ?>
        <?php elseif ($test['quiz_type'] === 'gform' || $test['type'] === 'link' || strpos($test['type'] ?? '', 'gform') !== false): ?>
            <iframe src="<?php echo htmlspecialchars(!empty($test['gform_url']) ? $test['gform_url'] : $test['link']); ?>" width="100%" height="100%" frameborder="0"></iframe>
        <?php else: ?>
            <p class="p-4 text-center mt-10">Invalid test format.</p>
        <?php endif; ?>
    </div>

</body>
</html>
