<?php
require_once 'common/config.php';
checkAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_name = $conn->real_escape_string($_POST['app_name']);
    $r_key = $conn->real_escape_string($_POST['razorpay_key']);
    $r_sec = $conn->real_escape_string($_POST['razorpay_secret']);
    $email = $conn->real_escape_string($_POST['support_email']);
    $phone = $conn->real_escape_string($_POST['support_phone']);
    
    $yt = $conn->real_escape_string($_POST['youtube_link']);
    $tg = $conn->real_escape_string($_POST['telegram_link']);
    $ig = $conn->real_escape_string($_POST['instagram_link']);
    $fb = $conn->real_escape_string($_POST['facebook_link']);

    // Check if row exists
    $chk = $conn->query("SELECT id FROM settings LIMIT 1");
    if($chk->num_rows > 0) {
        $conn->query("UPDATE settings SET 
            app_name='$app_name', razorpay_key='$r_key', razorpay_secret='$r_sec', 
            support_email='$email', support_phone='$phone', 
            youtube_link='$yt', telegram_link='$tg', instagram_link='$ig', facebook_link='$fb'");
    } else {
        $conn->query("INSERT INTO settings (app_name, razorpay_key, razorpay_secret, support_email, support_phone, youtube_link, telegram_link, instagram_link, facebook_link) 
            VALUES ('$app_name', '$r_key', '$r_sec', '$email', '$phone', '$yt', '$tg', '$ig', '$fb')");
    }
    $msg = "Settings updated successfully.";
}

$set = $conn->query("SELECT * FROM settings LIMIT 1")->fetch_assoc();
include 'common/header.php';
?>

<div class="mb-6"><h2 class="text-2xl font-bold">System Settings</h2></div>

<?php if(isset($msg)): ?>
    <div class="bg-green-100 text-green-700 p-3 mb-4 rounded text-sm"><?php echo $msg; ?></div>
<?php endif; ?>

<form method="POST" class="bg-white p-6 border shadow-sm max-w-4xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- App Details -->
        <div>
            <h3 class="font-bold border-b pb-2 mb-4 text-gray-700"><i class="fas fa-mobile-alt mr-2"></i> App & Contact Info</h3>
            
            <div class="mb-4">
                <label class="block text-sm mb-1 text-gray-500">App Name</label>
                <input type="text" name="app_name" value="<?php echo htmlspecialchars($set['app_name']??''); ?>" required class="w-full p-2 border bg-gray-50 outline-none focus:border-primary">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm mb-1 text-gray-500">Support Email</label>
                <input type="email" name="support_email" value="<?php echo htmlspecialchars($set['support_email']??''); ?>" class="w-full p-2 border bg-gray-50 outline-none focus:border-primary">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm mb-1 text-gray-500">Support Phone</label>
                <input type="text" name="support_phone" value="<?php echo htmlspecialchars($set['support_phone']??''); ?>" class="w-full p-2 border bg-gray-50 outline-none focus:border-primary">
            </div>
            
            <h3 class="font-bold border-b pb-2 mb-4 mt-8 text-gray-700"><i class="fas fa-wallet mr-2"></i> Payment Gateway (Razorpay)</h3>
            
            <div class="mb-4">
                <label class="block text-sm mb-1 text-gray-500">Razorpay API Key</label>
                <input type="text" name="razorpay_key" value="<?php echo htmlspecialchars($set['razorpay_key']??''); ?>" class="w-full p-2 border bg-gray-50 outline-none">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm mb-1 text-gray-500">Razorpay Secret</label>
                <input type="password" name="razorpay_secret" value="<?php echo htmlspecialchars($set['razorpay_secret']??''); ?>" class="w-full p-2 border bg-gray-50 outline-none">
            </div>
        </div>

        <!-- Social Media -->
        <div>
            <h3 class="font-bold border-b pb-2 mb-4 text-gray-700"><i class="fas fa-share-alt mr-2"></i> Social Media Links</h3>
            
            <div class="mb-4 relative">
                <i class="fab fa-youtube absolute left-3 top-3 text-red-600 text-lg"></i>
                <input type="text" name="youtube_link" value="<?php echo htmlspecialchars($set['youtube_link']??''); ?>" placeholder="YouTube Channel Link" class="w-full p-2 pl-10 border bg-gray-50 outline-none">
            </div>
            
            <div class="mb-4 relative">
                <i class="fab fa-telegram absolute left-3 top-3 text-blue-500 text-lg"></i>
                <input type="text" name="telegram_link" value="<?php echo htmlspecialchars($set['telegram_link']??''); ?>" placeholder="Telegram Link" class="w-full p-2 pl-10 border bg-gray-50 outline-none">
            </div>
            
            <div class="mb-4 relative">
                <i class="fab fa-instagram absolute left-3 top-3 text-pink-600 text-lg"></i>
                <input type="text" name="instagram_link" value="<?php echo htmlspecialchars($set['instagram_link']??''); ?>" placeholder="Instagram Link" class="w-full p-2 pl-10 border bg-gray-50 outline-none">
            </div>
            
            <div class="mb-4 relative">
                <i class="fab fa-facebook absolute left-3 top-3 text-blue-700 text-lg"></i>
                <input type="text" name="facebook_link" value="<?php echo htmlspecialchars($set['facebook_link']??''); ?>" placeholder="Facebook Page Link" class="w-full p-2 pl-10 border bg-gray-50 outline-none">
            </div>
        </div>
        
    </div>
    
    <div class="mt-6 border-t pt-4">
        <button type="submit" class="bg-primary text-white px-8 py-3 font-bold hover:bg-blue-600">SAVE ALL SETTINGS</button>
    </div>
</form>

<?php include 'common/bottom.php'; ?>
