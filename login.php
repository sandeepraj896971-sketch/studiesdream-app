<?php
require_once 'common/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];
    $res = $conn->query("SELECT * FROM admin WHERE username='$user' LIMIT 1");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            header("Location: index.php");
            exit;
        }
    }
    $error = "Invalid credentials";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#0284C7'}}}}</script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 border shadow-lg max-w-sm w-full">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Admin Panel</h2>
            <p class="text-gray-500 text-sm">Sign in to continue</p>
        </div>
        <?php if(isset($error)): ?>
            <div class="bg-red-100 text-red-600 p-3 mb-4 text-sm"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <input type="text" name="username" placeholder="Username" required class="w-full p-3 bg-gray-50 border outline-none focus:border-primary">
            </div>
            <div class="mb-6">
                <input type="password" name="password" placeholder="Password" required class="w-full p-3 bg-gray-50 border outline-none focus:border-primary">
            </div>
            <button type="submit" class="w-full bg-primary text-white py-3 font-bold">LOGIN</button>
        </form>
    </div>
</body>
</html>
