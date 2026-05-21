<?php
require_once 'common/config.php';
session_start();
session_destroy();
header("Location: login.php");
exit;
?>
