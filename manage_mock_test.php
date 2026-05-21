<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once '../common/config.php';

$test_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$test_id) die("Invalid test ID");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_q'])) {
    $q = $conn->real_escape_string($_POST['question']);
    $a = $conn->real_escape_string($_POST['opt_a']);
    $b = $conn->real_escape_string($_POST['opt_b']);
    $c = $conn->real_escape_string($_POST['opt_c']);
    $d = $conn->real_escape_string($_POST['opt_d']);
    $correct = $conn->real_escape_string($_POST['correct_opt']);
    
    $conn->query("INSERT INTO mock_test_questions (test_id, question, opt_a, opt_b, opt_c, opt_d, correct_opt) VALUES ($test_id, '$q', '$a', '$b', '$c', '$d', '$correct')");
    header("Location: manage_mock_test.php?id=$test_id");
    exit;
}

if (isset($_GET['del_q'])) {
    $qid = (int)$_GET['del_q'];
    $conn->query("DELETE FROM mock_test_questions WHERE id=$qid");
    header("Location: manage_mock_test.php?id=$test_id");
    exit;
}

$test = $conn->query("SELECT * FROM ext_mock_tests WHERE id=$test_id")->fetch_assoc();
$questions = $conn->query("SELECT * FROM mock_test_questions WHERE test_id=$test_id");

include 'common/header.php';
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="mock_tests.php" class="text-blue-500 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left"></i> Back to Mock Tests</a>
        <h1 class="text-2xl font-bold">Manage Quiz: <?php echo htmlspecialchars($test['title']); ?></h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="space-y-6">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-bold mb-4 border-b pb-2">Add Question</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="block text-sm mb-1">Question Text</label>
                    <textarea name="question" required class="w-full border p-2 rounded" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1">Option A</label>
                    <input type="text" name="opt_a" required class="w-full border p-2 rounded text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1">Option B</label>
                    <input type="text" name="opt_b" required class="w-full border p-2 rounded text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1">Option C</label>
                    <input type="text" name="opt_c" required class="w-full border p-2 rounded text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1">Option D</label>
                    <input type="text" name="opt_d" required class="w-full border p-2 rounded text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm mb-1 font-bold">Correct Option</label>
                    <select name="correct_opt" required class="w-full border p-2 rounded">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <button type="submit" name="add_q" class="bg-blue-600 text-white px-4 py-2 rounded w-full font-bold">Add Question</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <?php if($questions && $questions->num_rows > 0): ?>
            <?php $qnum = 1; while($q = $questions->fetch_assoc()): ?>
            <div class="bg-white border rounded shadow-sm p-4 relative">
                <a href="?id=<?php echo $test_id; ?>&del_q=<?php echo $q['id']; ?>" onclick="return confirm('Delete this question?')" class="absolute top-2 right-2 text-red-500 hover:bg-red-50 p-1.5 rounded" title="Delete"><i class="fas fa-trash"></i></a>
                
                <h3 class="font-bold text-gray-800 mb-2 pr-8 text-sm"><span class="text-primary mr-1">Q<?php echo $qnum++; ?>.</span> <?php echo nl2br(htmlspecialchars($q['question'])); ?></h3>
                
                <ul class="text-[13px] grid grid-cols-1 md:grid-cols-2 gap-2 text-gray-600">
                    <li class="p-2 border rounded <?php echo $q['correct_opt']=='A'?'bg-green-50 border-green-200 text-green-700 font-bold':''; ?>">A) <?php echo htmlspecialchars($q['opt_a']); ?></li>
                    <li class="p-2 border rounded <?php echo $q['correct_opt']=='B'?'bg-green-50 border-green-200 text-green-700 font-bold':''; ?>">B) <?php echo htmlspecialchars($q['opt_b']); ?></li>
                    <li class="p-2 border rounded <?php echo $q['correct_opt']=='C'?'bg-green-50 border-green-200 text-green-700 font-bold':''; ?>">C) <?php echo htmlspecialchars($q['opt_c']); ?></li>
                    <li class="p-2 border rounded <?php echo $q['correct_opt']=='D'?'bg-green-50 border-green-200 text-green-700 font-bold':''; ?>">D) <?php echo htmlspecialchars($q['opt_d']); ?></li>
                </ul>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="bg-white rounded border border-dashed py-8 text-center text-gray-400">
                <i class="fas fa-question-circle text-4xl mb-2 text-gray-300"></i>
                <p>No questions added yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
