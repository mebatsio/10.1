<?php

require_once "config.php";

// Check admin authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all registrations with user and event info
$query = "SELECT er.id as reg_id, er.registered_at, 
                 s.fullname, s.email, s.phone, d.name as department, s.id as student_id,
                 e.title as event_title, e.event_date, e.location, e.id as event_id
            FROM event_registrations er
            JOIN students s ON er.user_id = s.id
            LEFT JOIN departments d ON s.department_id = d.id
            JOIN events e ON er.event_id = e.id
            ORDER BY e.event_date DESC, er.registered_at DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - Campus Event System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
        .usermanagement-section {
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 2px 12px rgba(21,67,96,0.08);
            padding: 28px 24px;
            margin-bottom: 30px;
        }
        .table thead { background: #23649b; color: #fff; }
        .table tbody tr td { vertical-align: middle;}
        @media (max-width: 991.98px) {
            .main-content { padding: 16px; }
            .sidebar { padding: 20px 10px 0 10px; }
        }
    </style>
</head>
<body>
<div class="row gx-0">
    <!-- Sidebar -->
    <div class="col-md-3 sidebar">
        <h4><i class="bi bi-shield-lock me-2"></i>Admin Panel</h4>
        <a href="admin_dashboard.php"><i class="bi bi-calendar2-event me-2"></i>Manage Events</a>
        <a href="admin_dashboard.php#create"><i class="bi bi-plus-circle me-2"></i>Create Event</a>
        <a href="usermanagement.php" class="active"><i class="bi bi-people me-2"></i>User Management</a>
        <a href="logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
        <hr style="color:#fff;">
        <div style="margin-top:20px;font-size:0.92rem;">
            <i class="bi bi-info-circle"></i> View users who registered for events, their departments & details.
        </div>
    </div>
    <!-- Main Content -->
    <div class="col-md-9 main-content">
        <h2 class="mb-4"><i class="bi bi-people"></i> User Management</h2>
        <div class="usermanagement-section">
            <h5 class="mb-3"><i class="bi bi-person-lines-fill"></i> Registration List</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Event Date</th>
                            <th>Location</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Department</th>
                            <th>Student ID</th>
                            <th>Registered At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['event_title']) ?></td>
                                <td><?= htmlspecialchars($row['event_date']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phone']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><?= htmlspecialchars($row['registered_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($result->num_rows == 0): ?>
                            <tr><td colspan="9" class="text-center">No event registrations found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>