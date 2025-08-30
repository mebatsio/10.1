<?php

require_once "config.php";

// Check admin authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Error message
$error = '';
$msg = '';

// Ensure uploads folder exists
$uploadDir = "uploads";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle event creation
if (isset($_POST['create_event'])) {
    $title = $_POST['title'];
    $desc = $_POST['desc'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $image = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid("evt_") . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "$uploadDir/$image_name");
        $image = "$uploadDir/$image_name";
    }

    // Try inserting event, catch SQL errors
    try {
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $desc, $date, $location, $image);
        $stmt->execute();
        $stmt->close();
        $msg = "Event created successfully!";
    } catch (mysqli_sql_exception $e) {
        $error = "Error creating event: " . $e->getMessage();
    }
}

// Handle event deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Remove image file if exists
    $res = $conn->query("SELECT image FROM events WHERE id=$id");
    $row = $res->fetch_assoc();
    if ($row && isset($row['image']) && $row['image'] && file_exists($row['image'])) {
        unlink($row['image']);
    }
    $conn->query("DELETE FROM events WHERE id=$id");
    $msg = "Event deleted!";
}

// Fetch all events
$events = [];
try {
    $result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
} catch (mysqli_sql_exception $e) {
    $error = "Error fetching events: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Campus Event Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f4f6fa; font-family: 'Roboto', sans-serif; }
        .sidebar {
            min-height: 100vh;
            background: #2a4373;
            color: #fff;
            padding: 40px 20px 0 20px;
        }
        .sidebar h4 { color: #fff; margin-bottom: 30px; }
        .sidebar a {
            color: #fff; text-decoration: none; display: block; margin-bottom: 18px; font-size: 1.08rem; font-weight: 500;
        }
        .sidebar a.active, .sidebar a:hover { color: #ffc107; }
        .main-content { padding: 30px 40px; }
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
        .event-title { font-size: 1.25rem; font-weight: 700; color: #2a4373;}
        .event-desc { color: #444; margin-bottom: 7px;}
        .event-meta { color: #7a8a9c; font-size: 0.98rem;}
        .event-actions a { margin-right: 14px; }
        .form-section {
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 2px 12px rgba(21,67,96,0.08);
            padding: 28px 24px;
            margin-bottom: 30px;
        }
        .msg { color: green; font-weight: 500; }
        .error { color: red; font-weight: 500; }
        @media (max-width: 991.98px) {
            .main-content { padding: 16px; }
            .sidebar { padding: 20px 10px 0 10px; }
        }
    </style>
</head>
<body>
<div class="row gx-0">
    <div class="col-md-3 sidebar">
        <h4><i class="bi bi-shield-lock me-2"></i>Admin Panel</h4>
        <a href="admin_dashboard.php" class="active"><i class="bi bi-calendar2-event me-2"></i>Manage Events</a>
        <a href="#create"><i class="bi bi-plus-circle me-2"></i>Create Event</a>
        <a href="usermanagement.php"><i class="bi bi-people me-2"></i>User Management</a>
        <a href="logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
    </div>
    <div class="col-md-9 main-content">
        <h2 class="mb-4">Event Management</h2>
        <?php if ($msg): ?><div class="msg mb-3"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error mb-3"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="form-section mb-5" id="create">
            <h4 class="mb-3"><i class="bi bi-plus-circle"></i> Create New Event</h4>
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="desc" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Image (jpg/png)</label>
                    <input type="file" name="image" accept="image/*" class="form-control">
                </div>
                <button type="submit" name="create_event" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Event</button>
            </form>
        </div>

        <h4 class="mb-3">All Events</h4>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6">
                    <div class="event-card mb-4">
                        <?php
                        $eventImage = (isset($event['image']) && $event['image'] && file_exists($event['image']))
                            ? $event['image']
                            : "https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=400&q=80";
                        ?>
                        <img src="<?= htmlspecialchars($eventImage) ?>" class="event-img" alt="Event Image">
                        <div class="event-details">
                            <div class="event-title"><?= isset($event['title']) ? htmlspecialchars($event['title']) : 'No Title' ?></div>
                            <div class="event-desc"><?= isset($event['description']) ? htmlspecialchars($event['description']) : 'No Description' ?></div>
                            <div class="event-meta">
                                <i class="bi bi-calendar-event"></i>
                                <?= isset($event['event_date']) ? htmlspecialchars($event['event_date']) : 'No Date' ?><br>
                                <i class="bi bi-geo-alt"></i>
                                <?= isset($event['location']) ? htmlspecialchars($event['location']) : 'No location' ?>
                            </div>
                            <div class="event-actions mt-3">
                                <a href="update_event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i> Edit</a>
                                <a href="admin_dashboard.php?delete=<?= $event['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?');"><i class="bi bi-trash"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($events)): ?>
                <div class="alert alert-info">No events created yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>