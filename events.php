<?php
require_once "config.php";

// Fetch all events
$events = [];
$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Handle registration (simple example)
if (isset($_POST['register_event']) && isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
    $event_id = intval($_POST['event_id']);
    $user_id = $_SESSION['user_id']; // Make sure user_id is set in session on login
    // Prevent duplicate registration
    $check = $conn->query("SELECT * FROM event_registrations WHERE event_id=$event_id AND user_id=$user_id");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, user_id, registered_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $msg = "Successfully registered for the event!";
    } else {
        $msg = "You are already registered for this event.";
    }
} elseif (isset($_POST['register_event'])) {
    $msg = "You must be logged in as a student to register for events.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campus Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #f2f6fc 0%, #cde9f2 100%); font-family: 'Roboto', sans-serif; }
        .dashboard {
            min-height: 400px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 24px rgba(21,67,96,0.09);
            padding: 30px 18px 20px 18px;
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .dashboard-title {
            font-size: 1.26rem;
            font-weight: 700;
            color: #1a3c6e;
            margin-bottom: 22px;
            letter-spacing: 1px;
        }
        .dashboard-list {
            list-style: none;
            padding-left: 0;
        }
        .dashboard-list li {
            margin-bottom: 18px;
        }
        .dashboard-list a {
            color: #2471A3;
            font-size: 1.07rem;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }
        .dashboard-list a:hover {
            color: #198754;
        }
        .dashboard-list .bi {
            margin-right: 9px;
            font-size: 1.25rem;
        }
        .event-card {
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(21,67,96,0.10);
            margin-bottom: 28px;
            background: #fff;
            overflow: hidden;
        }
        .event-img {
            width: 100%; max-height: 190px; object-fit: cover; border-top-left-radius: 18px; border-top-right-radius: 18px;
        }
        .event-details { padding: 18px 22px; }
        .event-title { font-size: 1.20rem; font-weight: 700; color: #1a3c6e;}
        .event-desc { color: #444; margin-bottom: 7px;}
        .event-meta { color: #7a8a9c; font-size: 0.98rem;}
        .msg { color: green; font-weight: 500; }
        @media (max-width: 991.98px) {
            .dashboard { margin-bottom: 20px; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Dashboard (left) -->
        <div class="col-md-3 col-lg-3">
            <div class="dashboard">
                <div class="dashboard-title">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </div>
                <ul class="dashboard-list">
                    <li><a href="login.php"><i class="bi bi-person-circle"></i>Login</a></li>
                    <li><a href="register.php"><i class="bi bi-person-plus"></i>Register</a></li>
                    <li><a href="events.php" class="fw-bold"><i class="bi bi-calendar3"></i>Events</a></li>
                    <li><a href="message.php"><i class="bi bi-chat-dots"></i>Messages</a></li>
                    <li><a href="notification.php"><i class="bi bi-bell"></i>Notifications</a></li>
                    <li><a href="about.php"><i class="bi bi-info-circle"></i>About System</a></li>
                    <li><a href="contact.php"><i class="bi bi-envelope"></i>Contact Admin</a></li>
                </ul>
            </div>
        </div>
        <!-- Main Content (center) -->
        <div class="col-md-9 col-lg-9">
            <h2 class="mb-4"><i class="bi bi-calendar3 me-2"></i>Campus Events</h2>
            <?php if (isset($msg)): ?><div class="msg mb-3"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6">
                        <div class="event-card mb-4">
                            <?php if ($event['image'] && file_exists($event['image'])): ?>
                                <img src="<?= htmlspecialchars($event['image']) ?>" class="event-img" alt="Event Image">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=400&q=80"
                                     class="event-img" alt="Default Event">
                            <?php endif; ?>
                            <div class="event-details">
                                <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                                <div class="event-desc"><?= htmlspecialchars($event['description']) ?></div>
                                <div class="event-meta mb-2">
                                    <i class="bi bi-calendar-event"></i> <?= htmlspecialchars($event['event_date']) ?><br>
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($event['location']) ?>
                                </div>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'student'): ?>
                                    <form method="post" class="mt-2">
                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                        <button type="submit" name="register_event" class="btn btn-success btn-sm">
                                            <i class="bi bi-check-circle"></i> Register
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="alert alert-warning py-1 mt-2 mb-0" style="font-size:0.97rem;">
                                        Login as a student to register.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>