<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
require_once '../common/config.php';

// Add new Ebook package
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $mrp = (float)$_POST['mrp'];
    $price = (float)$_POST['price'];
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        if (!is_dir('../uploads/courses')) {
            mkdir('../uploads/courses', 0777, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/courses/' . $image);
    }
    
    $conn->query("INSERT INTO ebooks (title, mrp, price, is_free, image) VALUES ('$title', $mrp, $price, $is_free, '$image')");
    
    $notif_title = $conn->real_escape_string("New E-Book Added");
    $notif_msg = $conn->real_escape_string("A new E-Book package '$title' has been released.");
    $conn->query("INSERT INTO app_notifications (title, message) VALUES ('$notif_title', '$notif_msg')");
    
    header("Location: books_notes.php");
    exit;
}

if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM ebooks WHERE id=$id");
    header("Location: books_notes.php");
    exit;
}
include 'common/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Notes / E-Books Packages</h1>
</div>

<div class="bg-white rounded shadow p-4 mb-6">
    <h2 class="font-bold mb-4">Create New Package</h2>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Package Title</label>
            <input type="text" name="title" required class="w-full border p-2 rounded">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">MRP (₹)</label>
                <input type="number" step="0.01" name="mrp" class="w-full border p-2 rounded" value="0">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Selling Price (₹)</label>
                <input type="number" step="0.01" name="price" class="w-full border p-2 rounded" value="0">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Thumbnail Image</label>
            <input type="file" name="image" accept="image/*" class="w-full border p-2 rounded">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_free" id="is_free" value="1">
            <label for="is_free" class="text-sm font-medium">Make it Free?</label>
        </div>
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded font-bold">Create Package</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $res = $conn->query("SELECT * FROM ebooks ORDER BY id DESC");
    while($e = $res->fetch_assoc()):
    ?>
    <div class="bg-white border rounded shadow-sm overflow-hidden">
        <?php if($e['image']): ?>
        <img src="../uploads/courses/<?php echo htmlspecialchars($e['image']); ?>" class="w-full h-40 object-cover">
        <?php else: ?>
        <div class="w-full h-40 bg-gray-200 flex items-center justify-center"><i class="fas fa-book text-4xl text-gray-400"></i></div>
        <?php endif; ?>
        <div class="p-4">
            <h3 class="font-bold text-lg mb-2 truncate"><?php echo htmlspecialchars($e['title']); ?></h3>
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm font-bold <?php echo $e['is_free'] ? 'text-green-600' : 'text-blue-600'; ?>">
                    <?php echo $e['is_free'] ? 'FREE' : '₹'.$e['price']; ?>
                </span>
            </div>
            <div class="flex justify-between items-center">
                <a href="ebook_manage.php?id=<?php echo $e['id']; ?>" class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded font-bold text-sm">Manage Folders/Notes</a>
                <a href="?del=<?php echo $e['id']; ?>" onclick="return confirm('Delete this package?')" class="text-red-500 hover:bg-red-50 p-1.5 rounded"><i class="fas fa-trash"></i></a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'common/bottom.php'; ?>
