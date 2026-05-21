<?php
session_start();
require_once 'common/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'common/header.php';
$user_id = $_SESSION['user_id'];

// Get active live class
$res = $conn->query("SELECT * FROM live_classes WHERE is_active=1 ORDER BY id DESC LIMIT 1");
$active_live = null;
if ($res && $res->num_rows > 0) {
    $active_live = $res->fetch_assoc();
}

$has_access = false;
if ($active_live) {
    if ($active_live['is_free'] || $active_live['course_id'] == 0) {
        $has_access = true;
    } else {
        $course_id = $active_live['course_id'];
        $check = $conn->query("SELECT * FROM orders WHERE user_id=$user_id AND course_id=$course_id AND status='success'");
        if ($check && $check->num_rows > 0) {
            $has_access = true;
        }
    }
}
?>

<div class="px-4 py-4 max-w-lg mx-auto">
    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
        <i class="fas fa-satellite-dish text-red-500"></i> Live Classes
    </h2>

    <?php 
    if ($active_live): 
        if (!$has_access): 
    ?>
        <div class="bg-gray-900 rounded-lg overflow-hidden shadow-lg mb-4 aspect-video relative flex flex-col items-center justify-center p-6 text-center text-white">
            <i class="fas fa-lock text-4xl mb-3 text-red-500"></i>
            <h3 class="font-bold text-lg mb-1">Class is Locked</h3>
            <p class="text-sm text-gray-300 mb-4">This live class is restricted to enrolled students only.</p>
            <a href="course.php" class="bg-primary hover:bg-blue-600 px-6 py-2 rounded-full font-bold shadow transition text-sm">View Course</a>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-start gap-3 relative overflow-hidden opacity-70">
            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center shrink-0 overflow-hidden">
                <?php if (!empty($active_live['thumbnail'])): ?>
                    <img src="uploads/thumbnails/<?php echo htmlspecialchars($active_live['thumbnail']); ?>" class="w-full h-full object-cover grayscale">
                <?php else: ?>
                    <i class="fas fa-broadcast-tower"></i>
                <?php endif; ?>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-red-500 text-white text-[10px] uppercase font-bold px-1.5 py-0.5 rounded">LIVE NOW</span>
                </div>
                <h3 class="font-bold text-gray-800 leading-tight"><?php echo htmlspecialchars($active_live['title']); ?></h3>
            </div>
        </div>
    <?php else:
        $type = $active_live['video_type'] ?? 'youtube';
    ?>
    
    <div class="bg-black rounded-lg overflow-hidden shadow-lg mb-4 aspect-video relative">
        <?php if($type === 'youtube'): 
            $url = $active_live['youtube_link'];
            $videoId = '';
            if (strpos($url, 'youtu.be/') !== false) {
                $videoId = explode('?', explode('youtu.be/', $url)[1])[0];
            } elseif (strpos($url, 'youtube.com/watch') !== false) {
                parse_str(parse_url($url, PHP_URL_QUERY), $params);
                if (isset($params['v'])) $videoId = $params['v'];
            } elseif (strpos($url, 'youtube.com/embed/') !== false) {
                $videoId = explode('?', explode('youtube.com/embed/', $url)[1])[0];
            } else {
                $videoId = $url; // assume it's just the ID
            }
        ?>
            <?php if ($videoId): ?>
                <iframe class="w-full h-full absolute inset-0" src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>?autoplay=1&mute=0&rel=0&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-white">Invalid YouTube Link</div>
            <?php endif; ?>
        <?php else: ?>
            <?php if($active_live['video_file']): ?>
                <video class="w-full h-full" controls playsinline autoplay controlsList="nodownload">
                    <source src="uploads/live/<?php echo htmlspecialchars($active_live['video_file']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-white">No video file uploaded</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-start gap-3 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>
        <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center shrink-0 overflow-hidden">
            <?php if (!empty($active_live['thumbnail'])): ?>
                <img src="uploads/thumbnails/<?php echo htmlspecialchars($active_live['thumbnail']); ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <i class="fas fa-broadcast-tower"></i>
            <?php endif; ?>
        </div>
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <span class="bg-red-500 text-white text-[10px] uppercase font-bold px-1.5 py-0.5 rounded animate-pulse">LIVE NOW</span>
            </div>
            <h3 class="font-bold text-gray-800 leading-tight"><?php echo htmlspecialchars($active_live['title']); ?></h3>
        </div>
    </div>
    
    <?php endif; else: ?>
    <div class="flex flex-col items-center justify-center py-20 text-gray-500 bg-white rounded-xl border border-dashed border-gray-300">
        <i class="fas fa-video-slash text-5xl mb-3 text-gray-300"></i>
        <h2 class="text-lg font-bold text-gray-600">No Active Live Classes</h2>
        <p class="text-sm mt-1 text-gray-400 text-center px-4">There are no live classes streaming right now. Please check back later.</p>
    </div>
    <?php endif; ?>
</div>

<?php include 'common/bottom.php'; ?>
