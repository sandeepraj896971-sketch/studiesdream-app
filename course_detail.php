<?php
require_once 'common/config.php';

// Handle Enroll Free inline via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll_free') {
    header('Content-Type: application/json');
    if(!isset($_SESSION['user_id'])) {
        echo json_encode(['success'=>false, 'message'=>'Not logged in']);
        exit;
    }
    $c_id = (int)$_POST['course_id'];
    $u_id = (int)$_SESSION['user_id'];
    
    // Check if free
    $c = $conn->query("SELECT is_free FROM courses WHERE id=$c_id")->fetch_assoc();
    if($c && $c['is_free']) {
        $check = $conn->query("SELECT id FROM orders WHERE user_id=$u_id AND course_id=$c_id");
        if($check->num_rows == 0) {
            $conn->query("INSERT INTO orders (user_id, course_id, amount, status) VALUES ($u_id, $c_id, 0, 'success')");
        }
        echo json_encode(['success'=>true]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Course not free']);
    }
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$c_res = $conn->query("SELECT * FROM courses WHERE id=$id");
if ($c_res->num_rows == 0) {
    echo "Course not found.";
    exit;
}
$course = $c_res->fetch_assoc();

$enrolled = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $chk = $conn->query("SELECT id FROM orders WHERE user_id=$u_id AND course_id=$id AND status='success'");
    if($chk->num_rows > 0) $enrolled = true;
}

include 'common/header.php';
?>

<!-- Course Image -->
<div class="w-full aspect-video bg-gray-200 relative">
    <?php if($course['image']): ?>
        <img src="uploads/courses/<?php echo htmlspecialchars($course['image']); ?>" class="w-full h-full object-cover">
    <?php else: ?>
        <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-image text-4xl"></i></div>
    <?php endif; ?>
</div>

<div class="p-4 mb-20"> <!-- margin bottom to clear sticky bar -->
    <div class="flex items-center gap-2 mb-2">
        <span class="inline-block <?php echo $course['is_free'] ? 'bg-green-500' : 'bg-primary'; ?> text-white text-[10px] font-bold px-2 py-1 uppercase">
            <?php echo $course['is_free'] ? 'FREE COURSE' : 'PAID COURSE'; ?>
        </span>
    </div>
    
    <h1 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($course['title']); ?></h1>
    
    <?php if(!$course['is_free']): ?>
    <div class="flex items-center gap-2 mb-4">
        <span class="text-2xl font-bold text-primary">₹<?php echo floatval($course['price']); ?></span>
        <?php if($course['mrp'] > $course['price']): ?>
            <span class="text-gray-400 line-through">₹<?php echo floatval($course['mrp']); ?></span>
            <span class="text-green-500 text-xs font-bold ml-1"><?php echo round((($course['mrp']-$course['price'])/$course['mrp'])*100); ?>% OFF</span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="mt-4">
        <h3 class="font-bold text-gray-800 border-b pb-2 mb-2">Description</h3>
        <div class="text-sm text-gray-600 space-y-2 leading-relaxed">
            <?php echo nl2br(htmlspecialchars($course['description'])); ?>
        </div>
    </div>
</div>

<!-- Sticky Bottom Bar -->
<div class="fixed bottom-0 left-0 right-0 p-3 bg-white border-t z-50 pb-SAFE shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="login.php" class="block w-full bg-primary text-white text-center py-3 font-bold text-sm uppercase">Login to Enroll</a>
    <?php elseif($enrolled): ?>
        <a href="watch.php?course_id=<?php echo $course['id']; ?>" class="block w-full bg-green-500 text-white text-center py-3 font-bold text-sm uppercase shadow-sm">Start Course</a>
    <?php elseif($course['is_free']): ?>
        <button onclick="enrollFree()" class="w-full bg-primary text-white text-center py-3 font-bold text-sm uppercase shadow-sm">Enroll for Free</button>
    <?php else: ?>
        <a href="buy.php?id=<?php echo $course['id']; ?>" class="block w-full bg-primary text-white text-center py-3 font-bold text-sm uppercase shadow-sm">Buy Now (₹<?php echo floatval($course['price']); ?>)</a>
    <?php endif; ?>
</div>

<script>
    // Remove the bottom nav from this page to avoid double sticky
    document.querySelector('nav.fixed.bottom-0').style.display = 'none';

    function enrollFree() {
        fetch('course_detail.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=enroll_free&course_id=<?php echo $course['id']; ?>'
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error enrolling');
            }
        });
    }
</script>

<?php include 'common/bottom.php'; ?>
