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

$is_enrolled = false;
if($test['is_free']) {
    $is_enrolled = true;
} else {
    $check = $conn->query("SELECT * FROM orders WHERE user_id=$user_id AND mock_test_id=$test_id AND status='success'");
    if($check && $check->num_rows > 0) {
        $is_enrolled = true;
    }
}

if(!$is_enrolled) {
    header("Location: buy_mock_test.php?id=$test_id");
    exit;
}

// Check if already completed and show results
$check_res = $conn->query("SELECT * FROM mock_test_results WHERE user_id=$user_id AND test_id=$test_id ORDER BY id DESC LIMIT 1");
if ($check_res && $check_res->num_rows > 0) {
    header("Location: mock_test_result.php?id=$test_id");
    exit;
}

// Fetch all questions
$q_res = $conn->query("SELECT * FROM mock_test_questions WHERE test_id=$test_id");
$questions = [];
while ($q = $q_res->fetch_assoc()) {
    $questions[] = $q;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_test'])) {
    $score = 0;
    $total = count($questions);
    
    foreach ($questions as $q) {
        $qid = $q['id'];
        if (isset($_POST["q_$qid"]) && $_POST["q_$qid"] === $q['correct_opt']) {
            $score++;
        }
    }
    
    $conn->query("INSERT INTO mock_test_results (user_id, test_id, score, total) VALUES ($user_id, $test_id, $score, $total)");
    header("Location: mock_test_result.php?id=$test_id");
    exit;
}

    $duration = (int)$test['duration']; // duration in minutes
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Take Mock Test - <?php echo htmlspecialchars($test['title']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .option-radio:checked + div {
            background-color: #eff6ff;
            border-color: #3b82f6;
        }
        .option-radio:checked + div .ring {
            border-color: #3b82f6;
            background-color: #3b82f6;
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col h-screen oncontextmenu='return false;'">

    <!-- Header -->
    <header class="bg-white border-b h-[60px] flex items-center px-4 justify-between shrink-0 shadow-sm sticky top-0 z-50">
        <div class="flex items-center gap-3 w-1/2">
            <button onclick="history.back()" class="text-gray-600 text-xl p-2 shrink-0"><i class="fas fa-times"></i></button>
            <h1 class="text-lg font-bold text-gray-800 truncate"><?php echo htmlspecialchars($test['title']); ?></h1>
        </div>
        <div class="flex items-center gap-4 text-sm font-bold text-gray-500">
            <?php if($duration > 0): ?>
            <div class="flex items-center text-red-500 bg-red-50 px-2 py-1 rounded">
                <i class="far fa-clock mr-1.5"></i>
                <span id="timerDisplay"><?php 
                    $h = floor($duration / 60);
                    $m = $duration % 60;
                    echo ($h>0?sprintf("%02d:", $h):"").sprintf("%02d:00", $m); 
                ?></span>
            </div>
            <?php endif; ?>
            <div>
                <span id="q_count">1</span> / <?php echo count($questions); ?>
            </div>
        </div>
    </header>

    <?php if(empty($questions)): ?>
        <div class="flex-1 flex flex-col items-center justify-center p-4">
            <i class="fas fa-exclamation-triangle text-5xl text-yellow-500 mb-3"></i>
            <h2 class="text-xl font-bold">No Questions Yet!</h2>
            <p class="text-gray-500 mt-2 text-center">Admin hasn't added any questions to this test yet.</p>
            <button onclick="history.back()" class="mt-4 px-6 py-2 bg-primary text-white rounded font-bold">Go Back</button>
        </div>
    <?php else: ?>
        <div class="flex-1 overflow-y-auto px-4 py-6 pb-24">
            <form id="testForm" method="POST">
                <?php $idx=0; foreach($questions as $q): $idx++; ?>
                <div class="question-block mb-8 bg-white p-5 rounded-xl border border-gray-100 shadow-sm <?php echo $idx==1 ? '' : 'hidden'; ?>" id="qblock_<?php echo $idx; ?>">
                    <h3 class="font-bold text-gray-800 text-lg mb-4 leading-snug"><span class="text-primary mr-1">Q<?php echo $idx; ?>.</span> <?php echo nl2br(htmlspecialchars($q['question'])); ?></h3>
                    
                    <div class="space-y-3">
                        <label class="block cursor-pointer relative">
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="A" class="option-radio sr-only" onchange="autoNext()">
                            <div class="border rounded-lg p-4 flex items-center transition-colors">
                                <span class="ring w-5 h-5 rounded-full border-2 border-gray-300 mr-3 flex shrink-0 items-center justify-center"><i class="fas fa-check text-[10px] text-white"></i></span>
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($q['opt_a']); ?></span>
                            </div>
                        </label>
                        <label class="block cursor-pointer relative">
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="B" class="option-radio sr-only" onchange="autoNext()">
                            <div class="border rounded-lg p-4 flex items-center transition-colors">
                                <span class="ring w-5 h-5 rounded-full border-2 border-gray-300 mr-3 flex shrink-0 items-center justify-center"><i class="fas fa-check text-[10px] text-white"></i></span>
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($q['opt_b']); ?></span>
                            </div>
                        </label>
                        <label class="block cursor-pointer relative">
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="C" class="option-radio sr-only" onchange="autoNext()">
                            <div class="border rounded-lg p-4 flex items-center transition-colors">
                                <span class="ring w-5 h-5 rounded-full border-2 border-gray-300 mr-3 flex shrink-0 items-center justify-center"><i class="fas fa-check text-[10px] text-white"></i></span>
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($q['opt_c']); ?></span>
                            </div>
                        </label>
                        <label class="block cursor-pointer relative">
                            <input type="radio" name="q_<?php echo $q['id']; ?>" value="D" class="option-radio sr-only" onchange="autoNext()">
                            <div class="border rounded-lg p-4 flex items-center transition-colors">
                                <span class="ring w-5 h-5 rounded-full border-2 border-gray-300 mr-3 flex shrink-0 items-center justify-center"><i class="fas fa-check text-[10px] text-white"></i></span>
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($q['opt_d']); ?></span>
                            </div>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <input type="hidden" name="submit_test" value="1">
            </form>
        </div>

        <div class="fixed bottom-0 w-full bg-white border-t p-4 flex justify-between gap-4 max-w-lg mx-auto shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <button type="button" onclick="prevQ()" id="btn_prev" class="w-1/3 py-3 border border-gray-300 text-gray-600 font-bold rounded-lg opacity-50" disabled>Previous</button>
            <button type="button" onclick="nextQ()" id="btn_next" class="flex-1 bg-primary text-white font-bold rounded-lg text-lg hidden">Next</button>
            <button type="button" onclick="submitTest()" id="btn_submit" class="flex-1 bg-green-500 text-white font-bold rounded-lg text-lg hidden">Submit Quiz</button>
        </div>
    <?php endif; ?>

    <script>
        let currentQ = 1;
        const totalQ = <?php echo count($questions); ?>;
        
        <?php if($duration > 0): ?>
        let timeLeft = <?php echo $duration * 60; ?>;
        const timerEl = document.getElementById('timerDisplay');
        const timerInterval = setInterval(function() {
            timeLeft--;
            if (timeLeft < 0) {
                clearInterval(timerInterval);
                alert("Time is up! Your test will be submitted automatically.");
                document.getElementById('testForm').submit();
                return;
            }
            
            let h = Math.floor(timeLeft / 3600);
            let m = Math.floor((timeLeft % 3600) / 60);
            let s = timeLeft % 60;
            
            let timeStr = "";
            if (h > 0) timeStr += h.toString().padStart(2, '0') + ":";
            timeStr += m.toString().padStart(2, '0') + ":" + s.toString().padStart(2, '0');
            
            timerEl.innerText = timeStr;
            
            if(timeLeft <= 60) {
                timerEl.parentElement.classList.add('animate-pulse', 'bg-red-100');
            }
        }, 1000);
        <?php endif; ?>
        
        function updateUI() {
            document.querySelectorAll('.question-block').forEach(el => el.classList.add('hidden'));
            document.getElementById('qblock_' + currentQ).classList.remove('hidden');
            
            document.getElementById('q_count').innerText = currentQ;
            
            document.getElementById('btn_prev').disabled = (currentQ === 1);
            if(currentQ === 1) {
                document.getElementById('btn_prev').classList.add('opacity-50');
            } else {
                document.getElementById('btn_prev').classList.remove('opacity-50');
            }
            
            if(currentQ === totalQ) {
                document.getElementById('btn_next').classList.add('hidden');
                document.getElementById('btn_submit').classList.remove('hidden');
            } else {
                document.getElementById('btn_next').classList.remove('hidden');
                document.getElementById('btn_submit').classList.add('hidden');
            }
        }
        
        function autoNext() {
            if(currentQ < totalQ) {
                setTimeout(nextQ, 300);
            }
        }
        
        function nextQ() {
            if(currentQ < totalQ) { currentQ++; updateUI(); }
        }
        
        function prevQ() {
            if(currentQ > 1) { currentQ--; updateUI(); }
        }
        
        function submitTest() {
            if (confirm("Are you sure you want to submit your test?")) {
                document.getElementById('testForm').submit();
            }
        }
        
        if(totalQ > 0) updateUI();
    </script>
</body>
</html>
