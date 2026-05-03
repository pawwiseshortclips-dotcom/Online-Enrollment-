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

if ($enrollment['student_status'] !== 'regular') {
    http_response_code(403);
    die('This enrollment is not marked as regular.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Regular Student - Enrollment Confirmation</title>
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
            color: #0066cc;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-box {
            background: #e8f4f8;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .button:hover {
            background: #0052a3;
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
        <h1>✓ Regular Student</h1>
        
        <div style="text-align: center;">
            <span class="status-badge">REGULAR ENROLLMENT CONFIRMED</span>
        </div>

        <div class="info-box">
            <h2>Enrollment Details</h2>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($enrollment['firstname'] . ' ' . $enrollment['lastname']); ?></p>
            <p><strong>Course:</strong> <?php echo htmlspecialchars($enrollment['course']); ?></p>
            <p><strong>Status:</strong> Regular</p>
            <p><strong>Token:</strong> <code><?php echo htmlspecialchars($token); ?></code></p>
        </div>

        <div class="info-box">
            <h3>Next Steps for Regular Students:</h3>
            <ul>
                <li>Proceed to the Registrar's office for final verification</li>
                <li>Complete payment if not yet done</li>
                <li>Receive your official ID and enrollment confirmation</li>
                <li>Attend orientation on the scheduled date</li>
            </ul>
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
