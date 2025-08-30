<?php
session_start();
define('USER_FILE', 'users.json');
define('MESSAGE_FILE', 'messages.json');

function load_data($file) {
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}
function save_data($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

// Assume logged in as sender, else use "guest"
$sender = $_SESSION['username'] ?? 'guest';

// Send message
if (isset($_POST['send'])) {
    $messages = load_data(MESSAGE_FILE);
    $messages[] = [
        "from" => $sender,
        "to" => $_POST['to'],
        "subject" => $_POST['subject'],
        "body" => $_POST['body'],
        "time" => date("Y-m-d H:i:s")
    ];
    save_data(MESSAGE_FILE, $messages);
    $msg = "Message sent!";
}

$users = load_data(USER_FILE);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Send Message</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Send Message</h2>
    <?php if (isset($msg)): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <form method="post" class="mb-4">
        <div class="mb-3">
            <label for="to" class="form-label">To User</label>
            <select name="to" id="to" class="form-select" required>
                <?php foreach ($users as $u): ?>
                    <option value="<?= htmlspecialchars($u['username']) ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['username']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" name="subject" id="subject" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="body" class="form-label">Message</label>
            <textarea name="body" id="body" class="form-control" rows="4" required></textarea>
        </div>
        <button name="send" type="submit" class="btn btn-primary">Send</button>
        <a href="message.php" class="btn btn-secondary">Inbox</a>
    </form>
</div>
</body>
</html>