<?php
session_start();
require_once __DIR__ . '/../database.php';
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$saveMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_token'])) {
        $token = trim($_POST['approve_token']);
        $status = 'approved';
        $updateStmt = $conn->prepare('UPDATE students SET approval_status = ? WHERE token = ?');
        $updateStmt->bind_param('ss', $status, $token);
        $updateStmt->execute();
        $updateStmt->close();
        $saveMessage = 'Student approved successfully.';
    } elseif (isset($_POST['decline_token'])) {
        $token = trim($_POST['decline_token']);
        $status = 'declined';
        $updateStmt = $conn->prepare('UPDATE students SET approval_status = ? WHERE token = ?');
        $updateStmt->bind_param('ss', $status, $token);
        $updateStmt->execute();
        $updateStmt->close();
        $saveMessage = 'Student declined.';
    }
}

// Ensure required columns exist (older installations may have a smaller schema).
// This prevents "Unknown column 'address'" (and similar) errors when rendering the student list.
$requiredColumns = [
  'address' => 'TEXT',
  'sex' => 'VARCHAR(50)',
  'nationality' => 'VARCHAR(100)',
  'birthplace' => 'VARCHAR(100)',
  'birthdate' => 'DATE',
  'school' => 'VARCHAR(255)',
  'guardian' => 'VARCHAR(255)',
  'relationship' => 'VARCHAR(255)',
  'signature' => 'VARCHAR(255)',
  'semester' => 'VARCHAR(50)',
  'gcash_number' => 'VARCHAR(100)',
  'cellphone' => 'VARCHAR(100)',
  'middlename' => "VARCHAR(255) DEFAULT 'N/A'",
];
foreach ($requiredColumns as $col => $definition) {
  $colCheck = $conn->query("SHOW COLUMNS FROM students LIKE '" . $conn->real_escape_string($col) . "'");
  if ($colCheck && $colCheck->num_rows === 0) {
    $conn->query("ALTER TABLE students ADD COLUMN `" . $col . "` " . $definition);
  }
}

$q = trim($_GET['q'] ?? '');
$course = trim($_GET['course'] ?? '');
$year = trim($_GET['year'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$sort = in_array($_GET['sort'] ?? '', ['created_at','lastname']) ? $_GET['sort'] : 'created_at';
$order = (($_GET['order'] ?? '') === 'asc') ? 'ASC' : 'DESC';

$where = [];
$params = [];
$types = '';
if ($q !== '') { $where[] = "(firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR cellphone LIKE ? OR payment_ref LIKE ? OR gcash_number LIKE ? OR guardian LIKE ? OR address LIKE ? OR school LIKE ?)"; $like = "%$q%"; for ($i=0;$i<9;$i++){ $params[] = $like; $types .= 's'; } }
if ($course !== '') { $where[] = 'course = ?'; $params[] = $course; $types .= 's'; }
if ($year !== '') { $where[] = 'year_level = ?'; $params[] = $year; $types .= 's'; }
if ($status_filter !== '') { $where[] = 'approval_status = ?'; $params[] = $status_filter; $types .= 's'; }

$sql = 'SELECT id, token, firstname, lastname, middlename, address, sex, nationality, birthplace, birthdate, school, guardian, relationship, semester, email, cellphone, gcash_number, course, year_level, payment_ref, payment_proof, student_status, approval_status, created_at FROM students';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= " ORDER BY $sort $order LIMIT 1000";

$stmt = $conn->prepare($sql);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();

$res = $stmt->get_result();

// Fetch courses for filter dropdown
$courses_for_filter = [];
$rc = $conn->query("SELECT title FROM courses ORDER BY title ASC");
if ($rc) {
  while ($rowc = $rc->fetch_assoc()) { $courses_for_filter[] = $rowc['title']; }
}

// Count approval statuses
$statusCounts = [];
$statusQuery = $conn->query("SELECT approval_status, COUNT(*) as count FROM students GROUP BY approval_status");
if ($statusQuery) {
  while ($row = $statusQuery->fetch_assoc()) {
    $statusCounts[$row['approval_status']] = $row['count'];
  }
}
$pendingCount = $statusCounts['pending'] ?? 0;
$approvedCount = $statusCounts['approved'] ?? 0;
$declinedCount = $statusCounts['declined'] ?? 0;

?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Enrollments</title>
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
        <a href="students.php" class="active"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z" fill="#fff"/></svg></span><span class="label">Students</span></a>
        <a href="matriculation.php"><span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm3 3v4h10v-4H7z" fill="#fff"/></svg>
        </span><span class="label">Matriculation</span></a>
        <a href="courses.php"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg></span><span class="label">Courses</span></a>
        <a href="export.php"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="label">Export</span></a>
      </nav>
    </aside>

    <main class="main">
      <div class="container">
        <h2>Enrollments</h2>
        <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; border: 1px solid #dee2e6;">
          <h3 style="margin-top: 0; margin-bottom: 10px;">Enrollment Status Summary</h3>
          <div style="display: flex; gap: 20px;">
            <div style="background: #fff3cd; padding: 10px; border-radius: 4px; border: 1px solid #ffeaa7;">
              <strong>Pending:</strong> <?php echo $pendingCount; ?>
            </div>
            <div style="background: #d4edda; padding: 10px; border-radius: 4px; border: 1px solid #c3e6cb;">
              <strong>Approved:</strong> <?php echo $approvedCount; ?>
            </div>
            <div style="background: #f8d7da; padding: 10px; border-radius: 4px; border: 1px solid #f5c6cb;">
              <strong>Declined:</strong> <?php echo $declinedCount; ?>
            </div>
          </div>
        </div>
        <?php if (!empty($saveMessage)): ?>
          <div style="background:#d4edda;color:#155724;padding:10px;margin-bottom:10px;border:1px solid #c3e6cb;border-radius:4px;"><?php echo htmlspecialchars($saveMessage); ?></div>
        <?php endif; ?>
        <form method="get" class="search-form">
          <input type="text" name="q" placeholder="Search name, email, cellphone, payment ref" value="<?php echo htmlspecialchars($q); ?>">
          <select name="course">
            <option value="">All Courses</option>
            <?php foreach ($courses_for_filter as $cf): ?>
              <option value="<?php echo htmlspecialchars($cf); ?>" <?php if($course===$cf) echo 'selected'; ?>><?php echo htmlspecialchars($cf); ?></option>
            <?php endforeach; ?>
          </select>
          <select name="year">
            <option value="">All Years</option>
            <option <?php if($year==='1st Year') echo 'selected'; ?>>1st Year</option>
            <option <?php if($year==='2nd Year') echo 'selected'; ?>>2nd Year</option>
            <option <?php if($year==='3rd Year') echo 'selected'; ?>>3rd Year</option>
            <option <?php if($year==='4th Year') echo 'selected'; ?>>4th Year</option>
          </select>
          <select name="status">
            <option value="">All Statuses</option>
            <option value="pending" <?php if($status_filter==='pending') echo 'selected'; ?>>Pending</option>
            <option value="approved" <?php if($status_filter==='approved') echo 'selected'; ?>>Approved</option>
            <option value="declined" <?php if($status_filter==='declined') echo 'selected'; ?>>Declined</option>
          </select>
          <select name="sort">
            <option value="created_at" <?php if($sort==='created_at') echo 'selected'; ?>>Newest</option>
            <option value="lastname" <?php if($sort==='lastname') echo 'selected'; ?>>Last name</option>
          </select>
          <select name="order">
            <option value="desc" <?php if($order==='DESC') echo 'selected'; ?>>Desc</option>
            <option value="asc" <?php if($order==='ASC') echo 'selected'; ?>>Asc</option>
          </select>
          <button type="submit">Filter</button>
        </form>

        <div class="table-panel">
          <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Address</th><th>Sex</th><th>Birthdate</th><th>Guardian</th><th>Course / Year / Semester</th><th>GCash</th><th>Cellphone</th><th>Email</th><th>Payment Ref</th><th>Submitted</th><th>Approval</th><th>Actions</th></tr></thead>
            <tbody>
            <?php while($r = $res->fetch_assoc()): ?>
              <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['firstname'] . ' ' . $r['middlename'] . ' ' . $r['lastname']); ?></td>
                <td><?php echo htmlspecialchars($r['address']); ?></td>
                <td><?php echo htmlspecialchars($r['sex']); ?></td>
                <td><?php echo htmlspecialchars($r['birthdate']); ?></td>
                <td><?php echo htmlspecialchars($r['guardian']); ?></td>
                <td><?php echo htmlspecialchars($r['course'] . ' / ' . $r['year_level'] . ' / ' . $r['semester']); ?></td>
                <td><?php echo htmlspecialchars($r['gcash_number']); ?></td>
                <td><?php echo htmlspecialchars($r['cellphone']); ?></td>
                <td><?php echo htmlspecialchars($r['email']); ?></td>
                <td><?php echo htmlspecialchars($r['payment_ref']); ?></td>
                <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                <td><?php 
                  $status = $r['approval_status'] ?? 'pending';
                  if ($status === 'pending') {
                    echo 'Pending';
                  } elseif ($status === 'approved') {
                    echo 'Approved';
                  } elseif ($status === 'declined') {
                    echo 'Declined';
                  } else {
                    echo htmlspecialchars(ucfirst($status));
                  }
                ?></td>
                <td>
                  <a class="small" href="../receipt.php?token=<?php echo urlencode($r['token']); ?>" target="_blank">View</a>
                  <a class="small" href="../receipt_pdf.php?token=<?php echo urlencode($r['token']); ?>">PDF</a>
                  <?php if($r['payment_proof']): ?>
                    <a class="small" href="../uploads/<?php echo rawurlencode($r['payment_proof']); ?>" target="_blank">Proof</a>
                  <?php endif; ?>
                  <?php if (($r['approval_status'] ?? 'pending') === 'pending'): ?>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="approve_token" value="<?php echo htmlspecialchars($r['token']); ?>">
                      <button type="submit" class="small" style="background:#28a745;color:white;border:none;padding:2px 5px;margin:1px;">Approve</button>
                    </form>
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="decline_token" value="<?php echo htmlspecialchars($r['token']); ?>">
                      <button type="submit" class="small" style="background:#dc3545;color:white;border:none;padding:2px 5px;margin:1px;">Decline</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <p><a class="button" href="export.php?<?php echo http_build_query(['q'=>$q,'course'=>$course,'year'=>$year]); ?>">Export CSV</a></p>
      </div>
    </main>
  </div>
</body></html>
