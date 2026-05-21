<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit; }
require_once '../common/config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $message = $conn->real_escape_string($_POST['message']);
    $conn->query("INSERT INTO app_notifications (title, message) VALUES ('$title', '$message')");
    header("Location: notifications.php");
    exit;
}

if(isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM app_notifications WHERE id=$id");
    header("Location: notifications.php");
    exit;
}
include 'common/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Notifications</h1>
</div>

<div class="bg-white rounded shadow p-4 mb-6">
    <h2 class="font-bold mb-4">Send New Notification</h2>
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input type="text" name="title" required class="w-full border p-2 rounded">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Message</label>
            <textarea name="message" required rows="3" class="w-full border p-2 rounded"></textarea>
        </div>
        <button type="submit" name="add" class="bg-blue-600 text-white px-4 py-2 rounded">Send Notification</button>
    </form>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-3 text-left">Title</th>
                <th class="p-3 text-left">Message</th>
                <th class="p-3 text-left">Date</th>
                <th class="p-3 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $notifs = $conn->query("SELECT * FROM app_notifications ORDER BY id DESC");
            if($notifs) {
                while($n = $notifs->fetch_assoc()):
            ?>
            <tr class="border-t">
                <td class="p-3 font-semibold"><?php echo htmlspecialchars($n['title']); ?></td>
                <td class="p-3 text-sm text-gray-600"><?php echo htmlspecialchars($n['message']); ?></td>
                <td class="p-3 text-sm"><?php echo date('d M Y', strtotime($n['created_at'])); ?></td>
                <td class="p-3">
                    <a href="?del=<?php echo $n['id']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Delete this notification?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; } ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>
