    </div> <!-- end main-content -->
    
    <!-- Bottom Navigation -->
    <?php
    $footer_color = $app_settings['footer_color'] ?? '#ffffff';
    ?>
    <nav style="background-color: <?php echo htmlspecialchars($footer_color); ?>;" class="fixed bottom-0 left-0 right-0 h-[65px] border-t border-gray-100 flex justify-around items-center text-gray-500 z-40 text-[11px] shadow-[0_-2px_5px_rgba(0,0,0,0.02)] pb-1">
        <a href="index.php" class="flex flex-col items-center justify-end h-full w-[25%] <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-gray-800' : 'hover:bg-gray-50'; ?>">
            <i class="fas fa-home text-[22px] mb-1.5 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-gray-800' : 'text-gray-500'; ?>"></i>
            <span class="font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'font-bold' : ''; ?>">Home</span>
        </a>
        <a href="mycourses.php" class="flex flex-col items-center justify-end h-full w-[25%] <?php echo basename($_SERVER['PHP_SELF']) == 'mycourses.php' ? 'text-gray-800' : 'hover:bg-gray-50'; ?>">
            <i class="fas fa-swatchbook text-[20px] mb-1.5 <?php echo basename($_SERVER['PHP_SELF']) == 'mycourses.php' ? 'text-gray-800' : 'text-gray-500'; ?>"></i>
            <span class="font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'mycourses.php' ? 'font-bold' : ''; ?>">My Courses</span>
        </a>
        <a href="downloads.php" class="flex flex-col items-center justify-end h-full w-[25%] <?php echo basename($_SERVER['PHP_SELF']) == 'downloads.php' ? 'text-gray-800' : 'hover:bg-gray-50'; ?>">
            <div class="mb-1 bg-gray-500 text-white rounded-[4px] p-0.5 w-[22px] h-[22px] flex items-center justify-center <?php echo basename($_SERVER['PHP_SELF']) == 'downloads.php' ? 'bg-gray-800' : ''; ?>">
                <i class="fas fa-arrow-down text-[12px]"></i>
            </div>
            <span class="font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'downloads.php' ? 'font-bold' : ''; ?>">Downloads</span>
        </a>
        <a href="live.php" class="flex flex-col items-center justify-end h-full w-[25%] <?php echo basename($_SERVER['PHP_SELF']) == 'live.php' ? 'text-gray-800' : 'hover:bg-gray-50'; ?>">
            <div class="relative mb-1.5">
                <i class="fas fa-video text-[20px] <?php echo basename($_SERVER['PHP_SELF']) == 'live.php' ? 'text-gray-800' : 'text-gray-500'; ?>"></i>
                <!-- Simulated broadcast waves -->
                <i class="fas fa-wifi text-[10px] absolute -top-[7px] left-1/2 transform -translate-x-1/2 <?php echo basename($_SERVER['PHP_SELF']) == 'live.php' ? 'text-gray-800' : 'text-gray-500'; ?>"></i>
            </div>
            <span class="font-medium <?php echo basename($_SERVER['PHP_SELF']) == 'live.php' ? 'font-bold' : ''; ?>">Live Classes</span>
        </a>
    </nav>
    <script>
        // Disable pinch zoom
        document.addEventListener('touchmove', function (event) {
            if (event.scale && event.scale !== 1) { event.preventDefault(); }
        }, { passive: false });
    </script>
</body>
</html>
