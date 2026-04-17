<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $redirect_login = isset($path) ? $path . 'login.php' : 'login.php';
    header("Location: " . $redirect_login);
    exit();
}
?>
