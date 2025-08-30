<?php
require_once "functions.php";
if (!is_logged_in() || !is_student()) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$eid = $_POST['event_id'] ?? 0;

// Check if already registered
$sql = "SELECT * FROM registrations WHERE user_id=? AND event_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $uid, $eid);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    header("Location: student_dashboard.php?msg=already");
    exit();
}

// Check max participants
$sql = "SELECT max_participants FROM events WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eid);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$max = $row['max_participants'];

$sql2 = "SELECT COUNT(*) as cnt FROM registrations WHERE event_id=?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $eid);
$stmt2->execute();
$res2 = $stmt2->get_result();
$row2 = $res2->fetch_assoc();
if ($row2['cnt'] >= $max) {
    header("Location: student_dashboard.php?msg=full");
    exit();
}

// Register
$sql = "INSERT INTO registrations (user_id, event_id, registered_at) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $uid, $eid);
$stmt->execute();

// Send confirmation email
$sql = "SELECT email FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

$sql = "SELECT name, date, time FROM events WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eid);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();

$subject = "Event Registration Confirmation";
$message = "Dear Student,<br>
You have successfully registered for the event <strong>{$event['name']}</strong>.<br>
Date: {$event['date']} Time: {$event['time']}<br>
Thank you!";

send_email($user['email'], $subject, $message);

header("Location: student_dashboard.php?msg=success");
exit();
?>