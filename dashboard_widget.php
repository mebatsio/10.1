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
    $_SESSION['last_event_check'] = date('Y-m-d H:i:s', strtotime('-1 day'));
}

// Count new events since last check
$stmt = $conn->prepare("SELECT COUNT(*) as new_events FROM events WHERE event_date >= CURDATE() AND created_at > ?");
$stmt->bind_param("s", $_SESSION['last_event_check']);
$stmt->execute();
$stmt->bind_result($new_events_count);
$stmt->fetch();
$stmt->close();

// Fetch last 3 new events for widget preview
$stmt = $conn->prepare("SELECT * FROM events WHERE event_date >= CURDATE() AND created_at > ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("s", $_SESSION['last_event_check']);
$stmt->execute();
$events_result = $stmt->get_result();

$_SESSION['last_event_check'] = date('Y-m-d H:i:s');
?>
<!-- Notification Badge and Widget -->
<div style="position:fixed;top:20px;right:30px;z-index:1100;">
    <button type="button" class="btn btn-light position-relative" data-bs-toggle="modal" data-bs-target="#newEventsModal" id="notifBtn">
        <i class="bi bi-bell-fill"></i>
        <?php if ($new_events_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $new_events_count ?></span>
        <?php endif; ?>
    </button>
</div>

<!-- Widget Modal for new events -->
<div class="modal fade" id="newEventsModal" tabindex="-1" aria-labelledby="newEventsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newEventsModalLabel"><i class="bi bi-bell"></i> New Event Notifications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if ($new_events_count > 0): ?>
            <div class="alert alert-info">You have <?= $new_events_count ?> new event<?= $new_events_count>1?'s':'' ?>!</div>
            <ul class="list-group mb-2">
                <?php while ($event = $events_result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($event['title']) ?></strong><br>
                        <?= htmlspecialchars($event['description']) ?><br>
                        <span class="text-muted"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($event['event_date']) ?></span>
                    </li>
                <?php endwhile; ?>
            </ul>
            <a href="events.php" class="btn btn-primary btn-sm">View All Events</a>
        <?php else: ?>
            <div class="alert alert-success">No new event notifications.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<!-- Bootstrap JS for modal -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>