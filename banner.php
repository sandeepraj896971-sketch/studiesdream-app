<?php
require_once 'common/config.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $link = $conn->real_escape_string($_POST['link']);
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $filename = time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/banners/$filename");
        $conn->query("INSERT INTO banners (image, link) VALUES ('$filename', '$link')");
    }
}

if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $b = $conn->query("SELECT image FROM banners WHERE id=$id")->fetch_assoc();
    if($b) @unlink("../uploads/banners/".$b['image']);
    $conn->query("DELETE FROM banners WHERE id=$id");
    header("Location: banner.php");
    exit;
}

include 'common/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Manage Banners</h2>
</div>

<div class="bg-white p-6 border shadow-sm mb-6 max-w-xl">
    <form method="POST" enctype="multipart/form-data">
        <h3 class="font-bold mb-4 border-b pb-2">Add New Banner (16:9 ratio recommended)</h3>
        <div class="mb-4">
            <label class="block text-sm mb-1 text-gray-500">Image File</label>
            <input type="file" name="image" required class="w-full border p-2 bg-gray-50">
        </div>
        <div class="mb-4">
            <label class="block text-sm mb-1 text-gray-500">Target Link (Optional)</label>
            <input type="text" name="link" placeholder="e.g. course_detail.php?id=1" class="w-full p-2 bg-gray-50 border outline-none focus:border-primary">
        </div>
        <button type="submit" name="add" class="bg-primary text-white px-4 py-2 font-bold"><i class="fas fa-plus"></i> ADD BANNER</button>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php
    $res = $conn->query("SELECT * FROM banners ORDER BY id DESC");
    while($row = $res->fetch_assoc()):
    ?>
    <div class="bg-white border p-2 shadow-sm relative group">
        <img src="../uploads/banners/<?php echo htmlspecialchars($row['image']); ?>" class="w-full aspect-video object-cover">
        <div class="p-2 truncate text-sm text-gray-500"><?php echo htmlspecialchars($row['link']); ?></div>
        <a href="?del=<?php echo $row['id']; ?>" class="absolute top-4 right-4 bg-red-500 text-white w-8 h-8 flex items-center justify-center rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition" onclick="return confirm('Delete banner?');"><i class="fas fa-trash"></i></a>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'common/bottom.php'; ?>
