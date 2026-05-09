<?php
session_start();
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../subjects.php';
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

$q = trim($_GET['q'] ?? '');
$course = trim($_GET['course'] ?? '');
$year = trim($_GET['year'] ?? '');
$status = trim($_GET['status'] ?? '');
$preview_token = trim($_GET['preview_token'] ?? '');
$saveMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['preview_token'])) {
    $preview_token = trim($_POST['preview_token']);
    $registration_fee = floatval($_POST['registration_fee'] ?? 0);
    $tuition_fee = floatval($_POST['tuition_fee'] ?? 0);
    $lab_fee = floatval($_POST['lab_fee'] ?? 0);
    $misc_fee = floatval($_POST['misc_fee'] ?? 0);
    $upon_registration = floatval($_POST['upon_registration'] ?? 4700);
    $prelim_fee = floatval($_POST['prelim_fee'] ?? 0);
    $midterm_fee = floatval($_POST['midterm_fee'] ?? 0);
    $semi_final_fee = floatval($_POST['semi_final_fee'] ?? 0);
    $final_fee = floatval($_POST['final_fee'] ?? 0);
    $bank_account_number = trim($_POST['bank_account_number'] ?? '');

    $updateStmt = $conn->prepare('UPDATE students SET registration_fee = ?, tuition_fee = ?, lab_fee = ?, misc_fee = ?, upon_registration = ?, prelim_fee = ?, midterm_fee = ?, semi_final_fee = ?, final_fee = ?, bank_account_number = ? WHERE token = ?');
    if ($updateStmt) {
        $updateStmt->bind_param('dddddddddss', $registration_fee, $tuition_fee, $lab_fee, $misc_fee, $upon_registration, $prelim_fee, $midterm_fee, $semi_final_fee, $final_fee, $bank_account_number, $preview_token);
        if ($updateStmt->execute()) {
            $updateStmt->close();
            header('Location: matriculation.php?preview_token=' . urlencode($preview_token));
            exit;
        }
        $updateStmt->close();
    }
}

$where = [];
$params = [];
$types = '';
if ($q !== '') {
    $where[] = "(firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR cellphone LIKE ? OR token LIKE ?)";
    $like = "%$q%";
    for ($i = 0; $i < 5; $i++) { $params[] = $like; $types .= 's'; }
}
if ($course !== '') { $where[] = 'course = ?'; $params[] = $course; $types .= 's'; }
if ($year !== '') { $where[] = 'year_level = ?'; $params[] = $year; $types .= 's'; }
if ($status !== '') { $where[] = 'student_status = ?'; $params[] = $status; $types .= 's'; }

$sql = 'SELECT id, token, firstname, lastname, middlename, course, year_level, student_status, approval_status, email, cellphone, payment_ref, created_at FROM students';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY created_at DESC LIMIT 1000';

$stmt = $conn->prepare($sql);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();

$courses_for_filter = [];
$rc = $conn->query("SELECT title FROM courses ORDER BY title ASC");
if ($rc) {
    while ($rowc = $rc->fetch_assoc()) {
        $courses_for_filter[] = $rowc['title'];
    }
}

function getMatriculationLink(array $row): array {
    $status = strtolower($row['student_status'] ?? '');
    if ($status === 'regular') {
        return ['url' => '../regular.php?token=' . urlencode($row['token']), 'label' => 'Download Form', 'type' => 'Regular'];
    }
    if (in_array($status, ['iregular'], true)) {
        return ['url' => '../iregular.php?token=' . urlencode($row['token']), 'label' => 'Preview', 'type' => ucfirst($status)];
    }
    return ['url' => '#', 'label' => 'N/A', 'type' => 'Unknown'];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Matriculation</title>
  <link rel="stylesheet" href="../style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
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
        <a href="matriculation.php" class="active"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm3 3v4h10v-4H7z" fill="#fff"/></svg></span><span class="label">Matriculation</span></a>
        <a href="courses.php"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg></span><span class="label">Courses</span></a>
        <a href="export.php"><span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><span class="label">Export</span></a>
      </nav>
    </aside>

    <main class="main">
      <div class="container">
        <h2>Matriculation</h2>
        <?php if (!empty($saveMessage)): ?>
          <div style="background:#d4edda;color:#155724;padding:10px;margin-bottom:10px;border:1px solid #c3e6cb;border-radius:4px;"><?php echo htmlspecialchars($saveMessage); ?></div>
        <?php endif; ?>
        <form method="get" class="search-form">
          <input type="text" name="q" placeholder="Search name, email, cellphone, token" value="<?php echo htmlspecialchars($q); ?>">
          <select name="course">
            <option value="">All Courses</option>
            <?php foreach ($courses_for_filter as $cf): ?>
              <option value="<?php echo htmlspecialchars($cf); ?>" <?php if ($course === $cf) echo 'selected'; ?>><?php echo htmlspecialchars($cf); ?></option>
            <?php endforeach; ?>
          </select>
          <select name="year">
            <option value="">All Years</option>
            <option value="1st Year" <?php if ($year === '1st Year') echo 'selected'; ?>>1st Year</option>
            <option value="2nd Year" <?php if ($year === '2nd Year') echo 'selected'; ?>>2nd Year</option>
            <option value="3rd Year" <?php if ($year === '3rd Year') echo 'selected'; ?>>3rd Year</option>
            <option value="4th Year" <?php if ($year === '4th Year') echo 'selected'; ?>>4th Year</option>
          </select>
          <select name="status">
            <option value="">All Status</option>
            <option value="regular" <?php if ($status === 'regular') echo 'selected'; ?>>Regular</option>
            <option value="iregular" <?php if ($status === 'iregular') echo 'selected'; ?>>Iregular</option>
          </select>
          <button type="submit">Filter</button>
        </form>

        <div class="table-panel">
          <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Course / Year</th><th>Email</th><th>Cellphone</th><th>Matriculation</th><th>Submitted</th><th>Approval</th><th>Actions</th></tr></thead>
            <tbody>
            <?php while ($r = $res->fetch_assoc()): ?>
              <?php $link = getMatriculationLink($r); ?>
              <tr>
                <td><?php echo $r['id']; ?></td>
                <td><?php echo htmlspecialchars($r['firstname'] . ' ' . $r['middlename'] . ' ' . $r['lastname']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($r['student_status'])); ?></td>
                <td><?php echo htmlspecialchars($r['course'] . ' / ' . $r['year_level']); ?></td>
                <td><?php echo htmlspecialchars($r['email']); ?></td>
                <td><?php echo htmlspecialchars($r['cellphone']); ?></td>
                <td><?php echo htmlspecialchars($link['label']); ?></td>
                <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($r['approval_status'] ?? 'pending')); ?></td>
                <td>
                  <?php if ($link['url'] !== '#'): ?>
                    <span class="small"><?php echo htmlspecialchars($link['label']); ?></span>
                  <?php else: ?>
                    <span class="small" style="background:#f3f4f6;color:#555;">N/A</span>
                  <?php endif; ?>
                  <a class="small" href="?<?php echo http_build_query(['q' => $q, 'course' => $course, 'year' => $year, 'status' => $status, 'preview_token' => $r['token']]); ?>">Preview</a>
                  <a class="small" href="../edit_subjects.php?token=<?php echo urlencode($r['token']); ?>" target="_blank">Edit Subjects</a>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <?php if (!empty($preview_token)): ?>
          <?php
            $preview_stmt = $conn->prepare('SELECT id, surname, firstname, middlename, lastname, course, year_level, student_status, registration_fee, tuition_fee, lab_fee, misc_fee, upon_registration, prelim_fee, midterm_fee, semi_final_fee, final_fee, bank_account_number, created_at, matriculation_subjects FROM students WHERE token = ? LIMIT 1');
            $preview_stmt->bind_param('s', $preview_token);
            $preview_stmt->execute();
            $preview_res = $preview_stmt->get_result();
            $preview_student = $preview_res->fetch_assoc();
            $preview_stmt->close();
            if ($preview_student && !empty($preview_student['matriculation_subjects'])) {
                $decodedSubjects = json_decode($preview_student['matriculation_subjects'], true);
                if (is_array($decodedSubjects) && count($decodedSubjects) > 0) {
                    $subjects = $decodedSubjects;
                }
            }
          ?>
          <?php if ($preview_student): ?>
            <div class="table-panel" style="margin-top:24px;">
              <h3>Matriculation Preview</h3>
              <?php
                $token = $preview_token;
                $total_units = array_sum(array_column($subjects, 'units'));
                $registration_date = date('F d, Y', strtotime($preview_student['created_at']));
                $school_name = 'Computer Communication Development Institute';
                $school_address = 'Rizal St., Bitan-o, Sorsogon, Philippines';
                $current_semester = '2nd Sem';
                $current_school_year = '2025-2026';
                $fees = [
                    'registration' => $preview_student['registration_fee'] > 0 ? (float)$preview_student['registration_fee'] : 5400.00,
                    'tuition' => $preview_student['tuition_fee'] > 0 ? (float)$preview_student['tuition_fee'] : 3912.00,
                    'lab' => $preview_student['lab_fee'] > 0 ? (float)$preview_student['lab_fee'] : 0.00,
                    'misc' => $preview_student['misc_fee'] > 0 ? (float)$preview_student['misc_fee'] : 0.00,
                ];
                $total_fees = array_sum($fees);
                $payment_schedule = [
                    'upon_registration' => $preview_student['upon_registration'] > 0 ? (float)$preview_student['upon_registration'] : 4700.00,
                    'prelim' => $preview_student['prelim_fee'] > 0 ? (float)$preview_student['prelim_fee'] : 2811.00,
                    'midterm' => $preview_student['midterm_fee'] > 0 ? (float)$preview_student['midterm_fee'] : 2811.00,
                    'semi_final' => $preview_student['semi_final_fee'] > 0 ? (float)$preview_student['semi_final_fee'] : 2811.00,
                    'final' => $preview_student['final_fee'] > 0 ? (float)$preview_student['final_fee'] : 1405.80,
                ];
                $bank_account_number = !empty($preview_student['bank_account_number']) ? $preview_student['bank_account_number'] : '1234567890123';
                $matric_status = strtolower($preview_student['student_status']);
                if ($matric_status === 'iregular') {
                    $matric_label = 'Iregular';
                } else {
                    $matric_label = ucfirst($matric_status);
                }
              ?>
              <form method="post" action="matriculation.php?preview_token=<?php echo urlencode($preview_token); ?>">
                <input type="hidden" name="preview_token" value="<?php echo htmlspecialchars($preview_token); ?>">
              <div style="background:#fff;padding:24px;border:1px solid #ddd;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:20px;">
                  <div style="display:flex;align-items:center;gap:16px;">
                    <?php if (file_exists(__DIR__ . '/../logo.png')): ?>
                        <img src="../logo.png" alt="School Logo" style="width:72px;height:72px;object-fit:contain;border-radius:12px;background:#fff;padding:8px;box-shadow:0 2px 8px rgba(0,0,0,0.08);" />
                    <?php else: ?>
                        <div style="width:72px;height:72px;border-radius:50%;background:#0047b3;color:#fff;display:flex;align-items:center;justify-content:center;font-size:42px;font-weight:bold;position:relative;">
                          <span style="position:absolute;top:42%;right:18%;font-size:24px;color:#ff1f1f;">i</span>
                          <span>C</span>
                        </div>
                    <?php endif; ?>
                    <div>
                      <h3 style="margin:0 0 8px;font-size:18px;">CERTIFICATE OF MATRICULATION</h3>
                      <p style="margin:2px 0;font-size:14px;"><?php echo htmlspecialchars($school_name); ?></p>
                      <p style="margin:2px 0;font-size:14px;"><?php echo htmlspecialchars($school_address); ?></p>
                    </div>
                  </div>
                  <div style="text-align:right;display:flex;flex-direction:column;gap:8px;">
                    <div><strong>Semester:</strong> <?php echo htmlspecialchars($current_semester); ?></div>
                    <div><strong>School Year:</strong> <?php echo htmlspecialchars($current_school_year); ?></div>
                    <div><strong>Date:</strong> <?php echo htmlspecialchars($registration_date); ?></div>
                  </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:20px;">
                  <div style="border:1px solid #000;padding:12px;">
                    <strong>Student Name</strong>
                    <div style="margin-top:8px;"><?php echo htmlspecialchars(trim($preview_student['surname'] . ' ' . $preview_student['firstname'] . ' ' . $preview_student['middlename'])); ?></div>
                  </div>
                  <div style="border:1px solid #000;padding:12px;">
                    <strong>Course & Year</strong>
                    <div style="margin-top:8px;"><?php echo htmlspecialchars($preview_student['course'] . ' - ' . $preview_student['year_level']); ?></div>
                  </div>
                  <div style="border:1px solid #000;padding:12px;">
                    <strong>Student Status</strong>
                    <div style="margin-top:8px;"><?php echo htmlspecialchars($matric_label); ?></div>
                  </div>
                </div>

                <h4 style="margin:0 0 10px;font-size:16px;">Registered Subjects</h4>
                <table style="width:100%;border-collapse:collapse;margin-top:12px;">
                  <thead>
                    <tr>
                      <th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Subject Code</th>
                      <th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Subject Title</th>
                      <th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Units</th>
                      <th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Time</th>
                      <th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Day</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($subjects as $subject): ?>
                    <tr>
                      <td style="border:1px solid #000;padding:8px;"><?php echo htmlspecialchars($subject['code']); ?></td>
                      <td style="border:1px solid #000;padding:8px;"><?php echo htmlspecialchars($subject['title']); ?></td>
                      <td style="border:1px solid #000;padding:8px;text-align:center;"><?php echo htmlspecialchars($subject['units']); ?></td>
                      <td style="border:1px solid #000;padding:8px;text-align:center;"><?php echo htmlspecialchars($subject['time']); ?></td>
                      <td style="border:1px solid #000;padding:8px;text-align:center;"><?php echo htmlspecialchars($subject['day']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                      <td colspan="2" style="border:1px solid #000;padding:8px;font-weight:bold;">Total Units</td>
                      <td colspan="3" style="border:1px solid #000;padding:8px;text-align:center;font-weight:bold;"><?php echo htmlspecialchars($total_units); ?></td>
                    </tr>
                  </tbody>
                </table>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:24px;">
                  <div>
                    <h4 style="margin:0 0 10px;font-size:15px;">Fee Summary</h4>
                    <table style="width:100%;border-collapse:collapse;">
                      <tbody>
                        <tr><th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Fee</th><th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Amount</th></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Registration</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="registration_fee" value="<?php echo htmlspecialchars($fees['registration']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Tuition</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="tuition_fee" value="<?php echo htmlspecialchars($fees['tuition']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Lab</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="lab_fee" value="<?php echo htmlspecialchars($fees['lab']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Miscellaneous</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="misc_fee" value="<?php echo htmlspecialchars($fees['misc']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr style="font-weight:bold;"><td style="border:1px solid #000;padding:8px;">Total Assessment Fee</td><td style="border:1px solid #000;padding:8px;text-align:center;">₱<?php echo number_format($total_fees, 2); ?></td></tr>
                      </tbody>
                    </table>
                  </div>
                  <div>
                    <h4 style="margin:0 0 10px;font-size:15px;">Terms of Payment</h4>
                    <table style="width:100%;border-collapse:collapse;">
                      <tbody>
                        <tr><th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Due</th><th style="border:1px solid #000;padding:8px;background:#e5e7eb;">Amount</th></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Upon Registration</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="upon_registration" value="<?php echo htmlspecialchars($payment_schedule['upon_registration']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Prelim</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="prelim_fee" value="<?php echo htmlspecialchars($payment_schedule['prelim']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Midterm</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="midterm_fee" value="<?php echo htmlspecialchars($payment_schedule['midterm']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Semi-final</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="semi_final_fee" value="<?php echo htmlspecialchars($payment_schedule['semi_final']); ?>" style="width:110px;text-align:right;"></td></tr>
                        <tr><td style="border:1px solid #000;padding:8px;">Final</td><td style="border:1px solid #000;padding:8px;text-align:center;"><input type="number" step="0.01" name="final_fee" value="<?php echo htmlspecialchars($payment_schedule['final']); ?>" style="width:110px;text-align:right;"></td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div style="margin-top:20px;padding:16px;border:1px solid #f59e0b;background:#fffbeb;border-radius:8px;">
                  <label for="bank_account_number" style="display:block;font-weight:700;margin-bottom:6px;color:#92400e;">Bank Account Number</label>
                  <input type="text" id="bank_account_number" name="bank_account_number" value="<?php echo htmlspecialchars($bank_account_number); ?>" style="width:240px;padding:10px;border:1px solid #f59e0b;border-radius:6px;background:#fff8dc;color:#92400e;">
                  <div style="margin-top:8px;font-size:13px;color:#7c3aed;">Admins can edit this bank account number for the student.</div>
                </div>
                <div style="margin-top:18px; text-align:right;">
                  <button type="submit" style="padding:10px 16px;background:#2563eb;color:#fff;border:none;border-radius:5px;cursor:pointer;">Save Fee Summary</button>
                </div>
              </form>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:30px;">
                  <div style="text-align:center;">
                    <div style="border-bottom:1px solid #000;width:100%;height:32px;margin-bottom:6px;"></div>
                    <div>Student Signature</div>
                  </div>
                  <div style="text-align:center;">
                    <div style="border-bottom:1px solid #000;width:100%;height:32px;margin-bottom:6px;"></div>
                    <div>Approved by Registrar</div>
                  </div>
                </div>
              </div>
            </div>
          <?php else: ?>
            <div class="table-panel" style="margin-top:24px;">
              <p>No matriculation record found for preview token.</p>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>
</body>
</html>
