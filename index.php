<?php
require_once 'common/config.php';
checkAdmin();

$stats = [
    'Users' => $conn->query("SELECT count(*) as c FROM users")->fetch_assoc()['c'],
    'Courses' => $conn->query("SELECT count(*) as c FROM courses")->fetch_assoc()['c'],
    'Orders' => $conn->query("SELECT count(*) as c FROM orders WHERE status='success'")->fetch_assoc()['c'],
    'Revenue' => "₹" . ($conn->query("SELECT sum(amount) as s FROM orders WHERE status='success'")->fetch_assoc()['s'] ?? 0)
];

include 'common/header.php';
?>

<h2 class="text-2xl font-bold mb-6 text-gray-800">Dashboard</h2>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <!-- Stat Cards -->
    <div class="bg-white p-6 border-l-4 border-primary shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500 font-bold uppercase">Total Users</p>
        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['Users']; ?></p>
    </div>
    <div class="bg-white p-6 border-l-4 border-green-500 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500 font-bold uppercase">Active Courses</p>
        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['Courses']; ?></p>
    </div>
    <div class="bg-white p-6 border-l-4 border-orange-500 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500 font-bold uppercase">Enrollments</p>
        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['Orders']; ?></p>
    </div>
    <div class="bg-white p-6 border-l-4 border-purple-500 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500 font-bold uppercase">Total Revenue</p>
        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $stats['Revenue']; ?></p>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white p-6 border shadow-sm">
        <h3 class="font-bold text-lg mb-4">Quick Links</h3>
        <div class="grid grid-cols-2 gap-4">
            <a href="course.php" class="p-3 bg-gray-50 border hover:bg-primary hover:text-white transition text-center font-semibold text-sm cursor-pointer"><i class="fas fa-plus mb-2 block text-xl"></i> Add Course</a>
            <a href="chapter.php" class="p-3 bg-gray-50 border hover:bg-primary hover:text-white transition text-center font-semibold text-sm cursor-pointer"><i class="fas fa-list mb-2 block text-xl"></i> Manage Chapters</a>
            <a href="video.php" class="p-3 bg-gray-50 border hover:bg-primary hover:text-white transition text-center font-semibold text-sm cursor-pointer"><i class="fas fa-video mb-2 block text-xl"></i> Add Video</a>
            <a href="users.php" class="p-3 bg-gray-50 border hover:bg-primary hover:text-white transition text-center font-semibold text-sm cursor-pointer"><i class="fas fa-users mb-2 block text-xl"></i> View Users</a>
        </div>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
