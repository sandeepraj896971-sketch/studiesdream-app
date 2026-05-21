<?php
require_once 'common/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$u_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $conn->query("UPDATE users SET name='$name' WHERE id=$u_id");
    $_SESSION['user_name'] = $name;
    $msg = "Profile updated successfully!";
}

$user = $conn->query("SELECT * FROM users WHERE id=$u_id")->fetch_assoc();

include 'common/header.php';
?>

<div class="px-4 py-6">
    <div class="flex flex-col items-center mb-6">
        <i class="fas fa-user-circle text-6xl text-gray-300 mb-2"></i>
        <h2 class="text-xl font-bold"><?php echo htmlspecialchars($user['name']); ?></h2>
        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <?php if(isset($msg)): ?>
        <div class="bg-green-100 text-green-700 p-3 text-sm mb-4"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="POST" class="bg-white p-4 border mb-4">
        <h3 class="font-bold mb-4 border-b pb-2">Edit Profile</h3>
        <div class="mb-4">
            <label class="block text-xs text-gray-500 mb-1">Full Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required class="w-full p-2 bg-gray-50 border border-gray-200 outline-none focus:border-primary">
        </div>
        <div class="mb-4">
            <label class="block text-xs text-gray-500 mb-1">Phone</label>
            <input type="text" value="<?php echo htmlspecialchars($user['phone']); ?>" disabled class="w-full p-2 bg-gray-100 border border-gray-200 text-gray-500 outline-none">
        </div>
        <button type="submit" name="update_profile" class="w-full bg-primary text-white py-2 font-bold mb-2">UPDATE</button>
    </form>

    <a href="logout.php" class="block w-full text-center bg-white border border-red-500 text-red-500 py-2 font-bold shadow-sm">LOGOUT</a>
</div>

<?php include 'common/bottom.php'; ?>
