<?php
require_once "functions.php";
if (!is_logged_in() || !is_admin()) {
    header("Location: login.php");
    exit();
}

$edit_mode = false;
$name = $date = $time = $venue = $desc = $image = $deadline = $max = "";
if (isset($_GET['edit'])) {
    $eid = $_GET['edit'];
    $sql = "SELECT * FROM events WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) {
        $row = $res->fetch_assoc();
        $edit_mode = true;
        $name = $row['name'];
        $date = $row['date'];
        $time = $row['time'];
        $venue = $row['venue'];
        $desc = $row['description'];
        $image = $row['image'];
        $deadline = $row['registration_deadline'];
        $max = $row['max_participants'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $venue = $_POST['venue'];
    $desc = $_POST['description'];
    $deadline = $_POST['deadline'];
    $max = $_POST['max'];
    $image = "";

    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target = "uploads/" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image = $target;
    } else if ($edit_mode) {
        $image = $_POST['existing_image'];
    }

    if ($edit_mode) {
        $sql = "UPDATE events SET name=?, date=?, time=?, venue=?, description=?, image=?, registration_deadline=?, max_participants=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssii", $name, $date, $time, $venue, $desc, $image, $deadline, $max, $eid);
        $stmt->execute();
    } else {
        $sql = "INSERT INTO events (name, date, time, venue, description, image, registration_deadline, max_participants) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $name, $date, $time, $venue, $desc, $image, $deadline, $max);
        $stmt->execute();
    }
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Event</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <a href="admin_dashboard.php" class="btn btn-secondary mb-2">Back</a>
    <div class="card">
        <div class="card-header"><?= $edit_mode ? 'Edit' : 'Add' ?> Event</div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3"><label>Event Name</label><input type="text" name="name" class="form-control" required value="<?= $name ?>"></div>
                <div class="mb-3"><label>Date</label><input type="date" name="date" class="form-control" required value="<?= $date ?>"></div>
                <div class="mb-3"><label>Time</label><input type="time" name="time" class="form-control" required value="<?= $time ?>"></div>
                <div class="mb-3"><label>Venue</label><input type="text" name="venue" class="form-control" required value="<?= $venue ?>"></div>
                <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"><?= $desc ?></textarea></div>
                <div class="mb-3"><label>Image</label>
                    <input type="file" name="image" class="form-control">
                    <?php if($edit_mode && $image): ?>
                        <img src="<?= $image ?>" width="100" class="mt-2"><input type="hidden" name="existing_image" value="<?= $image ?>">
                    <?php endif; ?>
                </div>
                <div class="mb-3"><label>Registration Deadline</label><input type="date" name="deadline" class="form-control" required value="<?= $deadline ?>"></div>
                <div class="mb-3"><label>Max Participants</label><input type="number" name="max" class="form-control" required value="<?= $max ?>"></div>
                <button class="btn btn-success"><?= $edit_mode ? 'Update' : 'Add' ?></button>
            </form>
        </div>
    </div>
</div>
</body>
</html>