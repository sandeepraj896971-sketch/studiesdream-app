<?php
require_once 'common/config.php';
include 'common/header.php';

$set_res = $conn->query("SELECT * FROM settings LIMIT 1");
$settings = $set_res->fetch_assoc();

$app_set_res = $conn->query("SELECT * FROM app_settings LIMIT 1");
$app_settings = $app_set_res ? $app_set_res->fetch_assoc() : [];
?>

<div class="px-4 py-8 max-w-lg mx-auto">
    <div class="text-center mb-8">
        <i class="fas fa-globe text-primary text-5xl mb-4"></i>
        <h2 class="text-2xl font-bold text-gray-800">Website & Social Links</h2>
        <p class="text-gray-500 text-sm mt-2">Connect with us on our official platforms.</p>
    </div>

    <div class="bg-white border mb-6 shadow-sm">
        <div class="p-4 border-b flex items-center gap-4">
            <div class="w-10 h-10 bg-primary/10 text-primary flex items-center justify-center rounded-full text-xl"><i class="fas fa-envelope"></i></div>
            <div>
                <p class="text-xs text-gray-500">Email us</p>
                <p class="font-bold"><?php echo htmlspecialchars($settings['support_email'] ?? 'support@example.com'); ?></p>
            </div>
        </div>
        <div class="p-4 flex items-center gap-4">
            <div class="w-10 h-10 bg-primary/10 text-primary flex items-center justify-center rounded-full text-xl"><i class="fas fa-phone"></i></div>
            <div>
                <p class="text-xs text-gray-500">Call us</p>
                <p class="font-bold"><?php echo htmlspecialchars($settings['support_phone'] ?? '+1234567890'); ?></p>
            </div>
        </div>
    </div>

    <h3 class="font-bold mb-4 text-center text-gray-600">Connect with us</h3>
    <div class="flex justify-center gap-6">
        <?php if(!empty($app_settings['youtube_url'])): ?>
            <a href="<?php echo htmlspecialchars($app_settings['youtube_url']); ?>" target="_blank" class="w-12 h-12 bg-white border flex items-center justify-center text-red-600 text-2xl shadow-sm hover:shadow-md transition rounded-full"><i class="fab fa-youtube"></i></a>
        <?php endif; ?>
        <?php if(!empty($app_settings['whatsapp_url'])): ?>
            <a href="<?php echo htmlspecialchars($app_settings['whatsapp_url']); ?>" target="_blank" class="w-12 h-12 bg-white border flex items-center justify-center text-green-500 text-2xl shadow-sm hover:shadow-md transition rounded-full"><i class="fab fa-whatsapp"></i></a>
        <?php endif; ?>
        <?php if(!empty($app_settings['instagram_url'])): ?>
            <a href="<?php echo htmlspecialchars($app_settings['instagram_url']); ?>" target="_blank" class="w-12 h-12 bg-white border flex items-center justify-center text-pink-600 text-2xl shadow-sm hover:shadow-md transition rounded-full"><i class="fab fa-instagram"></i></a>
        <?php endif; ?>
        <?php if(!empty($app_settings['facebook_url'])): ?>
            <a href="<?php echo htmlspecialchars($app_settings['facebook_url']); ?>" target="_blank" class="w-12 h-12 bg-white border flex items-center justify-center text-blue-700 text-2xl shadow-sm hover:shadow-md transition rounded-full"><i class="fab fa-facebook"></i></a>
        <?php endif; ?>
    </div>
</div>

<?php include 'common/bottom.php'; ?>
