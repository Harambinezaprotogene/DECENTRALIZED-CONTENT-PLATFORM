<?php
session_start();
session_destroy();
header('Location: /kabaka/public/viewer_dashboard/login.php');
exit;
?>
