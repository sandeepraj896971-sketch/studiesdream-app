<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
require_once '../common/config.php';

$ebook_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if(!$ebook_id) die("Invalid Ebook ID");

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_folder'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $conn->query("INSERT INTO ebook_folders (ebook_id, title) VALUES ($ebook_id, '$title')");
    header("Location: ebook_manage.php?id=$ebook_id");
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_note'])) {
    $folder_id = (int)$_POST['folder_id'];
    $title = $conn->real_escape_string($_POST['title']);
    
    $file_url = '';
    // Handle PDF upload
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        if (!is_dir('../uploads/notes')) {
            mkdir('../uploads/notes', 0777, true);
        }
        $ext = pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
        $file_url = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], '../uploads/notes/' . $file_url);
    } else {
        // Fallback to URL if provided
        $file_url = $conn->real_escape_string($_POST['file_url'] ?? '');
    }
    
    $conn->query("INSERT INTO ebook_notes (folder_id, title, file_url) VALUES ($folder_id, '$title', '$file_url')");
    header("Location: ebook_manage.php?id=$ebook_id");
    exit;
}

if(isset($_GET['del_folder'])) {
    $fid = (int)$_GET['del_folder'];
    $conn->query("DELETE FROM ebook_notes WHERE folder_id=$fid");
    $conn->query("DELETE FROM ebook_folders WHERE id=$fid");
    header("Location: ebook_manage.php?id=$ebook_id");
    exit;
}

if(isset($_GET['del_note'])) {
    $nid = (int)$_GET['del_note'];
    $conn->query("DELETE FROM ebook_notes WHERE id=$nid");
    header("Location: ebook_manage.php?id=$ebook_id");
    exit;
}

$eb = $conn->query("SELECT * FROM ebooks WHERE id=$ebook_id")->fetch_assoc();
include 'common/header.php';
?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <a href="books_notes.php" class="text-blue-500 hover:underline text-sm mb-2 inline-block"><i class="fas fa-arrow-left"></i> Back to Packages</a>
        <h1 class="text-2xl font-bold">Manage: <?php echo htmlspecialchars($eb['title']); ?></h1>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column: Add Folder & Add Note -->
    <div class="space-y-6">
        <div class="bg-white rounded shadow p-4">
            <h2 class="font-bold mb-4 border-b pb-2">Add Folder/Subject</h2>
            <form method="POST">
                <div class="mb-3">
                    <label class="block text-sm mb-1">Folder Name</label>
                    <input type="text" name="title" required class="w-full border p-2 rounded">
                </div>
                <button type="submit" name="add_folder" class="bg-blue-600 text-white px-4 py-2 rounded w-full">Create Folder</button>
            </form>
        </div>

        <div class="bg-white rounded shadow p-4">
            <h2 class="font-bold mb-4 border-b pb-2">Upload Note (PDF)</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="block text-sm mb-1">Select Folder</label>
                    <select name="folder_id" required class="w-full border p-2 rounded">
                        <option value="">-- Select Folder --</option>
                        <?php
                        $folders = $conn->query("SELECT * FROM ebook_folders WHERE ebook_id=$ebook_id");
                        while($f = $folders->fetch_assoc()) {
                            echo "<option value='".$f['id']."'>".htmlspecialchars($f['title'])."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1">Note Title</label>
                    <input type="text" name="title" required class="w-full border p-2 rounded">
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1">Upload PDF File</label>
                    <input type="file" name="pdf_file" accept=".pdf" class="w-full border p-2 rounded text-sm">
                </div>
                <div class="mb-3">
                    <label class="block text-sm mb-1 text-gray-500">OR Drive Link URL</label>
                    <input type="url" name="file_url" class="w-full border p-2 rounded text-sm" placeholder="https://...">
                </div>
                <button type="submit" name="add_note" class="bg-green-600 text-white px-4 py-2 rounded w-full">Upload Note</button>
            </form>
        </div>
    </div>

    <!-- Right Column: Folders & Notes List -->
    <div class="lg:col-span-2 space-y-4">
        <?php
        $folders->data_seek(0);
        while($f = $folders->fetch_assoc()):
            $fid = $f['id'];
            $notes = $conn->query("SELECT * FROM ebook_notes WHERE folder_id=$fid");
        ?>
        <div class="bg-white rounded shadow-sm border">
            <div class="bg-gray-50 p-3 flex justify-between items-center border-b">
                <h3 class="font-bold text-gray-800"><i class="fas fa-folder-open text-yellow-500 mr-2"></i><?php echo htmlspecialchars($f['title']); ?></h3>
                <a href="?id=<?php echo $ebook_id; ?>&del_folder=<?php echo $fid; ?>" onclick="return confirm('Delete folder and all its notes?')" class="text-red-500 text-sm hover:underline"><i class="fas fa-trash"></i> Delete</a>
            </div>
            <div class="p-3">
                <?php if($notes->num_rows > 0): ?>
                    <ul class="space-y-2">
                        <?php while($n = $notes->fetch_assoc()): ?>
                        <li class="flex justify-between items-center p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100">
                            <span class="flex items-center gap-2 text-sm">
                                <i class="fas fa-file-pdf text-red-500"></i>
                                <?php echo htmlspecialchars($n['title']); ?>
                            </span>
                            <div class="flex gap-3 text-sm">
                                <?php if(strpos($n['file_url'], 'http') === 0): ?>
                                    <a href="../view_pdf.php?url=<?php echo urlencode($n['file_url']); ?>&title=<?php echo urlencode($n['title']); ?>" class="text-blue-500 hover:underline">Link</a>
                                <?php else: ?>
                                    <a href="../view_pdf.php?url=<?php echo urlencode('uploads/notes/'.$n['file_url']); ?>&title=<?php echo urlencode($n['title']); ?>" class="text-blue-500 hover:underline">View PDF</a>
                                <?php endif; ?>
                                <a href="?id=<?php echo $ebook_id; ?>&del_note=<?php echo $n['id']; ?>" onclick="return confirm('Delete note?')" class="text-red-500 hover:underline"><i class="fas fa-times"></i></a>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-sm text-gray-400 italic py-2">No notes uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if($folders->num_rows == 0): ?>
            <div class="bg-white rounded shadow-sm border p-8 text-center text-gray-500">
                <i class="fas fa-folder text-4xl text-gray-300 mb-3"></i>
                <p>No folders created yet. Create a folder to start adding notes.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
