<?php
require_once __DIR__ . '/database.php';

$subjectFile = __DIR__ . '/subjects.json';
$token = trim($_GET['token'] ?? '');
$subjects = [];
$student = null;
$editingStudent = false;
$saveError = '';

if ($token !== '') {
    $stmt = $conn->prepare("SELECT matriculation_subjects FROM students WHERE token = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();
    }
    if ($student) {
        $editingStudent = true;
        if (!empty($student['matriculation_subjects'])) {
            $decoded = json_decode($student['matriculation_subjects'], true);
            if (is_array($decoded)) {
                $subjects = $decoded;
            }
        }
    }
}

if (!$editingStudent && file_exists($subjectFile)) {
    $json = file_get_contents($subjectFile);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $subjects = $decoded;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codes = $_POST['code'] ?? [];
    $titles = $_POST['title'] ?? [];
    $units = $_POST['units'] ?? [];
    $times = $_POST['time'] ?? [];
    $days = $_POST['day'] ?? [];

    $newSubjects = [];
    for ($i = 0; $i < count($codes); $i++) {
        $code = trim($codes[$i]);
        $title = trim($titles[$i]);
        $unit = trim($units[$i]);
        $time = trim($times[$i]);
        $day = trim($days[$i]);

        if ($code === '' && $title === '') {
            continue;
        }

        $newSubjects[] = [
            'code' => $code,
            'title' => $title,
            'units' => is_numeric($unit) ? (int) $unit : 0,
            'time' => $time,
            'day' => $day,
        ];
    }

    if ($token !== '' && $editingStudent) {
        $jsonSubjects = json_encode($newSubjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $stmt = $conn->prepare("UPDATE students SET matriculation_subjects = ? WHERE token = ?");
        if ($stmt) {
            $stmt->bind_param('ss', $jsonSubjects, $token);
            $stmt->execute();
            $stmt->close();
            $subjects = $newSubjects;
            $saved = true;
        } else {
            $saveError = 'Unable to save subjects to student record.';
        }
    } else {
        if ($token !== '' && !$editingStudent) {
            $saveError = 'Student not found for the provided token.';
        } else {
            file_put_contents($subjectFile, json_encode($newSubjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $subjects = $newSubjects;
            $saved = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Matriculation Subjects</title>
    <style>
        body{margin:0;background:#f3f4f6;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif;color:#111}
        .container{max-width:1000px;margin:30px auto;padding:24px;background:#fff;border:1px solid #ddd;border-radius:8px}
        h1{margin:0 0 16px;font-size:24px}
        .note{margin:0 0 16px;color:#334155}
        .subject-table{width:100%;border-collapse:collapse}
        .subject-table th,.subject-table td{border:1px solid #cbd5e1;padding:10px;text-align:left}
        .subject-table th{background:#f8fafc;font-weight:700}
        .subject-table input{width:100%;padding:8px;border:1px solid #cbd5e1;border-radius:4px}
        .controls{display:flex;justify-content:space-between;align-items:center;margin:20px 0}
        .controls button{padding:10px 16px;border:none;border-radius:6px;color:#fff;cursor:pointer}
        .controls .add{background:#2563eb}
        .controls .save{background:#10b981}
        .message{margin:0 0 16px;padding:12px;background:#dcfce7;border:1px solid #bbf7d0;color:#166534;border-radius:6px}
        .footer{margin-top:20px;display:flex;gap:12px}
        .footer a{padding:10px 16px;text-decoration:none;color:#fff;background:#475569;border-radius:6px}
        .footer a.primary{background:#0f172a}
    </style>
    <script>
        function addRow() {
            const table = document.getElementById('subjects-table');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="code[]" value=""></td>
                <td><input type="text" name="title[]" value=""></td>
                <td><input type="number" name="units[]" min="0" value="0"></td>
                <td><input type="text" name="time[]" value=""></td>
                <td><input type="text" name="day[]" value=""></td>
                <td><button type="button" onclick="removeRow(this)">Remove</button></td>
            `;
            table.querySelector('tbody').appendChild(row);
        }
        function removeRow(button) {
            const row = button.closest('tr');
            row.remove();
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Edit Matriculation Subjects<?php if ($token !== ''): ?> for <?php echo htmlspecialchars($token); ?><?php endif; ?></h1>
        <p class="note">Change the subjects that appear on the matriculation form. Add, remove, or update subject code, title, units, time, and day. Click Save when finished.</p>
        <?php if (!empty($saveError)): ?>
            <div class="message" style="background:#fee2e2;border-color:#fecaca;color:#991b1b;"><?php echo htmlspecialchars($saveError); ?></div>
        <?php endif; ?>
        <?php if (!empty($saved)): ?>
            <div class="message">Subjects saved successfully.</div>
        <?php endif; ?>

        <form method="post">
            <table class="subject-table" id="subjects-table">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Title</th>
                        <th>Units</th>
                        <th>Time</th>
                        <th>Day</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($subjects) === 0): ?>
                        <tr>
                            <td><input type="text" name="code[]" value=""></td>
                            <td><input type="text" name="title[]" value=""></td>
                            <td><input type="number" name="units[]" min="0" value="0"></td>
                            <td><input type="text" name="time[]" value=""></td>
                            <td><input type="text" name="day[]" value=""></td>
                            <td><button type="button" onclick="removeRow(this)">Remove</button></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><input type="text" name="code[]" value="<?php echo htmlspecialchars($subject['code']); ?>"></td>
                                <td><input type="text" name="title[]" value="<?php echo htmlspecialchars($subject['title']); ?>"></td>
                                <td><input type="number" name="units[]" min="0" value="<?php echo htmlspecialchars($subject['units']); ?>"></td>
                                <td><input type="text" name="time[]" value="<?php echo htmlspecialchars($subject['time']); ?>"></td>
                                <td><input type="text" name="day[]" value="<?php echo htmlspecialchars($subject['day']); ?>"></td>
                                <td><button type="button" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="controls">
                <button type="button" class="add" onclick="addRow()">Add Subject</button>
                <button type="submit" class="save">Save Subjects</button>
            </div>
        </form>
        <div class="footer">
            <a class="primary" href="matriculation.php?token=<?php echo urlencode($token); ?>">Back to Matriculation</a>
            <a href="subjects.json" target="_blank">View subjects.json</a>
        </div>
    </div>
</body>
</html>
