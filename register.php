<?php
require_once "config.php";
$msg = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $msg = "Email already registered.";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $pass);
        $stmt->execute();
        $msg = "Registered successfully! You can login now.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">Student Registration</div>
                <div class="card-body">
                    <?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-success w-100">Register</button>
                    </form>
                    <a href="login.php" class="d-block text-center mt-2">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>