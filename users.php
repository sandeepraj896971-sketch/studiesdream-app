<?php
require_once 'common/config.php';
checkAdmin();
include 'common/header.php';
?>

<div class="mb-6"><h2 class="text-2xl font-bold">Registered Users</h2></div>

<div class="bg-white border shadow-sm overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[600px]">
        <thead>
            <tr class="bg-gray-100 border-b">
                <th class="p-4 text-sm font-bold text-gray-600">ID</th>
                <th class="p-4 text-sm font-bold text-gray-600">Name</th>
                <th class="p-4 text-sm font-bold text-gray-600">Email</th>
                <th class="p-4 text-sm font-bold text-gray-600">Phone</th>
                <th class="p-4 text-sm font-bold text-gray-600">Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $users = $conn->query("SELECT * FROM users ORDER BY id DESC");
            while($u = $users->fetch_assoc()):
            ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-4 text-sm">#<?php echo $u['id']; ?></td>
                <td class="p-4 font-bold text-sm"><?php echo htmlspecialchars($u['name']); ?></td>
                <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($u['email']); ?></td>
                <td class="p-4 text-sm text-gray-600"><?php echo htmlspecialchars($u['phone']); ?></td>
                <td class="p-4 text-sm text-gray-600"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>
