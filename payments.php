<?php
require_once 'common/config.php';
// Redirect to orders as it tracks payments too, kept for menu sync
header("Location: orders.php");
exit;
?>
