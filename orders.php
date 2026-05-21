<?php
require_once 'common/config.php';
checkAdmin();
include 'common/header.php';
?>

<div class="mb-6"><h2 class="text-2xl font-bold">Course Enrollments (Orders)</h2></div>

<div class="bg-white border shadow-sm overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[800px]">
        <thead>
            <tr class="bg-gray-100 border-b">
                <th class="p-4 text-sm">Order ID</th>
                <th class="p-4 text-sm">Student</th>
                <th class="p-4 text-sm">Course</th>
                <th class="p-4 text-sm">Amount</th>
                <th class="p-4 text-sm">Payment ID</th>
                <th class="p-4 text-sm">Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $q = "SELECT o.*, u.name as uname, c.title as cname FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  JOIN courses c ON o.course_id = c.id 
                  WHERE o.status='success' ORDER BY o.id DESC";
            $res = $conn->query($q);
            while($o = $res->fetch_assoc()):
            ?>
            <tr class="border-b hover:bg-gray-50">
                <td class="p-4 text-sm font-bold">#<?php echo $o['id']; ?></td>
                <td class="p-4 text-sm">
                    <div class="font-bold"><?php echo htmlspecialchars($o['uname']); ?></div>
                    <div class="text-xs text-gray-500">ID: <?php echo $o['user_id']; ?></div>
                </td>
                <td class="p-4 text-sm">
                    <div class="line-clamp-1 font-semibold text-primary"><?php echo htmlspecialchars($o['cname']); ?></div>
                </td>
                <td class="p-4 text-sm">
                    <?php if($o['amount'] > 0): ?>
                        <span class="font-bold text-gray-800">₹<?php echo floatval($o['amount']); ?></span>
                    <?php else: ?>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 font-bold rounded">FREE</span>
                    <?php endif; ?>
                </td>
                <td class="p-4 text-sm text-gray-500 text-xs truncate max-w-[150px]"><?php echo htmlspecialchars($o['razorpay_order_id'] ?? '-'); ?></td>
                <td class="p-4 text-sm text-gray-500 text-xs"><?php echo date('d M y h:i A', strtotime($o['created_at'])); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'common/bottom.php'; ?>
