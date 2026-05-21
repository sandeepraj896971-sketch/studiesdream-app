<?php
require_once 'common/config.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $ch_id = (int)$_POST['chapter_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $link = $conn->real_escape_string($_POST['link'] ?? '');
    
    // File Upload handling
    if(isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        if (!is_dir('../uploads/notes')) {
            mkdir('../uploads/notes', 0777, true);
        }
        $ext = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
        $filename = 'note_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], '../uploads/notes/' . $filename);
        $link = 'uploads/notes/' . $filename;
    }

    $conn->query("INSERT INTO notes (chapter_id, title, gdrive_link) VALUES ($ch_id, '$title', '$link')");
    
    $notif_title = $conn->real_escape_string("New PDF Note Added");
    $notif_msg = $conn->real_escape_string("A new note/PDF '$title' has been uploaded.");
    $conn->query("INSERT INTO app_notifications (title, message) VALUES ('$notif_title', '$notif_msg')");
    
    $c = $conn->query("SELECT course_id FROM chapters WHERE id=$ch_id")->fetch_assoc();
    header("Location: notes.php?course_id=".$c['course_id']);
    exit;
}

if(isset($_GET['del'])) {
    $conn->query("DELETE FROM notes WHERE id=".(int)$_GET['del']);
    header("Location: notes.php");
    exit;
}

$c_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
include 'common/header.php';
?>
<div class="mb-6"><h2 class="text-2xl font-bold">Manage PDF Notes</h2></div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-1 bg-white p-6 border shadow-sm">
        <form method="POST" enctype="multipart/form-data">
            <h3 class="font-bold mb-4 border-b pb-2">Add Note/PDF</h3>
            <div class="mb-4">
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
                <select name="chapter_id" required class="w-full p-2 border bg-gray-50">
                    <?php
                    $chs = $conn->query("SELECT * FROM chapters WHERE course_id=$c_id");
                    while($ch = $chs->fetch_assoc()):
                        echo "<option value='".$ch['id']."'>".htmlspecialchars($ch['title'])."</option>";
                    endwhile;
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <input type="text" name="title" required placeholder="Material Title" class="w-full p-2 border outline-none">
            </div>
            
            <div class="mb-4 border p-3 bg-gray-50 rounded">
                <label class="font-bold text-sm mb-2 block">Source (Choose one)</label>
                <div class="mb-3">
                    <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Option 1: Upload File</span>
                    <input type="file" name="pdf_file" accept=".pdf" class="w-full p-1 border outline-none bg-white text-sm">
                </div>
                <div class="text-center font-bold text-gray-400 text-xs my-2">- OR -</div>
                <div>
                    <span class="text-xs text-gray-500 font-bold uppercase block mb-1">Option 2: Google Drive Link</span>
                    <input type="text" name="link" placeholder="G-Drive Share Link" class="w-full p-2 border outline-none text-sm">
                </div>
            </div>

            <button type="submit" name="save" class="bg-red-500 text-white w-full py-2 font-bold"><i class="fas fa-file-pdf"></i> ADD MATERIAL</button>
            <?php endif; ?>
        </form>
    </div>

    <div class="xl:col-span-2 bg-white border shadow-sm p-4">
        <h3 class="font-bold mb-4">Materials List</h3>
        <?php if($c_id > 0): 
            $chs = $conn->query("SELECT * FROM chapters WHERE course_id=$c_id ORDER BY id ASC");
            while($ch = $chs->fetch_assoc()):
                $chid = $ch['id'];
                $ns = $conn->query("SELECT * FROM notes WHERE chapter_id=$chid");
                if($ns->num_rows > 0):
        ?>
            <div class="mb-4">
                <h4 class="bg-gray-100 p-2 font-bold text-sm border-l-4 border-red-500"><?php echo htmlspecialchars($ch['title']); ?></h4>
                <div class="divide-y border border-t-0">
                    <?php while($n = $ns->fetch_assoc()): ?>
                    <div class="flex justify-between p-3 items-center">
                        <div>
                            <span class="text-sm font-semibold flex items-center gap-2 mb-1"><i class="fas fa-file-pdf text-red-500"></i> <?php echo htmlspecialchars($n['title']); ?></span>
                            <span class="text-xs text-blue-500 truncate inline-block max-w-[250px]"><?php echo htmlspecialchars($n['gdrive_link']); ?></span>
                        </div>
                        <a href="?del=<?php echo $n['id']; ?>" class="text-red-500 bg-red-50 p-2 rounded"><i class="fas fa-trash"></i></a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; endwhile; endif; ?>
    </div>
</div>
<?php include 'common/bottom.php'; ?>
