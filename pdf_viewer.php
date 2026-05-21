<?php
// pdf_viewer.php
require_once 'common/config.php';
$url = isset($_GET['url']) ? $_GET['url'] : '';
$offline = isset($_GET['offline']) ? true : false;
if (!$url) die('No PDF URL provided');

include 'common/header.php';

// Determine if we need to proxy the URL or it's a blob
$is_blob = (strpos($url, 'blob:') === 0);
if ($is_blob || $offline) {
    $pdf_source = $url;
} else {
    // Proxy through fetch_pdf.php to avoid exposing raw links and CORS issues
    $pdf_source = 'fetch_pdf.php?url=' . urlencode($url);
}
?>
<div class="flex flex-col h-[calc(100vh-60px)] pb-[65px] bg-gray-100">
    <div class="px-4 py-3 bg-white border-b flex justify-between items-center shadow-sm z-10">
        <button onclick="history.back()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-700 active:scale-95 transition-transform"><i class="fas fa-arrow-left"></i></button>
        <span class="font-bold text-sm truncate px-3">Secure Document Viewer</span>
        <div class="flex gap-2">
            <button id="prev-page" class="w-8 h-8 flex items-center justify-center rounded bg-gray-100 text-gray-700 active:bg-gray-200 disabled:opacity-50"><i class="fas fa-chevron-up"></i></button>
            <button id="next-page" class="w-8 h-8 flex items-center justify-center rounded bg-gray-100 text-gray-700 active:bg-gray-200 disabled:opacity-50"><i class="fas fa-chevron-down"></i></button>
        </div>
    </div>
    
    <div class="flex bg-white text-xs border-b px-4 py-2 justify-center gap-2 items-center text-gray-600 shadow-sm z-10">
        <span>Page: <span id="page-num" class="font-bold">0</span> / <span id="page-count" class="font-bold">0</span></span>
        <span class="mx-2 text-gray-300">|</span>
        <button id="zoom-out" class="w-6 h-6 flex justify-center items-center bg-gray-100 rounded hover:bg-gray-200"><i class="fas fa-search-minus"></i></button>
        <button id="zoom-in" class="w-6 h-6 flex justify-center items-center bg-gray-100 rounded hover:bg-gray-200"><i class="fas fa-search-plus"></i></button>
    </div>

    <!-- Viewer Container -->
    <div id="pdf-container" class="flex-1 overflow-auto flex justify-center p-4 relative" style="user-select: none;">
        <canvas id="pdf-render" class="shadow-md bg-white"></canvas>
        <!-- Loading overlay -->
        <div id="loading-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100 z-20">
            <i class="fas fa-spinner fa-spin text-4xl text-primary mb-3"></i>
            <span class="text-sm font-bold text-gray-600">Loading Secure File...</span>
        </div>
    </div>
</div>

<!-- Prevent Context Menu (Right Click) -->
<script>
    document.addEventListener('contextmenu', event => event.preventDefault());
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    const pdfSource = "<?php echo $pdf_source; ?>";
    
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let pdfDoc = null,
        pageNum = 1,
        pageIsRendering = false,
        pageNumIsPending = null,
        scale = 1.0;

    const canvas = document.getElementById('pdf-render'),
          ctx = canvas.getContext('2d');

    // Prevent saving image from canvas natively
    canvas.addEventListener('contextmenu', e => e.preventDefault());

    function renderPage(num) {
        pageIsRendering = true;

        pdfDoc.getPage(num).then(page => {
            // Check container width for mobile responsiveness
            const container = document.getElementById('pdf-container');
            const containerWidth = container.clientWidth - 32; // 32px padding
            const unscaledViewport = page.getViewport({ scale: 1.0 });
            
            // Adjust scale to fit width based on default zoom level 1.0
            let renderScale = scale;
            if (scale === 1.0 && unscaledViewport.width > containerWidth && containerWidth > 0) {
                renderScale = containerWidth / unscaledViewport.width;
            }

            const viewport = page.getViewport({ scale: renderScale });
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderCtx = {
                canvasContext: ctx,
                viewport: viewport
            };

            page.render(renderCtx).promise.then(() => {
                pageIsRendering = false;

                if (pageNumIsPending !== null) {
                    renderPage(pageNumIsPending);
                    pageNumIsPending = null;
                }
            });

            document.getElementById('page-num').textContent = num;
        });
    }

    function queueRenderPage(num) {
        if (pageIsRendering) {
            pageNumIsPending = num;
        } else {
            renderPage(num);
        }
    }

    function onPrevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }

    function onNextPage() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    document.getElementById('prev-page').addEventListener('click', onPrevPage);
    document.getElementById('next-page').addEventListener('click', onNextPage);
    
    document.getElementById('zoom-in').addEventListener('click', () => {
        scale += 0.2;
        queueRenderPage(pageNum);
    });
    
    document.getElementById('zoom-out').addEventListener('click', () => {
        if (scale <= 0.4) return;
        scale -= 0.2;
        queueRenderPage(pageNum);
    });

    // Load Document
    pdfjsLib.getDocument(pdfSource).promise.then(pdfDoc_ => {
        pdfDoc = pdfDoc_;
        document.getElementById('page-count').textContent = pdfDoc.numPages;
        document.getElementById('loading-overlay').style.display = 'none';
        
        renderPage(pageNum);
    }).catch(err => {
        console.error(err);
        const overlay = document.getElementById('loading-overlay');
        overlay.innerHTML = '<div class="text-red-500 font-bold p-4 text-center mt-[30vh]">Error loading document.<br><span class="text-xs text-gray-500 font-normal">If this is a Drive link, make sure it is publicly shared.</span></div>';
    });
</script>

<?php include 'common/bottom.php'; ?>
