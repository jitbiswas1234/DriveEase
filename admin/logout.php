<?php
session_start();
require_once '../config/base_url.php';

// Destroy session
session_unset();
session_destroy();

// Redirect to main login
header('Location: ' . $base_url . 'login.php');
exit();
?>