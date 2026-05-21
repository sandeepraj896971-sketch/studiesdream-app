<?php
require_once 'common/config.php';
include 'common/header.php';
?>

<div class="px-4 py-4 max-w-md mx-auto">
    <div class="flex items-center gap-3 mb-4">
        <a href="index.php" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-700 active:scale-95 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h2 class="text-xl font-bold font-sans">Free PDFs</h2>
    </div>

    <div id="pdf-container" class="space-y-4">
        <div class="flex justify-center p-8 text-gray-400">
            <i class="fas fa-spinner fa-spin text-3xl"></i>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    fetch('api_free_pdfs.php')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('pdf-container');
            container.innerHTML = '';
            
            if (data.success && data.data.length > 0) {
                data.data.forEach(pdf => {
                    // Create card element
                    const card = document.createElement('div');
                    card.className = 'bg-white rounded-xl shadow-sm border border-gray-100 p-4 relative flex items-start gap-3 active:bg-gray-50 transition-colors';
                    
                    const imgUrl = pdf.course_image ? `uploads/courses/${pdf.course_image}` : 'assets/placeholder.png'; // Make sure styling fits
                    
                    card.innerHTML = `
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center text-red-500 shrink-0">
                            <i class="fas fa-file-pdf text-2xl"></i>
                        </div>
                        <div class="flex-1 pr-8">
                            <h3 class="font-bold text-gray-900 text-sm leading-tight mb-1">${pdf.title}</h3>
                            <p class="text-xs text-primary mb-1 border-b border-gray-50 pb-1">Course: ${pdf.course_title}</p>
                            <p class="text-[10px] text-gray-500"><i class="fas fa-folder-open text-yellow-500 mr-1"></i> ${pdf.chapter_title}</p>
                        </div>
                        <div class="absolute right-3 top-3 flex flex-col gap-2">
                            <a href="pdf_viewer.php?url=${encodeURIComponent(pdf.gdrive_link)}" class="w-8 h-8 rounded-full bg-blue-50 text-primary flex items-center justify-center shadow hover:bg-blue-100">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <button onclick="downloadPDF('${pdf.id}', '${escapeHtml(pdf.title)}', '${pdf.gdrive_link}', this)" class="w-8 h-8 rounded-full bg-gray-50 text-gray-600 flex items-center justify-center shadow border hover:bg-gray-100">
                                <i class="fas fa-download text-xs"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = `
                    <div class="text-center py-10 text-gray-500 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                        <p class="font-medium text-sm">No Free PDFs Available</p>
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error fetching PDFs:', err);
            document.getElementById('pdf-container').innerHTML = '<div class="text-center text-red-500 p-4">Error loading PDFs.</div>';
        });
});

function escapeHtml(unsafe) {
    return (unsafe || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
</script>

<?php include 'common/bottom.php'; ?>
