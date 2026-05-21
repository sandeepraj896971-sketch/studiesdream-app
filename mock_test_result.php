<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'common/config.php';

$test_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$test_id) die("Invalid test ID");
$user_id = $_SESSION['user_id'];

$test = $conn->query("SELECT * FROM ext_mock_tests WHERE id=$test_id")->fetch_assoc();
if (!$test) die("Test not found");

$res_q = $conn->query("SELECT * FROM mock_test_results WHERE user_id=$user_id AND test_id=$test_id ORDER BY id DESC LIMIT 1");
if ($res_q->num_rows == 0) {
    header("Location: take_mock_test.php?id=$test_id");
    exit;
}

$result = $res_q->fetch_assoc();
$total = $result['total'];
$percentage = $total > 0 ? round(($result['score'] / $total) * 100, 1) : 0;

include 'common/header.php';
?>

<div class="px-4 py-8 pb-[80px]">
    <div class="bg-white border rounded-xl overflow-hidden shadow-sm">
        <div class="bg-primary p-6 text-center text-white pb-10">
            <h1 class="font-bold text-xl mb-1">Test Completed!</h1>
            <p class="text-sm opacity-80"><?php echo htmlspecialchars($test['title']); ?></p>
        </div>
        
        <div class="bg-white rounded-xl -mt-6 mx-4 shadow-[0_2px_10px_rgba(0,0,0,0.1)] p-6 mb-6 text-center relative z-10 border border-gray-100">
            <p class="text-gray-500 font-bold uppercase text-xs tracking-wider mb-2">Your Score</p>
            <div class="flex items-end justify-center gap-2 mb-2">
                <span class="text-5xl font-black <?php echo $percentage >= 50 ? 'text-green-500' : 'text-red-500'; ?> leading-none"><?php echo $result['score']; ?></span>
                <span class="text-gray-400 font-bold text-xl mb-1 flex items-center">/ <?php echo $total; ?> <span class="ml-3 px-2 py-0.5 bg-gray-100 text-gray-700 text-sm rounded-full font-bold"><?php echo $percentage; ?>%</span></span>
            </div>
            
            <?php if($percentage >= 80): ?>
                <p class="text-green-600 font-bold mt-3"><i class="fas fa-trophy mr-1 text-yellow-500"></i> Excellent Work!</p>
            <?php elseif($percentage >= 50): ?>
                <p class="text-blue-500 font-bold mt-3"><i class="fas fa-thumbs-up mr-1 text-blue-500"></i> Good Job!</p>
            <?php else: ?>
                <p class="text-red-500 font-bold mt-3"><i class="fas fa-exclamation-circle mr-1 text-red-500"></i> Keep Practicing!</p>
            <?php endif; ?>
        </div>
        
        <div class="p-6 pt-2 grid grid-cols-2 gap-4 text-center">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                <i class="fas fa-check-circle text-green-500 text-2xl mb-1"></i>
                <p class="text-xs font-bold text-gray-500 uppercase">Correct</p>
                <p class="text-lg font-bold text-gray-800"><?php echo $result['score']; ?></p>
            </div>
            <div class="bg-rose-50 border border-rose-100 rounded-lg p-3">
                <i class="fas fa-times-circle text-red-500 text-2xl mb-1"></i>
                <p class="text-xs font-bold text-gray-500 uppercase">Incorrect</p>
                <p class="text-lg font-bold text-gray-800"><?php echo $result['total'] - $result['score']; ?></p>
            </div>
        </div>
        
        <div class="p-6 pt-0 space-y-3">
            <a href="mock_tests.php" class="block w-full bg-primary text-center text-white py-3 rounded-lg font-bold uppercase tracking-wide">Back to Tests</a>
        </div>
    </div>
</div>

<style>nav.fixed.bottom-0 { display: none; }</style>

<?php include 'common/bottom.php'; ?>
