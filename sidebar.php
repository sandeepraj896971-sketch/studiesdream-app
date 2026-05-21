<?php
$stmt = $conn->prepare("SELECT * FROM settings LIMIT 1");
$stmt->execute();
$res = $stmt->get_result();
$settings = $res->fetch_assoc();
?>
<!-- Sidebar Overlay -->
<div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden transition-opacity duration-300"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white z-50 transform -translate-x-full transition-transform duration-300 shadow-xl overflow-y-auto">
    <div class="bg-primary p-6 text-white text-center">
        <?php if(isset($_SESSION['user_id'])): ?>
            <i class="fas fa-user-circle text-4xl mb-2"></i>
            <h3 class="font-bold"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h3>
        <?php else: ?>
            <i class="fas fa-user-circle text-4xl mb-2"></i>
            <h3 class="font-bold">Guest User</h3>
            <a href="login.php" class="text-sm mt-2 inline-block bg-white text-primary px-4 py-1">Login</a>
        <?php endif; ?>
    </div>
    
    <div class="flex flex-col py-4">
        <a href="index.php" class="px-6 py-3 border-b border-gray-100 hover:bg-gray-50"><i class="fas fa-home w-6 text-primary"></i> Home</a>
        <a href="mycourses.php" class="px-6 py-3 border-b border-gray-100 hover:bg-gray-50"><i class="fas fa-book w-6 text-primary"></i> My Courses</a>
        <a href="course.php" class="px-6 py-3 border-b border-gray-100 hover:bg-gray-50"><i class="fas fa-list w-6 text-primary"></i> All Courses</a>
        <a href="help.php" class="px-6 py-3 border-b border-gray-100 hover:bg-gray-50"><i class="fas fa-headset w-6 text-primary"></i> Help & Support</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="px-6 py-3 border-b border-gray-100 hover:bg-gray-50"><i class="fas fa-user w-6 text-primary"></i> Profile</a>
            <a href="logout.php" class="px-6 py-3 border-b border-gray-100 hover:bg-gray-50"><i class="fas fa-sign-out-alt w-6 text-primary"></i> Logout</a>
        <?php endif; ?>
    </div>

    <!-- Social Media Icons from DB -->
    <div class="p-6">
        <h4 class="text-gray-500 mb-3 text-sm font-semibold">Follow Us</h4>
        <div class="flex gap-4">
            <?php if(!empty($settings['youtube_link'])): ?><a href="<?php echo htmlspecialchars($settings['youtube_link']); ?>" target="_blank" class="text-red-600 text-2xl"><i class="fab fa-youtube"></i></a><?php endif; ?>
            <?php if(!empty($settings['telegram_link'])): ?><a href="<?php echo htmlspecialchars($settings['telegram_link']); ?>" target="_blank" class="text-blue-500 text-2xl"><i class="fab fa-telegram"></i></a><?php endif; ?>
            <?php if(!empty($settings['facebook_link'])): ?><a href="<?php echo htmlspecialchars($settings['facebook_link']); ?>" target="_blank" class="text-blue-700 text-2xl"><i class="fab fa-facebook"></i></a><?php endif; ?>
            <?php if(!empty($settings['instagram_link'])): ?><a href="<?php echo htmlspecialchars($settings['instagram_link']); ?>" target="_blank" class="text-pink-600 text-2xl"><i class="fab fa-instagram"></i></a><?php endif; ?>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar.classList.contains('-translate-x-full')) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
    }
</script>
