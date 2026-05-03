<?php
require_once 'database.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    header('Location: index.php');
    exit;
}

// Fetch enrollment data
$stmt = $conn->prepare("SELECT id, firstname, lastname, course, student_status FROM students WHERE token = ? LIMIT 1");
if (!$stmt) {
    die('Query error: ' . $conn->error);
}
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$enrollment = $result->fetch_assoc();
$stmt->close();

if (!$enrollment) {
    http_response_code(404);
    die('Enrollment not found.');
}

if (!in_array($enrollment['student_status'], ['iregular', 'eregular'], true)) {
    http_response_code(403);
    die('This enrollment is not marked as iregular.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iregular Student - Enrollment Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #ff6600;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            background: #ff6600;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-box {
            background: #fff3e0;
            border-left: 4px solid #ff6600;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .button:hover {
            background: #e55a00;
        }
        .button-secondary {
            background: #6c757d;
        }
        .button-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Iregular Student</h1>
        
        <div style="text-align: center;">
            <span class="status-badge">IREGULAR ENROLLMENT CONFIRMED</span>
        </div>

        <div class="info-box">
            <h2>Enrollment Details</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($enrollment['firstname'] . ' ' . $enrollment['lastname']); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($enrollment['course']); ?></p>
            <p><strong>Status:</strong> Iregular</p>
            <p><strong>Token:</strong> <code><?php echo htmlspecialchars($token); ?></code></p>
        </div>

        <div class="info-box">
            <h2>Fees</h2>
            <p><em>Insert fee amounts here (admin use only):</em></p>
            <div style="border:1px solid #bbb; background:#fff; color:#000; padding:12px; min-height:80px; font-family:monospace;">
                ________
                <br>________
                <br>________
            </div>
        </div>

        <div class="info-box">
            <h2>Terms of Payment</h2>
            <p><em>Insert term of payment details here (admin use only):</em></p>
            <div style="border:1px solid #bbb; background:#fff; color:#000; padding:12px; min-height:80px; font-family:monospace;">
                ________
                <br>________
                <br>________
            </div>
        </div>

        <div class="info-box">
            <h3>Next Steps for Iregular Students:</h3>
            <ul>
                <li>Access the online learning portal using your student ID</li>
                <li>Complete your online orientation</li>
                <li>Ensure you have stable internet connection for classes</li>
                <li>Check your email for course schedules and links</li>
                <li>Contact support if you experience technical issues</li>
            </ul>
        </div>

        <div class="info-box" style="background: #fff9e6; border-left-color: #ffc107;">
            <strong>ℹ️ Important:</strong> Iregular enrollment requires online participation. 
            Ensure you meet all system requirements and have registered your learning device.
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="receipt.php?token=<?php echo urlencode($token); ?>" class="button">View Receipt</a>
            <a href="receipt_pdf.php?token=<?php echo urlencode($token); ?>" class="button">Download PDF</a>
            <a href="com.php?token=<?php echo urlencode($token); ?>" class="button">View Certificate of Matriculation</a>
            <a href="index.php" class="button button-secondary">New Enrollment</a>
        </div>
    </div>
</body>
</html>
