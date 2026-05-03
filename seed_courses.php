<?php
/**
 * Seed the `courses` table with the CCDI course list.
 * Run this once via browser: http://localhost/Enrollment-system/seed_courses.php
 */
require_once 'database.php';

$courses = [
    'Bachelor of Science in Information Systems (BSIS)',
    'Bachelor of Science in Information Technology (BSIT)',
    'Bachelor of Science in Computer Science (BSCS)',
    'Associate in Computer Technology (ACT)',
    'Bachelor of Engineering Technology (BET) Major in Electrical and Electronics',
    'Diploma in Software Development and Programming',
    'Diploma in Electronic and Computer Technology',
];

$inserted = [];
foreach ($courses as $title) {
    // check exists
    $stmt = $conn->prepare('SELECT id FROM courses WHERE title = ? LIMIT 1');
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $ins = $conn->prepare('INSERT INTO courses (code, title) VALUES (?, ?)');
        // simple code: use acronym if available
        $code = preg_replace('/[^A-Z]/', '', strtoupper($title));
        if (strlen($code) > 8) $code = substr($code,0,8);
        $ins->bind_param('ss', $code, $title);
        if ($ins->execute()) { $inserted[] = $title; }
        $ins->close();
    }
    $stmt->close();
}

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Seed Courses</title>
<link rel="stylesheet" href="style.css"></head><body>
<div class="container">
  <h2>Seed Courses</h2>
  <?php if (empty($inserted)): ?>
    <p>No new courses were added — they already exist.</p>
  <?php else: ?>
    <p>Added <?php echo count($inserted); ?> courses:</p>
    <ul>
      <?php foreach ($inserted as $c): ?>
        <li><?php echo htmlspecialchars($c); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <p><a class="button" href="index.php">Back to form</a></p>
</div>
</body></html>
