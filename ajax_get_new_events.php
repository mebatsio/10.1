<?php
session_start();
require_once "config.php";

// For demo, you could use user_id or session for filtering. For now, use a session last check.
if (!isset($_SESSION['last_event_check'])) {
    $_SESSION['last_event_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
}

// Make sure events table has a created_at DATETIME column!
$stmt = $conn->prepare("SELECT id, title, description, event_date, created_at FROM events WHERE event_date >= CURDATE() AND created_at > ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("s", $_SESSION['last_event_check']);
$stmt->execute();
$res = $stmt->get_result();
$events = [];
while ($row = $res->fetch_assoc()) $events[] = $row;
$stmt->close();

$count = count($events);

// Update last check time
$_SESSION['last_event_check'] = date('Y-m-d H:i:s');

header('Content-Type: application/json');
echo json_encode([
    'count' => $count,
    'events' => $events
]);