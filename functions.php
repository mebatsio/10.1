<?php
require_once "config.php";

// Authentication: Check login for both admin and student
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Send Email (basic)
function send_email($to, $subject, $message) {
    $headers = "From: campus-events@yourdomain.com\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}
?>