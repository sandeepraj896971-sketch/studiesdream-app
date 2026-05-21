<?php
require_once '../common/config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_live'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $video_type = $conn->real_escape_string($_POST['video_type']);
    $youtube_link = $conn->real_escape_string($_POST['youtube_link'] ?? '');
    $course_id = (int)($_POST['course_id'] ?? 0);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Check if video file uploaded
    $video_file = '';
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        if (!is_dir('../uploads/live')) {
            mkdir('../uploads/live', 0777, true);
        }
        $ext = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
        $video_file = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['video_file']['tmp_name'], '../uploads/live/' . $video_file);
    }
    
    // Check if thumbnail uploaded
    $thumbnail = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
        if (!is_dir('../uploads/thumbnails')) {
            mkdir('../uploads/thumbnails', 0777, true);
        }
        $ext = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
        $thumbnail = 'thumb_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../uploads/thumbnails/' . $thumbnail);
    }
    
    // Deactivate others if this is active (optional, but usually only one live at a time)
    if ($is_active) {
        $conn->query("UPDATE live_classes SET is_active=0");
    }

    if (!empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $up_q = "UPDATE live_classes SET title='$title', video_type='$video_type', course_id=$course_id, is_free=$is_free, is_active=$is_active, youtube_link='$youtube_link'";
        if ($video_file) {
            $up_q .= ", video_file='$video_file'";
        }
        if ($thumbnail) {
            $up_q .= ", thumbnail='$thumbnail'";
        }
        $up_q .= " WHERE id=$id";
        $conn->query($up_q) or die($conn->error);
    } else {
        $conn->query("INSERT INTO live_classes (title, video_type, youtube_link, video_file, course_id, is_free, is_active, thumbnail) VALUES ('$title', '$video_type', '$youtube_link', '$video_file', $course_id, $is_free, $is_active, '$thumbnail')") or die($conn->error);
    }
    header("Location: live_classes.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM live_classes WHERE id=$id");
    header("Location: live_classes.php");
    exit;
}

// Handle Toggle Active
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $st = (int)$_GET['st'];
    
    // Deactivate others if making active
    if ($st == 1) {
         $conn->query("UPDATE live_classes SET is_active=0");
    }
    
    $conn->query("UPDATE live_classes SET is_active=$st WHERE id=$id");
    header("Location: live_classes.php");
    exit;
}

// Handle Archive to Course
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['archive_live'])) {
    $chapter_id = (int)$_POST['chapter_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $video_type = $conn->real_escape_string($_POST['a_type'] ?? 'youtube');
    $video_url = $conn->real_escape_string($_POST['a_link'] ?? '');
    
    // Insert video
    $conn->query("INSERT INTO videos (chapter_id, title, video_type, video_url) VALUES ($chapter_id, '$title', '$video_type', '$video_url')");
    
    // Check if PDF uploaded
    $pdf_file_name = '';
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        if (!is_dir('../uploads/notes')) {
            mkdir('../uploads/notes', 0777, true);
        }
        $ext = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
        $pdf_file_name = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], '../uploads/notes/' . $pdf_file_name);
    } else {
        $pdf_file_name = $conn->real_escape_string($_POST['pdf_link'] ?? '');
    }
    
    if($pdf_file_name) {
        $note_url = (strpos($pdf_file_name, 'http') === 0) ? $pdf_file_name : 'uploads/notes/' . $pdf_file_name;
        $note_title = $title . " Class Notes";
        $conn->query("INSERT INTO notes (chapter_id, title, gdrive_link) VALUES ($chapter_id, '$note_title', '$note_url')");
    }
    
    header("Location: live_classes.php");
    exit;
}

$lives = $conn->query("SELECT * FROM live_classes ORDER BY id DESC");
include 'common/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Live Classes</h1>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="bg-primary text-white px-4 py-2 rounded shadow hover:bg-primary/90">
        <i class="fas fa-plus mr-2"></i>Add Live Class
    </button>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 border-b">
                <th class="p-4 font-semibold text-gray-600">Title</th>
                <th class="p-4 font-semibold text-gray-600">YouTube Link</th>
                <th class="p-4 font-semibold text-gray-600 w-24 text-center">Status</th>
                <th class="p-4 font-semibold text-gray-600 w-32 text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($l = $lives->fetch_assoc()): ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-4"><?php echo htmlspecialchars($l['title']); ?></td>
                <td class="p-4 text-blue-500 max-w-xs truncate"><a href="<?php echo htmlspecialchars($l['youtube_link']); ?>" target="_blank"><?php echo htmlspecialchars($l['youtube_link']); ?></a></td>
                <td class="p-4 text-center">
                    <?php if($l['is_active']): ?>
                        <a href="live_classes.php?toggle=<?php echo $l['id']; ?>&st=0" class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold uppercase transition hover:bg-green-200">Active</a>
                    <?php else: ?>
                        <a href="live_classes.php?toggle=<?php echo $l['id']; ?>&st=1" class="inline-block px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-bold uppercase transition hover:bg-gray-200">Inactive</a>
                    <?php endif; ?>
                </td>
                <td class="p-4 flex gap-2 justify-center">
                    <button onclick="editLive(<?php echo htmlspecialchars(json_encode($l)); ?>)" class="text-blue-500 hover:bg-blue-50 p-2 rounded" title="Edit"><i class="fas fa-edit"></i></button>
                    <button onclick="archiveLive(<?php echo htmlspecialchars(json_encode($l)); ?>)" class="text-green-500 hover:bg-green-50 p-2 rounded" title="Add to Course"><i class="fas fa-save"></i></button>
                    <a href="live_classes.php?delete=<?php echo $l['id']; ?>" class="text-red-500 hover:bg-red-50 p-2 rounded" onclick="return confirm('Delete this live class?');"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php if($lives->num_rows == 0): ?>
            <tr><td colspan="4" class="p-4 text-center text-gray-500">No live classes found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="addModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4" id="modalTitle">Add Live Class</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="l_id">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Class Title</label>
                <input type="text" name="title" id="l_title" required class="w-full border rounded p-2 focus:ring-2 focus:ring-primary/50 outline-none">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Video Source Type</label>
                <select name="video_type" id="l_type" required class="w-full border rounded p-2 outline-none" onchange="toggleVideoFields()">
                    <option value="youtube">YouTube Link</option>
                    <option value="file">Upload Video File</option>
                </select>
            </div>
            
            <div class="mb-4" id="f_youtube">
                <label class="block text-sm font-semibold mb-1">YouTube Link / Video ID</label>
                <input type="text" name="youtube_link" id="l_link" placeholder="https://youtube.com/watch?v=XXXX or just XXXX" class="w-full border rounded p-2 focus:ring-2 focus:ring-primary/50 outline-none">
            </div>
            
            <div class="mb-4 hidden" id="f_file">
                <label class="block text-sm font-semibold mb-1">Upload Video File</label>
                <input type="file" name="video_file" accept=".mp4,.webm,.mkv" class="w-full border rounded p-1 text-sm outline-none">
                <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing file when editing.</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Upload Thumbnail Image</label>
                <input type="file" name="thumbnail" accept="image/*" class="w-full border rounded p-1 text-sm outline-none">
                <p class="text-xs text-gray-500 mt-1">Leave empty to keep existing image when editing.</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Access Options</label>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" name="is_free" value="1" id="l_free" class="w-4 h-4 text-primary focus:ring-primary" checked onchange="toggleCourseReq()">
                    <label class="text-sm font-semibold" for="l_free">Free for Everyone</label>
                </div>
            </div>
            
            <div class="mb-4" id="f_course" style="display:none;">
                <label class="block text-sm font-semibold mb-1">Link to Course (Only enrolled students can view)</label>
                <select name="course_id" id="l_course" class="w-full border rounded p-2 outline-none">
                    <option value="0">-- Select Course (Optional) --</option>
                    <?php
                    $c_rows = $conn->query("SELECT id, title FROM courses ORDER BY id DESC");
                    while($cr = $c_rows->fetch_assoc()) {
                        echo "<option value='{$cr['id']}'>".htmlspecialchars($cr['title'])."</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox" name="is_active" id="l_active" value="1" class="w-4 h-4 text-primary focus:ring-primary" checked>
                <label class="text-sm font-semibold" for="l_active">Set as Active (Currently Live)</label>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                <button type="submit" name="save_live" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Archive Modal -->
<div id="archiveModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">Add to Course (VOD)</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="a_type" id="a_type">
            <input type="hidden" name="a_link" id="a_link">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Class Title</label>
                <input type="text" name="title" id="a_title" required class="w-full border rounded p-2 focus:ring-2 focus:ring-primary/50 outline-none">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-semibold mb-1">Select Course Chapter</label>
                <select name="chapter_id" required class="w-full border rounded p-2 outline-none">
                    <option value="">-- Select Chapter --</option>
                    <?php
                    $courses = $conn->query("SELECT * FROM courses ORDER BY id DESC");
                    while($c = $courses->fetch_assoc()):
                        echo "<optgroup label='".htmlspecialchars($c['title'])."'>";
                        $cid = $c['id'];
                        $chs = $conn->query("SELECT * FROM chapters WHERE course_id=$cid");
                        while($ch = $chs->fetch_assoc()) {
                            echo "<option value='".$ch['id']."'> - ".htmlspecialchars($ch['title'])."</option>";
                        }
                        echo "</optgroup>";
                    endwhile;
                    ?>
                </select>
            </div>
            
            <div class="mb-4 border-t pt-4">
                <label class="block text-sm font-semibold mb-1">Attach PDF Class Notes (Optional)</label>
                <input type="file" name="pdf_file" accept=".pdf" class="w-full border rounded p-1 mb-2">
                <div class="text-xs text-gray-400 mb-1 text-center">OR</div>
                <input type="url" name="pdf_link" placeholder="External Drive Link" class="w-full border rounded p-2 text-sm outline-none">
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('archiveModal').classList.add('hidden')" class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                <button type="submit" name="archive_live" class="px-4 py-2 bg-green-500 text-white font-bold rounded shadow hover:bg-green-600">Save to Course</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleVideoFields() {
    let t = document.getElementById('l_type').value;
    if(t == 'youtube') {
        document.getElementById('f_youtube').classList.remove('hidden');
        document.getElementById('f_file').classList.add('hidden');
    } else {
        document.getElementById('f_youtube').classList.add('hidden');
        document.getElementById('f_file').classList.remove('hidden');
    }
}
function toggleCourseReq() {
    let f = document.getElementById('l_free').checked;
    if(f) {
        document.getElementById('f_course').style.display = 'none';
        document.getElementById('l_course').value = '0';
    } else {
        document.getElementById('f_course').style.display = 'block';
    }
}
function editLive(data) {
    document.getElementById('modalTitle').textContent = 'Edit Live Class';
    document.getElementById('l_id').value = data.id;
    document.getElementById('l_title').value = data.title;
    document.getElementById('l_type').value = data.video_type || 'youtube';
    document.getElementById('l_link').value = data.youtube_link || '';
    document.getElementById('l_free').checked = data.is_free == 1;
    document.getElementById('l_course').value = data.course_id || '0';
    document.getElementById('l_active').checked = data.is_active == 1;
    
    toggleVideoFields();
    toggleCourseReq();
    document.getElementById('addModal').classList.remove('hidden');
}

function archiveLive(data) {
    document.getElementById('a_title').value = data.title;
    let t = data.video_type || 'youtube';
    document.getElementById('a_type').value = data.video_type || 'youtube';
    document.getElementById('a_link').value = t === 'youtube' ? data.youtube_link : data.video_file;
    document.getElementById('archiveModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modalTitle').textContent = 'Add Live Class';
    document.getElementById('l_id').value = '';
    document.getElementById('l_title').value = '';
    document.getElementById('l_type').value = 'youtube';
    document.getElementById('l_link').value = '';
    document.getElementById('l_free').checked = true;
    document.getElementById('l_course').value = '0';
    document.getElementById('l_active').checked = true;
    toggleVideoFields();
    toggleCourseReq();
    document.getElementById('addModal').classList.add('hidden');
}
</script>

<?php include 'common/bottom.php'; ?>
