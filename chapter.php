<?php
require_once 'common/config.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $c_id = (int)$_POST['course_id'];
    $title = $conn->real_escape_string($_POST['title']);
    $conn->query("INSERT INTO chapters (course_id, title) VALUES ($c_id, '$title')");
    header("Location: chapter.php?course_id=$c_id");
    exit;
}

if(isset($_GET['del'])) {
    $conn->query("DELETE FROM chapters WHERE id=".(int)$_GET['del']);
    header("Location: chapter.php");
    exit;
}

$c_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
include 'common/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Manage Chapters</h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white p-6 border shadow-sm">
        <form method="POST">
            <h3 class="font-bold mb-4 border-b pb-2">Add Chapter</h3>
            <div class="mb-4">
                <label class="block text-sm mb-1">Select Course</label>
                <select name="course_id" class="w-full p-2 border bg-gray-50 outline-none" required onchange="window.location='?course_id='+this.value">
                    <option value="">-- Select --</option>
                    <?php
                    $courses = $conn->query("SELECT id, title FROM courses ORDER BY id DESC");
                    while($c = $courses->fetch_assoc()):
                        $sel = ($c_id == $c['id']) ? 'selected' : '';
                        echo "<option value='{$c['id']}' $sel>" . htmlspecialchars($c['title']) . "</option>";
                    endwhile;
                    ?>
                </select>
            </div>
            <?php if($c_id > 0): ?>
            <div class="mb-4">
                <label class="block text-sm mb-1">Chapter Title</label>
                <input type="text" name="title" required class="w-full p-2 border bg-gray-50 outline-none focus:border-primary">
            </div>
            <button type="submit" name="save" class="bg-primary text-white w-full py-2 font-bold">ADD CHAPTER</button>
            <?php else: ?>
                <div class="text-xs text-red-500">Select a course to add chapters</div>
            <?php endif; ?>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white border shadow-sm overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-3 text-sm">Chapter Name</th>
                    <th class="p-3 text-sm text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($c_id > 0) {
                    $res = $conn->query("SELECT * FROM chapters WHERE course_id=$c_id ORDER BY id ASC");
                    if($res->num_rows > 0): while($row = $res->fetch_assoc()):
                ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 font-bold text-sm"><?php echo htmlspecialchars($row['title']); ?></td>
                    <td class="p-3 text-right">
                        <a href="?del=<?php echo $row['id']; ?>" class="text-red-500 text-xl hover:text-red-700" onclick="return confirm('Sure?');"><i class="fas fa-trash-alt"></i></a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="2" class="p-4 text-center text-gray-400">No chapters added yet.</td></tr>
                <?php endif; } else { ?>
                    <tr><td colspan="2" class="p-4 text-center text-gray-400">Select a course to view chapters.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
