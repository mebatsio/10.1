<?php
session_start();
require_once "config.php";

// Ensure student is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student information
$stmt = $conn->prepare("SELECT s.*, d.name AS department_name FROM students s LEFT JOIN departments d ON s.department_id = d.id WHERE s.id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();
$stmt->close();

// Fetch all departments for select
$departments = [];
$res = $conn->query("SELECT * FROM departments ORDER BY name");
while ($row = $res->fetch_assoc()) {
    $departments[] = $row;
}

// Handle student info update
if (isset($_POST['update_info'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $department_id = $_POST['department_id'];

    $stmt = $conn->prepare("UPDATE students SET fullname=?, email=?, phone=?, department_id=? WHERE id=?");
    $stmt->bind_param("sssii", $fullname, $email, $phone, $department_id, $user_id);
    $stmt->execute();
    $stmt->close();

    $msg = "Your profile has been updated.";
    // Refresh student info
    $stmt = $conn->prepare("SELECT s.*, d.name AS department_name FROM students s LEFT JOIN departments d ON s.department_id = d.id WHERE s.id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $student_result = $stmt->get_result();
    $student = $student_result->fetch_assoc();
    $stmt->close();
}

// Fetch events
$events = [];
$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Handle event registration
if (isset($_POST['register_event'])) {
    $event_id = intval($_POST['event_id']);
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Registration - Campus Event System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6fa; font-family: 'Roboto', sans-serif; }
        .main-content { padding: 30px 40px; }
        .event-card {
            border-radius: 18px;
            box-shadow: 0 4px 18px rgba(21,67,96,0.09);
            margin-bottom: 28px;
            background: #fff;
            overflow: hidden;
        }
        .event-img {
            width: 100%; max-height: 180px; object-fit: cover; border-top-left-radius: 18px; border-top-right-radius: 18px;
        }
        .event-details { padding: 16px 18px; }
        .event-title { font-size: 1.13rem; font-weight: 700; color: #23649b;}
        .event-desc { color: #444; margin-bottom: 7px;}
        .event-meta { color: #7a8a9c; font-size: 0.97rem; }
        .event-actions { margin-top: 10px; display: flex; gap: 10px; }
        .student-info-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(21,67,96,0.08);
            padding: 24px 20px;
            margin-bottom: 28px;
        }
        .profile-img {
            width: 100px; height: 100px; border-radius: 50%; object-fit: cover;
            border: 3px solid #2471A3;
            background: #eaf3fb;
        }
        .profile-info h5 { font-size: 1.10rem; font-weight: 700; color: #23649b;}
        .profile-info span { color: #7a8a9c; font-size: 0.98rem;}
        .profile-info div { margin-bottom: 5px;}
        .form-label { font-weight: 500; }
        .msg { color: green; font-weight: 500; }
        @media (max-width: 991.98px) {
            .main-content { padding: 16px;}
            .event-actions { flex-direction: column; gap: 5px; }
        }
    </style>
</head>
<body>
<div class="container main-content">
    <h2 class="mb-4"><i class="bi bi-calendar3"></i> Campus Events & Registration</h2>
    <?php if (isset($msg)): ?><div class="msg mb-3"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <!-- Student Info Card -->
    <div class="student-info-card">
        <?php if ($student): ?>
            <div class="d-flex flex-row align-items-center">
                <img src="<?= isset($student['photo']) && $student['photo'] ? htmlspecialchars($student['photo']) : 'https://ui-avatars.com/api/?name='.urlencode($student['fullname']).'&background=2471A3&color=fff&size=100' ?>"
                     alt="Student Photo" class="profile-img me-3">
                <div class="profile-info">
                    <h5><?= htmlspecialchars($student['fullname']) ?></h5>
                    <div><span><i class="bi bi-envelope"></i> <?= htmlspecialchars($student['email']) ?></span></div>
                    <div><span><i class="bi bi-telephone"></i> <?= htmlspecialchars($student['phone']) ?></span></div>
                    <div><span><i class="bi bi-mortarboard"></i> <?= htmlspecialchars($student['department_name']) ?></span></div>
                    <div><span><i class="bi bi-card-list"></i> Student ID: <?= htmlspecialchars($student['id']) ?></span></div>
                </div>
            </div>
            <!-- Update Info Button triggers collapsible form -->
            <button class="btn btn-info btn-sm mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#updateInfoForm" aria-expanded="false" aria-controls="updateInfoForm">
                <i class="bi bi-pencil-square"></i> Update Info
            </button>
            <div class="collapse mt-3" id="updateInfoForm">
                <form method="post">
                    <div class="mb-2">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= ($student['department_id'] == $dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="update_info" class="btn btn-primary btn-sm"><i class="bi bi-check-circle"></i> Save Changes</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">Student information not found. Please contact admin.</div>
        <?php endif; ?>
    </div>

    <!-- Events Section -->
    <h4 class="mb-3"><i class="bi bi-calendar3"></i> Campus Events</h4>
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
                        <div class="event-meta mb-2">
                            <i class="bi bi-calendar-event"></i> <?= isset($event['event_date']) ? htmlspecialchars($event['event_date']) : 'No Date' ?><br>
                            <i class="bi bi-geo-alt"></i> <?= isset($event['location']) ? htmlspecialchars($event['location']) : 'No location' ?>
                        </div>
                        <div class="event-actions">
                            <form method="post">
                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                <button type="submit" name="register_event" class="btn btn-success btn-sm">
                                    <i class="bi bi-check-circle"></i> Register
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($events)): ?>
            <div class="alert alert-info">No events available at the moment.</div>
        <?php endif; ?>
    </div>
</div>
<!-- Bootstrap JS for collapse -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>