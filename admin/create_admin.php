<?php
// Simple script to create an admin user via a web form.
require_once __DIR__ . '/../database.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Provide username and password';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO admins (username,password_hash) VALUES (?,?)');
        $stmt->bind_param('ss',$username,$hash);
        if ($stmt->execute()) {
            $success = 'Created admin. Please delete this file after use.';
        } else {
            $error = 'Failed to create admin: ' . $stmt->error;
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Create Admin</title>
<link rel="stylesheet" href="../style.css"></head><body>
<div class="container">
  <h2>Create Admin User</h2>
  <?php if(!empty($error)): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if(!empty($success)): ?><div class="success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <form method="post">
    <label>Username</label>
    <input name="username" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Create Admin</button>
  </form>
  <p>After creating an admin, delete this file for security.</p>
</div>
</body></html>
