<?php
require_once 'common/config.php';
// checkAdmin() assuming it's available or handled in header.php
include 'common/header.php';

// Fetch current settings
$res = $conn->query("SELECT * FROM app_settings LIMIT 1");
$settings = $res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $app_name = $conn->real_escape_string($_POST['app_name']);
    $header_color = $conn->real_escape_string($_POST['header_color']);
    $footer_color = $conn->real_escape_string($_POST['footer_color']);
    $facebook_url = $conn->real_escape_string($_POST['facebook_url']);
    $youtube_url = $conn->real_escape_string($_POST['youtube_url']);
    $instagram_url = $conn->real_escape_string($_POST['instagram_url']);
    $whatsapp_url = $conn->real_escape_string($_POST['whatsapp_url']);

    $logo_url = $settings['logo_url'] ?? '';
    
    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] == 0) {
        if (!is_dir('../uploads/logos')) {
            mkdir('../uploads/logos', 0777, true);
        }
        $ext = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
        $logo_url = 'logo_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['logo_image']['tmp_name'], '../uploads/logos/' . $logo_url);
    }

    if ($settings) {
        $conn->query("UPDATE app_settings SET 
            app_name='$app_name', 
            logo_url='$logo_url', 
            header_color='$header_color', 
            footer_color='$footer_color', 
            facebook_url='$facebook_url', 
            youtube_url='$youtube_url', 
            instagram_url='$instagram_url', 
            whatsapp_url='$whatsapp_url' 
            WHERE id=" . $settings['id']);
    } else {
        $conn->query("INSERT INTO app_settings (app_name, logo_url, header_color, footer_color, facebook_url, youtube_url, instagram_url, whatsapp_url) 
            VALUES ('$app_name', '$logo_url', '$header_color', '$footer_color', '$facebook_url', '$youtube_url', '$instagram_url', '$whatsapp_url')");
    }

    echo "<script>alert('Settings updated successfully!'); window.location.href='app_settings.php';</script>";
    exit;
}
?>

<div class="px-4 py-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800 border-l-4 border-primary pl-3">App Configuration Panel</h1>

    <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 max-w-2xl">
        
        <h2 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2">Branding Settings</h2>
        
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1">App Name</label>
            <input type="text" name="app_name" value="<?php echo htmlspecialchars($settings['app_name'] ?? ''); ?>" required class="w-full border rounded p-2 text-sm outline-none focus:border-primary">
        </div>

        <div class="mb-4 flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-semibold mb-1">App Logo</label>
                <input type="file" name="logo_image" accept="image/*" class="w-full border rounded p-1 text-sm outline-none focus:border-primary">
            </div>
            <?php if(!empty($settings['logo_url'])): ?>
            <div class="w-12 h-12 rounded-full overflow-hidden border">
                <img src="../uploads/logos/<?php echo htmlspecialchars($settings['logo_url']); ?>" class="w-full h-full object-cover">
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold mb-1">Header Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="header_color" value="<?php echo htmlspecialchars($settings['header_color'] ?? '#ffffff'); ?>" class="w-10 h-10 border rounded cursor-pointer p-0">
                    <span class="text-gray-500 text-xs">HEX Color</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Footer Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="footer_color" value="<?php echo htmlspecialchars($settings['footer_color'] ?? '#1f2937'); ?>" class="w-10 h-10 border rounded cursor-pointer p-0">
                    <span class="text-gray-500 text-xs">HEX Color</span>
                </div>
            </div>
        </div>

        <h2 class="text-lg font-bold text-gray-700 mb-4 border-b pb-2 mt-8">Social Media & Links</h2>

        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1"><i class="fab fa-facebook text-blue-600 mr-1"></i> Facebook URL</label>
            <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($settings['facebook_url'] ?? ''); ?>" class="w-full border rounded p-2 text-sm outline-none focus:border-primary" placeholder="https://facebook.com/yourpage">
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1"><i class="fab fa-youtube text-red-600 mr-1"></i> YouTube URL</label>
            <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($settings['youtube_url'] ?? ''); ?>" class="w-full border rounded p-2 text-sm outline-none focus:border-primary" placeholder="https://youtube.com/c/yourchannel">
        </div>
        
        <div class="mb-4">
            <label class="block text-sm font-semibold mb-1"><i class="fab fa-instagram text-pink-500 mr-1"></i> Instagram URL</label>
            <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($settings['instagram_url'] ?? ''); ?>" class="w-full border rounded p-2 text-sm outline-none focus:border-primary" placeholder="https://instagram.com/yourpage">
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-semibold mb-1"><i class="fab fa-whatsapp text-green-500 mr-1"></i> WhatsApp URL</label>
            <input type="url" name="whatsapp_url" value="<?php echo htmlspecialchars($settings['whatsapp_url'] ?? ''); ?>" class="w-full border rounded p-2 text-sm outline-none focus:border-primary" placeholder="https://wa.me/1234567890">
        </div>

        <button type="submit" name="update_settings" class="bg-primary text-white px-6 py-2 rounded-lg font-bold w-full hover:bg-blue-600 transition-colors shadow">Save Configuration</button>

    </form>
</div>

</body>
</html>
