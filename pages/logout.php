<?php
require_once '../includes/config.php';
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // Correct relative path
exit;
?>