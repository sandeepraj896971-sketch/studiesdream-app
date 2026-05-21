<?php
require_once 'common/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM ext_mock_tests WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$c_res = $stmt->get_result();
if ($c_res->num_rows == 0) {
    die("Mock Test not found");
}
$test = $c_res->fetch_assoc();

if ($test['is_free']) {
    header("Location: view_mock_test.php?id=$id");
    exit;
}

$set_res = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $set_res->fetch_assoc();
$razorpay_key = $settings['razorpay_key'] ?? '';

// Handle AJAX payment success
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $u_id = $_SESSION['user_id'];
    $amt = $test['price'];
    $payment_id = $_POST['razorpay_payment_id'];
    
    $stmt = $conn->prepare("INSERT INTO orders (user_id, mock_test_id, amount, status, razorpay_order_id) VALUES (?, ?, ?, 'success', ?)");
    $stmt->bind_param("iids", $u_id, $id, $amt, $payment_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// Simulated payment handler for testing locally
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_simulate'])) {
    $u_id = $_SESSION['user_id'];
    $amt = $test['price'];
    $txnid = "SIM".time().rand(100,999);
    $stmt = $conn->prepare("INSERT INTO orders (user_id, mock_test_id, amount, status, razorpay_order_id) VALUES (?, ?, ?, 'success', ?)");
    $stmt->bind_param("iids", $u_id, $id, $amt, $txnid);
    $stmt->execute();
    header("Location: view_mock_test.php?id=$id");
    exit;
}

include 'common/header.php';
$user = $conn->query("SELECT * FROM users WHERE id=".$_SESSION['user_id'])->fetch_assoc();
?>

<div class="px-4 py-8">
    <div class="bg-white border rounded-lg p-5 mb-6 shadow-sm">
        <h2 class="font-bold text-xl border-b pb-3 mb-4 text-gray-800">Order Summary</h2>
        <div class="flex gap-4 mb-4 items-center">
            <div class="w-28 aspect-video bg-gray-200 flex items-center justify-center rounded border"><i class="fas fa-file-alt text-gray-400 text-2xl"></i></div>
            <div class="flex-1">
                <p class="font-bold text-gray-800 text-sm md:text-base line-clamp-2"><?php echo htmlspecialchars($test['title']); ?></p>
                <div class="text-xs text-gray-500 mt-1 uppercase font-bold text-primary tracking-wide">Paid Mock Test</div>
            </div>
        </div>
        
        <div class="bg-gray-50 p-4 rounded-lg space-y-2 mb-6 border border-gray-100">
            <div class="flex justify-between text-sm text-gray-600">
                <span>Original Price</span>
                <span>₹<?php echo floatval($test['mrp']); ?></span>
            </div>
            <div class="flex justify-between text-sm text-green-600 font-medium border-b pb-2 border-gray-200">
                <span>Discount</span>
                <span>- ₹<?php echo floatval($test['mrp'] - $test['price']); ?></span>
            </div>
            <div class="flex justify-between font-bold text-lg text-gray-800 pt-1">
                <span>Total Amount</span>
                <span>₹<?php echo floatval($test['price']); ?></span>
            </div>
        </div>
        
        <?php if(!empty($razorpay_key)): ?>
            <button id="rzp-button1" class="w-full bg-[#0284C7] text-white py-4 rounded-lg font-bold text-[15px] uppercase shadow-md hover:bg-[#026cb3] active:scale-[0.98] transition-transform"><i class="fas fa-lock mr-2"></i> Pay ₹<?php echo floatval($test['price']); ?> Securely</button>
        <?php else: ?>
            <div class="p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg text-sm mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i> Razorpay keys are not configured in Admin Settings. Showing simulate button for testing.
            </div>
            <form method="POST">
                <input type="hidden" name="pay_simulate" value="1">
                <button type="submit" class="w-full bg-green-500 text-white py-4 rounded-lg font-bold text-[15px] uppercase shadow-md active:scale-[0.98] transition-transform"><i class="fas fa-check-circle mr-2"></i> Simulate Payment Success</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>nav.fixed.bottom-0 { display: none; }</style>

<?php if(!empty($razorpay_key)): ?>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo htmlspecialchars($razorpay_key); ?>",
    "amount": "<?php echo floatval($test['price']) * 100; ?>", 
    "currency": "INR",
    "name": "<?php echo htmlspecialchars($settings['app_name']); ?>",
    "description": "Purchase: <?php echo htmlspecialchars($test['title']); ?>",
    "image": "https://example.com/your_logo",
    "handler": function (response){
        fetch('buy_mock_test.php?id=<?php echo $id; ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'razorpay_payment_id=' + response.razorpay_payment_id
        }).then(res => res.json()).then(data => {
            if(data.success) {
                window.location.href = 'view_mock_test.php?id=<?php echo $id; ?>';
            } else {
                alert('Payment verification failed.');
            }
        }).catch(e => {
            alert('Something went wrong during payment processing.');
        });
    },
    "prefill": {
        "name": "<?php echo htmlspecialchars($user['name']); ?>",
        "email": "<?php echo htmlspecialchars($user['email']); ?>",
        "contact": "<?php echo htmlspecialchars($user['phone']); ?>"
    },
    "theme": {
        "color": "#0284C7"
    }
};
var rzp1 = new Razorpay(options);
document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>
<?php endif; ?>

<?php include 'common/bottom.php'; ?>
