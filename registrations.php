<?php
require_once "functions.php";
if (!is_logged_in() || !is_admin()) {
    header("Location: login.php");
    exit();
}

$event_id = $_GET['event_id'] ?? 0;
$sql = "SELECT r.*, u.name, u.email FROM registrations r JOIN users u ON r.user_id=u.id WHERE r.event_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Registrations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <a href="admin_dashboard.php" class="btn btn-secondary mb-2">Back</a>
    <div class="card">
        <div class="card-header">Registered Students</div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered At</th>
                </tr>
                <?php while($row = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['registered_at'] ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>