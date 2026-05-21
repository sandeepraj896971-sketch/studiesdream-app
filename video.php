<?php
require_once 'common/config.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $ch_id = (int)$_POST['chapter_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $type = $conn->real_escape_string($_POST['video_type']);
    
    $url = '';
    if($type == 'youtube') {
        $url = $conn->real_escape_string($_POST['youtube_url']);
    } else {
        if(isset($_FILES['mp4_file']) && $_FILES['mp4_file']['error'] == 0){
            if (!is_dir('../uploads/videos')) {
                mkdir('../uploads/videos', 0777, true);
            }
            $url = time() . '_' . $_FILES['mp4_file']['name'];
            move_uploaded_file($_FILES['mp4_file']['tmp_name'], "../uploads/videos/$url");
        }
    }

    if($url != '') {
        $conn->query("INSERT INTO videos (chapter_id, title, video_type, video_url) VALUES ($ch_id, '$title', '$type', '$url')");
    }
    
    // get course id to redirect back
    $c = $conn->query("SELECT course_id FROM chapters WHERE id=$ch_id")->fetch_assoc();
    header("Location: video.php?course_id=".$c['course_id']);
    exit;
}

if(isset($_GET['del'])) {
    $v = $conn->query("SELECT * FROM videos WHERE id=".(int)$_GET['del'])->fetch_assoc();
    if($v && $v['video_type']=='mp4') {
        @unlink("../uploads/videos/".$v['video_url']);
    }
    $conn->query("DELETE FROM videos WHERE id=".(int)$_GET['del']);
    header("Location: video.php");
    exit;
}

$c_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

include 'common/header.php';
?>

<div class="mb-6"><h2 class="text-2xl font-bold">Manage Videos</h2></div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-1 bg-white p-6 border shadow-sm h-fit">
        <form method="POST" enctype="multipart/form-data">
            <h3 class="font-bold mb-4 border-b pb-2">Add Video</h3>
            
            <div class="mb-4">
                <label class="block text-sm mb-1">Filter by Course</label>
                <select class="w-full p-2 border bg-gray-50 text-sm" onchange="window.location='?course_id='+this.value">
                    <option value="">-- Choose Course --</option>
                    <?php
                    $cs = $conn->query("SELECT id, title FROM courses");
                    while($c = $cs->fetch_assoc()):
                        $sel = ($c_id == $c['id']) ? 'selected' : '';
                        echo "<option value='".$c['id']."' $sel>".htmlspecialchars($c['title'])."</option>";
                    endwhile;
                    ?>
                </select>
            </div>

            <?php if($c_id > 0): ?>
            <div class="mb-4">
                <label class="block text-sm mb-1">Select Chapter</label>
                <select name="chapter_id" required class="w-full p-2 border bg-gray-50 outline-none">
                    <?php
                    $chs = $conn->query("SELECT * FROM chapters WHERE course_id=$c_id");
                    if($chs->num_rows==0) echo "<option value=''>No chapters found</option>";
                    while($ch = $chs->fetch_assoc()):
                        echo "<option value='".$ch['id']."'>".htmlspecialchars($ch['title'])."</option>";
                    endwhile;
                    ?>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm mb-1">Video Title</label>
                <input type="text" name="title" required class="w-full p-2 border outline-none">
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Video Source</label>
                <select name="video_type" id="vType" onchange="toggleType()" class="w-full p-2 border bg-gray-50 outline-none">
                    <option value="youtube">YouTube Embed URL</option>
                    <option value="mp4">Upload MP4</option>
                </select>
            </div>

            <div class="mb-4" id="div_yt">
                <label class="block text-sm mb-1">YouTube URL (Embed or Watch Link)</label>
                <input type="text" name="youtube_url" placeholder="https://www.youtube.com/embed/..." class="w-full p-2 border outline-none">
                <p class="text-xs text-gray-500 mt-1">Example: https://www.youtube.com/embed/dQw4w9WgXcQ</p>
            </div>

            <div class="mb-4 hidden" id="div_mp4">
                <label class="block text-sm mb-1">MP4 File</label>
                <input type="file" name="mp4_file" accept=".mp4" class="w-full border p-1 bg-gray-50 text-sm">
            </div>

            <button type="submit" name="save" class="bg-primary text-white w-full py-2 font-bold">ADD VIDEO</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="xl:col-span-2 bg-white border shadow-sm p-4">
        <h3 class="font-bold mb-4">Videos List</h3>
        <?php if($c_id > 0): 
            $chs = $conn->query("SELECT * FROM chapters WHERE course_id=$c_id ORDER BY id ASC");
            while($ch = $chs->fetch_assoc()):
                $chid = $ch['id'];
                $vs = $conn->query("SELECT * FROM videos WHERE chapter_id=$chid");
                if($vs->num_rows > 0):
        ?>
            <div class="mb-4">
                <h4 class="bg-gray-100 p-2 font-bold text-sm border-l-4 border-primary"><?php echo htmlspecialchars($ch['title']); ?></h4>
                <div class="divide-y border border-t-0">
                    <?php while($v = $vs->fetch_assoc()): ?>
                    <div class="flex justify-between items-center p-3 hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="<?php echo $v['video_type']=='youtube'?'bg-red-100 text-red-700':'bg-blue-100 text-blue-700'; ?> text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?php echo $v['video_type']; ?></span>
                            <span class="text-sm font-semibold"><?php echo htmlspecialchars($v['title']); ?></span>
                        </div>
                        <a href="?del=<?php echo $v['id']; ?>&course_id=<?php echo $c_id; ?>" title="Delete" onclick="return confirm('Sure?');" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; endwhile; else: ?>
            <p class="text-gray-400 text-sm">Select a course to view videos.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleType(){
    const t = document.getElementById('vType').value;
    if(t === 'youtube') {
        document.getElementById('div_yt').classList.remove('hidden');
        document.getElementById('div_mp4').classList.add('hidden');
    } else {
        document.getElementById('div_yt').classList.add('hidden');
        document.getElementById('div_mp4').classList.remove('hidden');
    }
}
</script>

<?php include 'common/bottom.php'; ?>
