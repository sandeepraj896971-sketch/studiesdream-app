<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
require_once '../common/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $url = $conn->real_escape_string($_POST['file_url'] ?? '');
    $video_url = $conn->real_escape_string($_POST['video_url'] ?? '');
    
    $thumbnail = '';
    if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0){
        if(!is_dir('../uploads/thumbnails')) { mkdir('../uploads/thumbnails', 0777, true); }
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $thumbnail = time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../uploads/thumbnails/' . $thumbnail);
    }
    
    $pdf_file = '';
    if(isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0){
        if(!is_dir('../uploads/notes')) { mkdir('../uploads/notes', 0777, true); }
        $ext = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
        $pdf_file = time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], '../uploads/notes/' . $pdf_file);
        $url = 'uploads/notes/' . $pdf_file;
    }
    
    $video_file = '';
    if(isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0){
        if(!is_dir('../uploads/videos')) { mkdir('../uploads/videos', 0777, true); }
        $ext = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
        $video_file = time() . '_' . rand(100, 999) . '.' . $ext;
        move_uploaded_file($_FILES['video_file']['tmp_name'], '../uploads/videos/' . $video_file);
        $video_url = 'uploads/videos/' . $video_file;
    }

    $conn->query("INSERT INTO ext_current_affairs (title, file_url, video_url, thumbnail) VALUES ('$title', '$url', '$video_url', '$thumbnail')") or die($conn->error);
    header("Location: current_affairs.php");
    exit;
}

if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM ext_current_affairs WHERE id=$id") or die($conn->error);
    header("Location: current_affairs.php");
    exit;
}
include 'common/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">YouTube Videos</h1>
</div>

<div class="bg-white rounded shadow p-4 mb-6">
    <h2 class="font-bold mb-4">Add New Record</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Title (e.g. New YouTube Video)</label>
            <input type="text" name="title" required class="w-full border p-2 rounded">
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border p-3 bg-gray-50 rounded">
                <label class="font-bold text-sm mb-2 block">Thumbnail Image</label>
                <input type="file" name="thumbnail" accept="image/*" class="w-full p-1 border bg-white mb-2 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border p-3 bg-gray-50 rounded">
                <label class="font-bold text-sm mb-2 block">Video Material (Choose one)</label>
                <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Option 1: Upload MP4 File</span>
                <input type="file" name="video_file" accept=".mp4" class="w-full p-1 border bg-white mb-2 text-sm">
                <div class="text-xs font-bold text-gray-400 my-1">- OR -</div>
                <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Option 2: YouTube URL</span>
                <input type="text" name="video_url" placeholder="https://youtube.com/watch?v=..." class="w-full border p-2 text-sm">
            </div>
            
            <div class="border p-3 bg-gray-50 rounded">
                <label class="font-bold text-sm mb-2 block">PDF Material (Choose one)</label>
                <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Option 1: Upload PDF File</span>
                <input type="file" name="pdf_file" accept=".pdf" class="w-full p-1 border bg-white mb-2 text-sm">
                <div class="text-xs font-bold text-gray-400 my-1">- OR -</div>
                <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Option 2: Drive/External URL</span>
                <input type="url" name="file_url" placeholder="https://..." class="w-full border p-2 text-sm">
            </div>
        </div>
        
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded font-bold w-full md:w-auto">Save Video</button>
    </form>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Title</th>
                <th class="p-3 text-left">PDF Link</th>
                <th class="p-3 text-left">Video</th>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rows = $conn->query("SELECT * FROM ext_current_affairs ORDER BY id DESC");
            if($rows) {
                while($n = $rows->fetch_assoc()):
            ?>
            <tr class="border-t">
                <td class="p-3 font-semibold"><?php echo htmlspecialchars($n['title']); ?></td>
                <td class="p-3 text-sm text-blue-600"><a href="../view_pdf.php?url=<?php echo urlencode($n['file_url']); ?>&title=<?php echo urlencode($n['title']); ?>" target="_blank">View PDF</a></td>
                <td class="p-3 text-sm text-blue-600">
                    <?php if($n['video_url']): ?>
                        <a href="<?php echo htmlspecialchars($n['video_url']); ?>" target="_blank">Watch</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td class="p-3 text-sm"><?php echo date('d M Y', strtotime($n['created_at'])); ?></td>
                <td class="p-3">
                    <a href="?del=<?php echo $n['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Delete this record?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; } ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>
