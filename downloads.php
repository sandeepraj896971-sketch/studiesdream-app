<?php
require_once 'common/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'common/header.php';
$user_id = $_SESSION['user_id'];
?>
<div class="px-4 py-4 pb-[80px]">
    <h2 class="text-xl font-bold mb-4 font-sans text-gray-800 border-l-4 border-primary pl-2">My Offline Downloads</h2>
    
    <div id="downloads-container" class="space-y-3">
        <div class="flex justify-center p-8 text-gray-400">
            <i class="fas fa-spinner fa-spin text-3xl"></i>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('downloads-container');
    try {
        const downloads = await window.getDownloadedPDFs();
        container.innerHTML = '';
        
        if (downloads && downloads.length > 0) {
            // Sort to show newest first
            downloads.sort((a,b) => b.timestamp - a.timestamp);
            
            downloads.forEach(d => {
                const date = new Date(d.timestamp).toLocaleDateString();
                const card = document.createElement('div');
                card.className = 'bg-white p-3 rounded-xl shadow-sm border border-gray-100 flex gap-3 items-center';
                
                card.innerHTML = `
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center flex-shrink-0 border border-red-100">
                        <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0" onclick="openOfflinePDF('${d.id}')" style="cursor:pointer;">
                        <h3 class="font-bold text-gray-800 text-[13px] leading-snug line-clamp-2">${d.title}</h3>
                        <span class="text-[10px] font-bold text-green-600 mt-1 block"><i class="fas fa-check-circle mr-1"></i> Saved Offline &bull; ${date}</span>
                    </div>
                    <button onclick="openOfflinePDF('${d.id}')" class="w-9 h-9 rounded-full bg-blue-50 text-primary flex justify-center items-center shadow-sm active:scale-95 transition-transform">
                        <i class="fas fa-play text-xs pl-0.5"></i>
                    </button>
                    <button onclick="handleDelete('${d.id}')" class="w-9 h-9 rounded-full bg-gray-50 text-gray-400 hover:text-red-500 flex justify-center items-center shadow-sm active:scale-95 transition-colors">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                `;
                container.appendChild(card);
            });
        } else {
            container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                <i class="fas fa-download text-5xl mb-3 text-gray-300"></i>
                <h2 class="text-lg font-bold text-gray-600">No Offline Files</h2>
                <p class="text-sm mt-1 text-gray-400">Your downloaded PDFs will appear here.</p>
            </div>
            `;
        }
    } catch (err) {
        console.error(err);
        container.innerHTML = '<div class="text-center text-red-500 p-4">Error loading downloads.</div>';
    }
});

async function handleDelete(id) {
    if(confirm('Are you sure you want to remove this offline file?')) {
        await window.deleteDownloadedPDF(id);
        location.reload();
    }
}
</script>
<?php include 'common/bottom.php'; ?>
