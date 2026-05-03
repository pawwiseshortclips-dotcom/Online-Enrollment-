<?php
require_once 'database.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    http_response_code(400);
    die('Invalid token.');
}

$stmt = $conn->prepare("SELECT id, firstname, lastname, course, student_status FROM students WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    http_response_code(404);
    die('Enrollment not found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Success</title>
    <style>
        body{margin:0;background:#e7e7e7;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,sans-serif}
        .container{max-width:600px;margin:60px auto;padding:30px;background:#fff;border-radius:8px;box-shadow:0 3px 12px rgba(0,0,0,.1);text-align:center}
        h1{color:#28a745;font-size:28px;margin:0 0 15px}
        p{color:#555;line-height:1.6;margin:10px 0}
        .token-box{background:#f9f9f9;border:1px solid #ddd;border-radius:5px;padding:15px;margin:20px 0;font-family:monospace;word-break:break-all}
        .btn{display:inline-block;margin-top:20px;padding:12px 24px;background:#1d4ed8;color:#fff;text-decoration:none;border-radius:5px;font-weight:600}
        .btn:hover{background:#1e40af}
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Enrollment Successful</h1>
        <p>Thank you for enrolling! Your enrollment has been recorded.</p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($student['firstname'] . ' ' . ($student['lastname'] ?? '')); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($student['course']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($student['student_status']); ?></p>
        <div class="token-box">
            <strong>Your Enrollment Token:</strong><br>
            <?php echo htmlspecialchars($token); ?>
        </div>
        <p>Save this token to view your enrollment details later.</p>
        <a href="index.php" class="btn">Enroll Another Student</a>
    </div>
</body>
</html>