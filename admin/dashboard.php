<?php
session_start();
require_once __DIR__ . '/../database.php';
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

// Metrics
$totals = $conn->query('SELECT COUNT(*) AS total FROM students')->fetch_assoc();
$total_enroll = $totals['total'] ?? 0;
$thisMonth = $conn->query("SELECT COUNT(*) AS c FROM students WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())")->fetch_assoc();
$enroll_month = $thisMonth['c'] ?? 0;
$today = $conn->query("SELECT COUNT(*) AS c FROM students WHERE DATE(created_at)=CURDATE()")->fetch_assoc();
$enroll_today = $today['c'] ?? 0;
$total_courses = $conn->query('SELECT COUNT(*) AS c FROM courses')->fetch_assoc()['c'] ?? 0;

// Pending / Approved (simple heuristic: pending if no payment_proof)
$pending = $conn->query("SELECT COUNT(*) AS c FROM students WHERE payment_proof IS NULL OR payment_proof=''")->fetch_assoc()['c'] ?? 0;
$approved = max(0, $total_enroll - $pending);

// Payments total placeholder (no numeric amount stored); we'll show count as proxy
$payments_count = $conn->query('SELECT COUNT(*) AS c FROM students WHERE payment_ref IS NOT NULL AND payment_ref <> ""')->fetch_assoc()['c'] ?? 0;

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>

  <header class="top-header">
    <div class="top-left">
      <div class="logo"> <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg>
        <span class="logo-text">Online Enrollment System <small>- Admin Dashboard</small></span>
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
        <span class="brand-icon" aria-hidden="true">
          <!-- simple graduation cap icon -->
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg>
        </span>
        <span class="brand-label">Online Enrollment<span class="dot">Sys</span></span>
      </div>
      <nav>
        <a href="dashboard.php" class="active"><span class="icon"> 
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9z" fill="#fff"/></svg>
        </span><span class="label">Dashboard</span></a>
        <a href="students.php"><span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v3h20v-3c0-3.33-6.67-5-10-5z" fill="#fff"/></svg>
        </span><span class="label">Students</span></a>
        <a href="matriculation.php"><span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm3 3v4h10v-4H7z" fill="#fff"/></svg>
        </span><span class="label">Matriculation</span></a>
        <a href="courses.php"><span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2z" fill="#fff"/></svg>
        </span><span class="label">Courses</span></a>
        <a href="export.php"><span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2v10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span><span class="label">Export</span></a>
        <a href="logout.php"><span class="icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 17l5-5-5-5M21 12H9" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span><span class="label">Logout</span></a>
      </nav>
    </aside>

    <main class="main">
      <header class="topbar">
        <div class="greeting">Good morning, <?php echo htmlspecialchars($_SESSION['admin_user']); ?>!</div>
        <div class="top-actions">
          <input class="search" placeholder="Quick search">
        </div>
      </header>

      <section class="cards-grid">
        <div class="stat-card blue">
          <div class="stat-icon">🏫</div>
          <div class="stat-body">
            <div class="stat-title">Total Students Enrolled</div>
            <div class="stat-value"><?php echo (int)$total_enroll; ?></div>
          </div>
        </div>

        <div class="stat-card orange">
          <div class="stat-icon">⏳</div>
          <div class="stat-body">
            <div class="stat-title">Pending Enrollments</div>
            <div class="stat-value"><?php echo (int)$pending; ?></div>
          </div>
        </div>

        <div class="stat-card green">
          <div class="stat-icon">✅</div>
          <div class="stat-body">
            <div class="stat-title">Approved Enrollments</div>
            <div class="stat-value"><?php echo (int)$approved; ?></div>
          </div>
        </div>

        <div class="stat-card purple">
          <div class="stat-icon">💰</div>
          <div class="stat-body">
            <div class="stat-title">Payments Received (count)</div>
            <div class="stat-value"><?php echo (int)$payments_count; ?></div>
          </div>
        </div>
      </section>

      <div class="grid-main">
        <div class="chart-card">
          <div style="display:flex;gap:14px">
            <div style="flex:1">
              <h4>Enrollment Overview</h4>
              <div class="chart-placeholder" id="bar-chart">Bar chart placeholder</div>
            </div>
            <div style="width:320px">
              <h4>Enrollments by Course</h4>
                      <div class="chart-placeholder" id="pie-chart">Pie chart placeholder</div>
            </div>
          </div>
        </div>

        <div class="chart-card recent-table">
          <h4>Recent Enrollments</h4>
          <table class="table">
            <thead><tr><th>Student</th><th>Course</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php
              $rs = $conn->query('SELECT firstname, lastname, course, payment_proof, created_at FROM students ORDER BY created_at DESC LIMIT 5');
              while($r = $rs->fetch_assoc()):
                $status = (!empty($r['payment_proof'])) ? 'Approved' : 'Pending';
            ?>
              <tr>
                <td><?php echo htmlspecialchars($r['firstname'].' '.$r['lastname']); ?></td>
                <td><?php echo htmlspecialchars($r['course']); ?></td>
                <td style="color:<?php echo $status==='Approved' ? '#16a34a' : '#f97316'; ?>"><?php echo $status; ?></td>
                <td><?php echo date('M d, Y', strtotime($r['created_at'])); ?></td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
          <p style="text-align:center;margin-top:8px"><a class="button" href="students.php">View All</a></p>
        </div>
      </div>

      <div class="mini-row">
        <div class="list-card">
          <h4>Uploaded Payment Receipts</h4>
          <ul class="receipt-list">
          <?php
            $rp = $conn->query("SELECT payment_proof FROM students WHERE payment_proof IS NOT NULL AND payment_proof<>'' ORDER BY created_at DESC LIMIT 5");
            if ($rp->num_rows === 0) echo '<li>No receipts uploaded yet.</li>';
            while($rr = $rp->fetch_assoc()):
          ?>
            <li>
              <span><?php echo htmlspecialchars($rr['payment_proof']); ?></span>
              <a class="button" href="../uploads/<?php echo rawurlencode($rr['payment_proof']); ?>" target="_blank">View</a>
            </li>
          <?php endwhile; ?>
          </ul>
        </div>

        <div class="list-card courses-list">
          <h4>Popular Courses</h4>
          <?php
            $stats = $conn->query('SELECT course, COUNT(*) AS c FROM students GROUP BY course ORDER BY c DESC LIMIT 4');
            $total = $total_enroll ?: 1;
            while($s = $stats->fetch_assoc()):
              $pct = round(($s['c'] / $total) * 100);
          ?>
            <div class="course">
              <div style="flex:1"><strong><?php echo htmlspecialchars($s['course']); ?></strong></div>
              <div style="width:60%">
                <div class="progress"><i style="width:<?php echo $pct; ?>%;background:#3b82f6"></i></div>
              </div>
              <div style="width:60px;text-align:right"><?php echo $pct; ?>%</div>
            </div>
          <?php endwhile; ?>
        </div>
      </div>
      
      <!-- Chart.js -->
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <?php
        // Prepare chart data: last 7 days
        $labels = [];
        $counts = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('D', strtotime($d));
            $c = $conn->query("SELECT COUNT(*) AS c FROM students WHERE DATE(created_at) = '" . $conn->real_escape_string($d) . "'")->fetch_assoc()['c'];
            $counts[] = (int)$c;
        }

        // Pie data: enrollments by course (top 6)
        $plabels = [];
        $pcounts = [];
        $prs = $conn->query('SELECT course, COUNT(*) AS c FROM students GROUP BY course ORDER BY c DESC LIMIT 6');
        while ($pr = $prs->fetch_assoc()) { $plabels[] = $pr['course']; $pcounts[] = (int)$pr['c']; }
      ?>
      <script>
        const barLabels = <?php echo json_encode($labels); ?>;
        const barData = <?php echo json_encode($counts); ?>;
        const pieLabels = <?php echo json_encode($plabels); ?>;
        const pieData = <?php echo json_encode($pcounts); ?>;

        // Render bar chart
        const barCtx = document.createElement('canvas');
        document.getElementById('bar-chart').innerHTML='';
        document.getElementById('bar-chart').appendChild(barCtx);
        new Chart(barCtx.getContext('2d'), {
          type: 'bar',
          data: { labels: barLabels, datasets: [{ label: 'Enrollments', data: barData, backgroundColor: '#3b82f6' }] },
          options: { responsive: true, maintainAspectRatio:false }
        });

        // Render pie chart
        const pieCtx = document.createElement('canvas');
        document.getElementById('pie-chart').innerHTML='';
        document.getElementById('pie-chart').appendChild(pieCtx);
        new Chart(pieCtx.getContext('2d'), {
          type: 'doughnut',
          data: { labels: pieLabels, datasets: [{ data: pieData, backgroundColor: ['#3b82f6','#34d399','#f97316','#a78bfa','#60a5fa','#f59e0b'] }] },
          options: { responsive: true, maintainAspectRatio:false }
        });
      </script>

    </main>
  </div>
</body>
</html>

