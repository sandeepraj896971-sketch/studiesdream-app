<?php
require_once 'common/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$c_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$u_id = $_SESSION['user_id'];

// verify purchase
$chk = $conn->query("SELECT id FROM orders WHERE user_id=$u_id AND course_id=$c_id AND status='success'");
if($chk->num_rows == 0) {
    die("Not enrolled in this course.");
}

$course = $conn->query("SELECT * FROM courses WHERE id=$c_id")->fetch_assoc();

include 'common/header.php';
?>

<div class="pb-6">
    <!-- Top Player Area Placeholder -->
    <div id="player-container" class="w-full aspect-video bg-black flex items-center justify-center sticky top-[50px] z-40 shadow-md">
        <div class="text-gray-400 text-center px-4">
            <i class="fas fa-play-circle text-4xl mb-2 text-white/50"></i>
            <p class="text-sm">Select a video or note from chapters to begin.</p>
        </div>
    </div>

    <!-- Course Title -->
    <div class="p-4 bg-white border-b mb-2 sticky top-[calc(50px+56.25vw)] z-30 shadow-sm md:top-[calc(50px+360px)]"> <!-- Approximate top for sticky tabs if needed -->
        <h2 class="font-bold text-gray-800 text-lg line-clamp-1"><?php echo htmlspecialchars($course['title']); ?></h2>
    </div>

    <!-- Chapters Accordion -->
    <div class="px-2">
        <?php
        $chapters = $conn->query("SELECT * FROM chapters WHERE course_id=$c_id ORDER BY id ASC");
        if($chapters->num_rows > 0): while($ch = $chapters->fetch_assoc()):
            $ch_id = $ch['id'];
            $videos = $conn->query("SELECT * FROM videos WHERE chapter_id=$ch_id");
            $notes = $conn->query("SELECT * FROM notes WHERE chapter_id=$ch_id");
        ?>
        <div class="mb-2 bg-white border">
            <button class="w-full p-3 flex justify-between items-center font-bold text-sm bg-gray-50 hover:bg-gray-100 outline-none focus:outline-none" onclick="toggleChapter(this)">
                <span><?php echo htmlspecialchars($ch['title']); ?></span>
                <i class="fas fa-chevron-down transition-transform duration-300"></i>
            </button>
            <div class="chapter-content hidden flex-col">
                <?php while($v = $videos->fetch_assoc()): 
                        // Create a safe string for javascript argument
                        $safe_url = json_encode($v['video_url'], JSON_HEX_APOS | JSON_HEX_QUOT);
                ?>
                    <div class="p-3 border-t border-gray-100 flex items-center gap-3 cursor-pointer hover:bg-gray-50" onclick='playMedia("video", "<?php echo $v['video_type']; ?>", <?php echo $safe_url; ?>)'>
                        <i class="fas fa-play-circle text-primary text-xl w-6"></i>
                        <span class="text-sm flex-1 truncate"><?php echo htmlspecialchars($v['title']); ?></span>
                    </div>
                <?php endwhile; ?>
                <?php while($n = $notes->fetch_assoc()): ?>
                    <div class="p-3 border-t border-gray-100 flex items-center gap-3 hover:bg-gray-50">
                        <div class="flex-1 flex items-center gap-3 cursor-pointer" onclick="window.location.href='pdf_viewer.php?url=<?php echo urlencode($n['gdrive_link']); ?>'">
                            <i class="fas fa-file-pdf text-red-500 text-xl w-6"></i>
                            <span class="text-sm truncate"><?php echo htmlspecialchars($n['title']); ?></span>
                        </div>
                        <button onclick="downloadPDF('<?php echo $n['id']; ?>', '<?php echo htmlspecialchars($n['title'], ENT_QUOTES); ?>', '<?php echo $n['gdrive_link']; ?>', this)" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:text-primary flex justify-center items-center transition-colors">
                            <i class="fas fa-download text-xs"></i>
                        </button>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endwhile; else: ?>
            <div class="p-4 text-center text-gray-400">Chapters coming soon.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleChapter(btn) {
        const content = btn.nextElementSibling;
        const icon = btn.querySelector('i');
        if(content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            content.classList.add('flex');
            icon.style.transform = 'rotate(180deg)';
        } else {
            content.classList.add('hidden');
            content.classList.remove('flex');
            icon.style.transform = 'rotate(0deg)';
        }
    }

    function playMedia(type, videoType, url) {
        const container = document.getElementById('player-container');
        if(type === 'video') {
            if(videoType === 'youtube') {
                let videoId = '';
                try {
                    if (url.includes('youtu.be/')) {
                        videoId = url.split('youtu.be/')[1].split('?')[0];
                    } else if (url.includes('youtube.com/watch')) {
                        const urlParams = new URL(url).searchParams;
                        videoId = urlParams.get('v');
                    } else if (url.includes('youtube.com/embed/')) {
                        videoId = url.split('youtube.com/embed/')[1].split('?')[0];
                    } else {
                        videoId = url; // fallback
                    }
                } catch(e) {}
                
                let embedUrl = videoId ? `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1` : url;
                container.innerHTML = `<iframe class="w-full h-full" src="${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`;
            } else if(videoType === 'mp4') {
                container.innerHTML = `<video class="w-full h-full bg-black block" controls controlsList="nodownload" oncontextmenu="return false;"><source src="uploads/videos/${encodeURIComponent(url)}" type="video/mp4"></video>`;
            } else if(videoType === 'file') {
                container.innerHTML = `<video class="w-full h-full bg-black block" controls controlsList="nodownload" oncontextmenu="return false;"><source src="uploads/live/${encodeURIComponent(url)}" type="video/mp4"></video>`;
            }
        }
    }
</script>

<style>nav.fixed.bottom-0 { display: none; }</style>
<?php include 'common/bottom.php'; ?>
