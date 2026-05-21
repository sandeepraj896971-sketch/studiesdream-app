<?php
require_once 'common/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'common/header.php';
$u_id = $_SESSION['user_id'];
$orders = $conn->query("
    SELECT c.* FROM orders o 
    JOIN courses c ON o.course_id = c.id 
    WHERE o.user_id = $u_id AND o.status='success'
    ORDER BY o.id DESC
");

$ebook_orders = $conn->query("
    SELECT e.* FROM orders o 
    JOIN ebooks e ON o.ebook_id = e.id 
    WHERE o.user_id = $u_id AND o.status='success'
    ORDER BY o.id DESC
");
?>

<div class="px-4 py-4 pb-[80px]">    
    <!-- Tab Controls -->
    <div class="flex border-b border-gray-200 mb-4">
        <button id="tab-courses" onclick="switchTab('courses')" class="flex-1 py-3 text-sm font-bold text-primary border-b-2 border-primary text-center transition-colors">
            My Courses
        </button>
        <button id="tab-ebooks" onclick="switchTab('ebooks')" class="flex-1 py-3 text-sm font-bold text-gray-500 border-b-2 border-transparent text-center transition-colors hover:text-gray-700">
            My Notes / E-books
        </button>
    </div>

    <!-- Courses Container -->
    <div id="content-courses" class="grid grid-cols-1 gap-4 mb-6">
        <?php if($orders->num_rows > 0): while($c = $orders->fetch_assoc()): ?>
        <div class="flex bg-white border border-gray-100 shadow-sm overflow-hidden h-28">
            <div class="w-2/5 relative h-full bg-gray-200">
                <?php if($c['image']): ?>
                    <img src="uploads/courses/<?php echo htmlspecialchars($c['image']); ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-image text-3xl"></i></div>
                <?php endif; ?>
            </div>
            <div class="w-3/5 p-3 flex flex-col justify-between">
                <h3 class="font-bold text-gray-800 text-[13px] leading-snug line-clamp-2"><?php echo htmlspecialchars($c['title']); ?></h3>
                <a href="watch.php?course_id=<?php echo $c['id']; ?>" class="mt-auto inline-block text-center bg-primary text-white text-[11px] font-bold py-2 uppercase shadow-sm tracking-wide rounded">Start Learning <i class="fas fa-play ml-1"></i></a>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="text-center py-6 text-gray-400 bg-gray-50 rounded border border-dashed border-gray-200">
                <i class="fas fa-video text-3xl mb-2 text-gray-300"></i>
                <p class="text-sm">No courses enrolled yet.</p>
                <a href="course.php" class="text-primary font-bold mt-2 inline-block text-sm">Browse Courses</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- E-Books Container (Hidden by default) -->
    <div id="content-ebooks" class="grid grid-cols-1 gap-4 hidden">
        <?php if($ebook_orders->num_rows > 0): while($e = $ebook_orders->fetch_assoc()): ?>
        <div class="flex bg-white border border-gray-100 shadow-sm overflow-hidden h-28">
            <div class="w-2/5 relative h-full bg-gray-200">
                <?php if($e['image']): ?>
                    <img src="uploads/courses/<?php echo htmlspecialchars($e['image']); ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-book text-3xl"></i></div>
                <?php endif; ?>
            </div>
            <div class="w-3/5 p-3 flex flex-col justify-between">
                <h3 class="font-bold text-gray-800 text-[13px] leading-snug line-clamp-2"><?php echo htmlspecialchars($e['title']); ?></h3>
                <a href="ebook_detail.php?id=<?php echo $e['id']; ?>" class="mt-auto inline-block text-center bg-green-500 hover:bg-green-600 text-white text-[11px] font-bold py-2 uppercase shadow-sm tracking-wide rounded">Read Notes <i class="fas fa-book-reader ml-1"></i></a>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="text-center py-6 text-gray-400 bg-gray-50 rounded border border-dashed border-gray-200">
                <i class="fas fa-book-open text-3xl mb-2 text-gray-300"></i>
                <p class="text-sm">No E-Books purchased yet.</p>
                <a href="books_notes.php" class="text-primary font-bold mt-2 inline-block text-sm">Explore E-Books</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function switchTab(tab) {
    const tabC = document.getElementById('tab-courses');
    const tabE = document.getElementById('tab-ebooks');
    const contentC = document.getElementById('content-courses');
    const contentE = document.getElementById('content-ebooks');

    if(tab === 'courses') {
        tabC.className = "flex-1 py-3 text-sm font-bold text-primary border-b-2 border-primary text-center transition-colors";
        tabE.className = "flex-1 py-3 text-sm font-bold text-gray-500 border-b-2 border-transparent text-center transition-colors hover:text-gray-700";
        contentC.classList.remove('hidden');
        contentE.classList.add('hidden');
    } else {
        tabE.className = "flex-1 py-3 text-sm font-bold text-primary border-b-2 border-primary text-center transition-colors";
        tabC.className = "flex-1 py-3 text-sm font-bold text-gray-500 border-b-2 border-transparent text-center transition-colors hover:text-gray-700";
        contentE.classList.remove('hidden');
        contentC.classList.add('hidden');
    }
}
</script>

<?php include 'common/bottom.php'; ?>
