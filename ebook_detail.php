<?php
session_start();
require_once 'common/config.php';
include 'common/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$c_res = $conn->query("SELECT * FROM ebooks WHERE id=$id");
if($c_res->num_rows == 0) die("E-Book not found");
$course = $c_res->fetch_assoc();

$is_enrolled = false;
$user_id = 0;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT * FROM orders WHERE user_id=$user_id AND ebook_id=$id AND status='success'");
    if($check && $check->num_rows > 0) {
        $is_enrolled = true;
    }
}
if($course['is_free']) {
    $is_enrolled = true;
}

$folders = $conn->query("SELECT * FROM ebook_folders WHERE ebook_id=$id ORDER BY id ASC");
?>

<div class="pb-[70px]">
    <!-- Course Header -->
    <div class="relative w-full aspect-video bg-gray-200">
        <?php if($course['image']): ?>
            <img src="uploads/courses/<?php echo htmlspecialchars($course['image']); ?>" class="w-full h-full object-cover">
        <?php else: ?>
            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                <i class="fas fa-book text-5xl mb-2"></i>
            </div>
        <?php endif; ?>
        <?php if(!$is_enrolled): ?>
        <div class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white p-4 text-center">
            <i class="fas fa-lock text-4xl mb-3 text-white"></i>
            <p class="font-bold mb-2">Unlock this E-Book to view all folders and notes</p>
            <a href="buy_ebook.php?id=<?php echo $id; ?>" class="bg-primary hover:bg-blue-600 font-bold px-6 py-2 rounded-full shadow-lg transition-transform active:scale-95 text-sm uppercase flex gap-2 items-center">
                <span>Buy for ₹<?php echo floatval($course['price']); ?></span>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Details -->
    <div class="p-4 bg-white border-b border-gray-100 shadow-sm relative">
        <h1 class="font-bold text-xl text-gray-800 leading-tight mb-2"><?php echo htmlspecialchars($course['title']); ?></h1>
        <div class="flex items-center gap-3">
            <?php if(!$course['is_free']): ?>
                <span class="font-bold text-primary text-xl">₹<?php echo floatval($course['price']); ?></span>
                <?php if($course['mrp'] > $course['price']): ?>
                    <span class="text-sm text-gray-400 line-through">₹<?php echo floatval($course['mrp']); ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span class="font-bold text-green-500 text-xl">Free Package</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Content / Folders -->
    <div class="p-4">
        <h2 class="font-bold text-gray-800 mb-4 text-lg">E-Book Content</h2>
        
        <div class="space-y-4">
            <?php if($folders->num_rows > 0): while($f = $folders->fetch_assoc()): ?>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden folder-acc" data-id="<?php echo $f['id']; ?>">
                <div class="p-4 flex justify-between items-center bg-gray-50 cursor-pointer text-gray-800" onclick="toggleFolder(<?php echo $f['id']; ?>)">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-folder text-yellow-500 text-xl"></i>
                        <span class="font-bold text-[15px]"><?php echo htmlspecialchars($f['title']); ?></span>
                    </div>
                    <i class="fas fa-chevron-down outline-none text-gray-400 transition-transform duration-300 transform" id="icon-<?php echo $f['id']; ?>"></i>
                </div>
                
                <div class="hidden bg-white border-t border-gray-100" id="content-<?php echo $f['id']; ?>">
                    <?php
                    $notes = $conn->query("SELECT * FROM ebook_notes WHERE folder_id=".$f['id']);
                    if($notes->num_rows > 0):
                        while($n = $notes->fetch_assoc()):
                    ?>
                    <div class="flex items-center gap-3 p-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors cursor-pointer" <?php if($is_enrolled): ?>onclick="openNote('<?php echo htmlspecialchars($n['file_url']); ?>', '<?php echo htmlspecialchars($n['title']); ?>')"<?php else: ?>onclick="alert('Please buy this E-Book package to access.')"<?php endif; ?>>
                        <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center shrink-0">
                            <i class="fas fa-file-pdf text-red-500 text-sm"></i>
                        </div>
                        <div class="flex-1 text-[14px] text-gray-700 truncate"><?php echo htmlspecialchars($n['title']); ?></div>
                        <?php if(!$is_enrolled): ?>
                            <i class="fas fa-lock text-gray-300 shrink-0 text-sm"></i>
                        <?php else: ?>
                            <i class="fas fa-external-link-alt text-gray-300 shrink-0 text-sm"></i>
                        <?php endif; ?>
                    </div>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                    <div class="p-4 text-center text-gray-400 text-sm border-b border-gray-50">No notes in this folder.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; else: ?>
                <div class="text-center py-8 text-gray-500 text-sm">Content will be updated soon.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleFolder(id) {
    const el = document.getElementById('content-'+id);
    const icon = document.getElementById('icon-'+id);
    if(el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        el.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}
function openNote(url, title) {
    let finalUrl = url;
    if(url.indexOf('http') !== 0) {
        finalUrl = 'uploads/notes/' + url;
    }
    window.location.href = 'pdf_viewer.php?url=' + encodeURIComponent(finalUrl);
}
</script>

<?php include 'common/bottom.php'; ?>
