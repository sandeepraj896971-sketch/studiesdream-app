<?php
require_once 'common/config.php';
// checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = $conn->real_escape_string($_POST['title']);
    $mrp = !empty($_POST['mrp']) ? (float)$_POST['mrp'] : 0;
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0;
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    if ($is_free) {
        $price = 0;
    } else if ($price == 0) {
        $is_free = 1;
    }
    $desc = isset($_POST['description']) ? $conn->real_escape_string($_POST['description']) : '';
    
    $img_sql = "";
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        // Ensure folder exists
        if (!is_dir('../uploads/courses')) {
            mkdir('../uploads/courses', 0777, true);
        }
        $filename = time() . '_' . preg_replace("/[^a-zA-Z0-9\.\-_]/", "", basename($_FILES['image']['name']));
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/courses/$filename");
        $img_sql = ", image='$filename'";
    }

    if ($id > 0) {
        $conn->query("UPDATE courses SET title='$title', mrp='$mrp', price='$price', is_free='$is_free', description='$desc' $img_sql WHERE id=$id") or die("Update error: " . $conn->error);
    } else {
        $img_val = isset($filename) ? "'$filename'" : "NULL";
        $conn->query("INSERT INTO courses (title, mrp, price, is_free, description, image) VALUES ('$title', '$mrp', '$price', '$is_free', '$desc', $img_val)") or die("Insert error: " . $conn->error);
        
        $notif_title = $conn->real_escape_string("New Course Uploaded");
        $notif_msg = $conn->real_escape_string("A new course '$title' has been added.");
        $conn->query("INSERT INTO app_notifications (title, message) VALUES ('$notif_title', '$notif_msg')");
    }
    header("Location: course.php");
    exit;
}

if(isset($_GET['del'])) {
    $conn->query("DELETE FROM courses WHERE id=".(int)$_GET['del']);
    header("Location: course.php");
    exit;
}

include 'common/header.php';

$edit = null;
if(isset($_GET['edit'])) {
    $edit = $conn->query("SELECT * FROM courses WHERE id=".(int)$_GET['edit'])->fetch_assoc();
}
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Manage Courses</h2>
    <?php if($edit): ?><a href="course.php" class="bg-gray-500 text-white px-4 py-2">Add New</a><?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white p-6 border shadow-sm h-fit">
        <form method="POST" enctype="multipart/form-data">
            <h3 class="font-bold mb-4 border-b pb-2"><?php echo $edit ? 'Edit Course' : 'Create Course'; ?></h3>
            <input type="hidden" name="id" value="<?php echo $edit['id']??''; ?>">
            
            <div class="mb-4">
                <label class="block text-sm mb-1">Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($edit['title']??''); ?>" required class="w-full p-2 border bg-gray-50 outline-none focus:border-primary">
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm mb-1">MRP</label>
                    <input type="number" step="0.01" name="mrp" value="<?php echo $edit['mrp']??''; ?>" class="w-full p-2 border bg-gray-50 outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm mb-1">Offer Price</label>
                    <input type="number" step="0.01" name="price" value="<?php echo $edit['price']??''; ?>" class="w-full p-2 border bg-gray-50 outline-none focus:border-primary">
                </div>
            </div>

            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" name="is_free" id="cb_free" value="1" <?php if(isset($edit['is_free']) && $edit['is_free']) echo 'checked'; ?> class="w-4 h-4">
                <label for="cb_free" class="font-bold cursor-pointer text-sm">Mark as FREE Course</label>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Thumbnail (16:9)</label>
                <input type="file" name="image" class="w-full p-1 border bg-gray-50 text-sm">
                <?php if(isset($edit['image']) && $edit['image']): ?>
                    <img src="../uploads/courses/<?php echo $edit['image']; ?>" class="w-24 mt-2 border">
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label class="block text-sm mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full p-2 border bg-gray-50 outline-none focus:border-primary"><?php echo htmlspecialchars($edit['description']??''); ?></textarea>
            </div>

            <button type="submit" name="save" class="bg-primary text-white w-full py-2 font-bold"><?php echo $edit ? 'UPDATE' : 'SAVE'; ?></button>
        </form>
    </div>

    <!-- List -->
    <div class="lg:col-span-2 bg-white border shadow-sm overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 border-b">
                    <th class="p-3 text-sm">Image</th>
                    <th class="p-3 text-sm">Details</th>
                    <th class="p-3 text-sm text-center">Type</th>
                    <th class="p-3 text-sm text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM courses ORDER BY id DESC");
                while($row = $res->fetch_assoc()):
                ?>
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3 w-24">
                        <?php if($row['image']): ?>
                            <img src="../uploads/courses/<?php echo htmlspecialchars($row['image']); ?>" class="w-20 aspect-video object-cover">
                        <?php endif; ?>
                    </td>
                    <td class="p-3">
                        <div class="font-bold text-sm text-gray-800"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="text-xs text-gray-500 mt-1">₹<?php echo floatval($row['price']); ?> <del>₹<?php echo floatval($row['mrp']); ?></del></div>
                    </td>
                    <td class="p-3 text-center">
                        <span class="text-[10px] font-bold px-2 py-1 <?php echo $row['is_free']?'bg-green-100 text-green-700':'bg-blue-100 text-blue-700'; ?> uppercase"><?php echo $row['is_free']?'Free':'Paid'; ?></span>
                    </td>
                    <td class="p-3 text-right whitespace-nowrap">
                        <a href="?edit=<?php echo $row['id']; ?>" class="bg-blue-500 text-white w-8 h-8 inline-flex items-center justify-center rounded"><i class="fas fa-edit"></i></a>
                        <a href="?del=<?php echo $row['id']; ?>" class="bg-red-500 text-white w-8 h-8 inline-flex items-center justify-center rounded" onclick="return confirm('Sure?');"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
