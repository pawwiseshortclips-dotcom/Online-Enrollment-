<?php
session_start();
require_once __DIR__ . '/../database.php';
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Create courses table if needed
$create = "CREATE TABLE IF NOT EXISTS courses (id INT AUTO_INCREMENT PRIMARY KEY, code VARCHAR(50), title VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($create);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['title'])) {
    $code = $conn->real_escape_string(trim($_POST['code']));
    $title = $conn->real_escape_string(trim($_POST['title']));
    $stmt = $conn->prepare('INSERT INTO courses (code,title) VALUES (?,?)');
    $stmt->bind_param('ss',$code,$title);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query('DELETE FROM courses WHERE id=' . $id);
}

$res = $conn->query('SELECT * FROM courses ORDER BY created_at DESC');

?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Manage Courses</title>
<link rel="stylesheet" href="../style.css"></head>
<body>
  <header class="top-header">
    <div class="top-left">
      <div class="logo"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg>
        <span class="logo-text">Online Enrollment System <small>- Admin</small></span>
      </div>
    </div>
    <div class="top-right">
      <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?></div>
      <div class="avatar"><img src="https://i.pravatar.cc/40?u=<?php echo urlencode($_SESSION['admin_user']); ?>" alt="avatar"></div>
      <a class="btn-logout" href="logout.php">Logout</a>
    </div>
  </header>
  <div class="app">
    <aside class="sidebar" tabindex="0">
      <div class="brand">
        <span class="brand-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg></span>
        <span class="brand-label">Online Enrollment<span class="dot">Sys</span></span>
      </div>
      <nav>
        <a href="dashboard.php"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9z" fill="#fff"/></svg></span><span class="label">Dashboard</span></a>
        <a href="students.php"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z" fill="#fff"/></svg></span><span class="label">Students</span></a>
        <a href="courses.php" class="active"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg></span><span class="label">Courses</span></a>
        <a href="export.php"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="label">Export</span></a>
      </nav>
    </aside>

    <main class="main">
      <div class="container">
        <h2>Courses</h2>
        <form method="post" class="simple-form">
          <label>Code</label>
          <input name="code">
          <label>Title</label>
          <input name="title" required>
          <button type="submit">Add Course</button>
        </form>

        <div class="table-panel">
          <table class="table">
            <thead><tr><th>ID</th><th>Code</th><th>Title</th><th>Created</th><th>Action</th></tr></thead>
            <tbody>
            <?php while($r=$res->fetch_assoc()): ?>
              <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['code']); ?></td>
                <td><?php echo htmlspecialchars($r['title']); ?></td>
                <td><?php echo $r['created_at']; ?></td>
                <td><a class="small" href="?delete=<?php echo $r['id']; ?>" onclick="return confirm('Delete course?')">Delete</a></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</body></html>
