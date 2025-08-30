<?php
require_once "config.php";

$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Admin login
    $sql = "SELECT * FROM admins WHERE email=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit();
    }

    // Student login
    $sql = "SELECT * FROM users WHERE email=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'student';
        header("Location: student_dashboard.php");
        exit();
    }

    $msg = "Invalid credentials!";
}
?>

<!-- Bootstrap Login Form -->
<!DOCTYPE html>
<html>
<head>
    <title>Login - Campus Event Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Login</div>
                <div class="card-body">
                    <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Login</button>
                    </form>
                    <a href="register.php" class="d-block text-center mt-2">Student Register</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>