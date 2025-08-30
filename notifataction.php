<?php
session_start();
require_once "config.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get last visit time from session or set to now on first visit
if (!isset($_SESSION['last_event_check'])) {
    $_SESSION['last_event_check'] = date('Y-m-d H:i:s');
}

// Fetch new events since last check
$stmt = $conn->prepare("SELECT * FROM events WHERE event_date >= CURDATE() AND created_at > ?");
$stmt->bind_param("s", $_SESSION['last_event_check']);
$stmt->execute();
$events_result = $stmt->get_result();

// Update last check time
$_SESSION['last_event_check'] = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - New Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body style="background:#f4f6fa;">
<div class="container py-4">
    <h3 class="mb-4"><i class="bi bi-bell"></i> Notifications</h3>
    <?php if ($events_result->num_rows > 0): ?>
        <div class="alert alert-info">New events have been created!</div>
        <ul class="list-group">
            <?php while ($event = $events_result->fetch_assoc()): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($event['title']) ?></strong><br>
                    <?= htmlspecialchars($event['description']) ?><br>
                    <span class="text-muted"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($event['event_date']) ?></span>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <div class="alert alert-success">No new event notifications.</div>
    <?php endif; ?>
</div>
</body>
</html>