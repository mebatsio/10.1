<?php
// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$db = "campus_events";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Session start
session_start();
?>