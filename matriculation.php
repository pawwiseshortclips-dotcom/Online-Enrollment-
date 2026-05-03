<?php
require_once 'database.php';
require_once 'subjects.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT id, surname, firstname, middlename, lastname, course, year_level, student_status, created_at FROM students WHERE token = ? LIMIT 1");
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

$total_units = array_sum(array_column($subjects, 'units'));
$registration_date = date('F d, Y', strtotime($enrollment['created_at']));
$school_name = 'Computer Communication Development Institute';
$school_address = 'Some Road, Branch, City';
$current_semester = '2nd Sem';
$current_school_year = '2025-2026';

$fees = [
    'registration' => 5400.00,
    'tuition' => 3912.00,
    'lab' => 0.00,
    'misc' => 0.00,
];
$total_fees = array_sum($fees);
$payment_schedule = [
    'upon_registration' => 4700.00,
    'prelim' => 2811.00,
    'midterm' => 2811.00,
    'semi_final' => 2811.00,
    'final' => 1405.80,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate of Matriculation</title>
    <style>
        body { font-family: 'Arial', sans-serif; background: #f3f4f6; margin: 0; padding: 20px; }
        .page { max-width: 900px; margin: 0 auto; background: white; padding: 24px; border: 1px solid #ccc; }
        .header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; gap: 20px; }
        .header-left { display: flex; align-items: center; gap: 16px; }
        .header-left h1 { margin: 0; font-size: 20px; letter-spacing: .08em; }
        .header-left p { margin: 4px 0; font-size: 14px; }
        .header-right { text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
        .logo-block { display: flex; align-items: center; justify-content: flex-start; gap: 12px; }
        .logo-img { width: 72px; height: 72px; object-fit: contain; border-radius: 12px; background: #fff; }
        .logo-circle { position: relative; width: 72px; height: 72px; border-radius: 50%; background: #0047b3; display: flex; align-items: center; justify-content: center; color: #fff; font-family: 'Arial', sans-serif; }
        .logo-circle .logo-c { font-size: 42px; line-height: 1; font-weight: bold; }
        .logo-circle .logo-i { position: absolute; top: 44%; right: 18%; transform: translate(0, -50%); font-size: 24px; color: #ff1f1f; font-weight: bold; }
        .logo-label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #1f2937; }
        .header-right p { margin: 2px 0; font-size: 14px; }
        .top-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
        .info-box { border: 1px solid #000; padding: 12px; }
        .info-box strong { display: block; margin-bottom: 6px; font-size: 13px; }
        .subjects-table, .fees-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .subjects-table th, .subjects-table td, .fees-table th, .fees-table td { border: 1px solid #000; padding: 8px; font-size: 13px; }
        .subjects-table th, .fees-table th { background: #e5e7eb; }
        .subjects-table td:nth-child(3), .subjects-table td:nth-child(4), .subjects-table td:nth-child(5), .fees-table td { text-align: center; }
        .footer { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; }
        .signature { text-align: center; margin-top: 40px; }
        .signature-line { border-bottom: 1px solid #000; width: 100%; height: 32px; margin-bottom: 6px; }
        .print-button { text-align: right; margin-bottom: 20px; }
        .print-button button { padding: 10px 18px; background: #1d4ed8; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        .print-button button:hover { background: #1e40af; }
        @media print { .print-button { display: none; } }
    </style>
</head>
<body>
    <div class="page">
        <div class="print-button">
            <button onclick="window.print()">Print Matriculation</button>
            <a href="edit_subjects.php?token=<?php echo urlencode($token); ?>" style="margin-left:12px; display:inline-block; padding:10px 18px; background:#10b981; color:#fff; border-radius:5px; text-decoration:none;">Edit Subjects</a>
        </div>
        <div class="header">
            <div class="header-left">
                <div class="logo-block">
                    <?php if (file_exists(__DIR__ . '/logo.png')): ?>
                        <img src="logo.png" alt="CCD Institute Logo" class="logo-img">
                    <?php else: ?>
                        <div class="logo-circle">
                            <span class="logo-c">C</span>
                            <span class="logo-i">i</span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1>CERTIFICATE OF MATRICULATION</h1>
                        <p><?php echo htmlspecialchars($school_name); ?></p>
                        <p><?php echo htmlspecialchars($school_address); ?></p>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <p><strong>Semester:</strong> <?php echo htmlspecialchars($current_semester); ?></p>
                <p><strong>School Year:</strong> <?php echo htmlspecialchars($current_school_year); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($registration_date); ?></p>
            </div>
        </div>

        <div class="top-row">
            <div class="info-box">
                <strong>Student Name</strong>
                <?php echo htmlspecialchars(trim($enrollment['surname'] . ' ' . $enrollment['firstname'] . ' ' . $enrollment['middlename'])); ?>
            </div>
            <div class="info-box">
                <strong>Course & Year</strong>
                <?php echo htmlspecialchars($enrollment['course'] . ' - ' . $enrollment['year_level']); ?>
            </div>
            <div class="info-box">
                <strong>Student Status</strong>
                <?php echo htmlspecialchars(ucfirst(str_replace('iregular', 'Iregular', str_replace('eregular', 'iregular', $enrollment['student_status'])))); ?>
            </div>
        </div>

        <h2 style="margin:0 0 10px; font-size:16px;">Registered Subjects</h2>
        <table class="subjects-table">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Title</th>
                    <th>Units</th>
                    <th>Time</th>
                    <th>Day</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td><?php echo htmlspecialchars($subject['code']); ?></td>
                    <td><?php echo htmlspecialchars($subject['title']); ?></td>
                    <td><?php echo htmlspecialchars($subject['units']); ?></td>
                    <td><?php echo htmlspecialchars($subject['time']); ?></td>
                    <td><?php echo htmlspecialchars($subject['day']); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="2" style="font-weight:bold;">Total Units</td>
                    <td colspan="3" style="font-weight:bold; text-align:center;"><?php echo htmlspecialchars($total_units); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <div>
                <h3 style="margin:0 0 10px; font-size:15px;">Fee Summary</h3>
                <table class="fees-table">
                    <tbody>
                        <tr><th>Fee</th><th>Amount</th></tr>
                        <tr><td>Registration</td><td>₱<?php echo number_format($fees['registration'], 2); ?></td></tr>
                        <tr><td>Tuition</td><td>₱<?php echo number_format($fees['tuition'], 2); ?></td></tr>
                        <tr><td>Lab</td><td>₱<?php echo number_format($fees['lab'], 2); ?></td></tr>
                        <tr><td>Miscellaneous</td><td>₱<?php echo number_format($fees['misc'], 2); ?></td></tr>
                        <tr style="font-weight:bold;"><td>Total Assessment Fee</td><td>₱<?php echo number_format($total_fees, 2); ?></td></tr>
                    </tbody>
                </table>
            </div>
            <div>
                <h3 style="margin:0 0 10px; font-size:15px;">Terms of Payment</h3>
                <table class="fees-table">
                    <tbody>
                        <tr><th>Due</th><th>Amount</th></tr>
                        <tr><td>Upon Registration</td><td>₱<?php echo number_format($payment_schedule['upon_registration'], 2); ?></td></tr>
                        <tr><td>Prelim</td><td>₱<?php echo number_format($payment_schedule['prelim'], 2); ?></td></tr>
                        <tr><td>Midterm</td><td>₱<?php echo number_format($payment_schedule['midterm'], 2); ?></td></tr>
                        <tr><td>Semi-final</td><td>₱<?php echo number_format($payment_schedule['semi_final'], 2); ?></td></tr>
                        <tr><td>Final</td><td>₱<?php echo number_format($payment_schedule['final'], 2); ?></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="signature">
            <div class="signature-line"></div>
            <div>Student Signature</div>
        </div>
        <div class="signature" style="margin-top:30px;">
            <div class="signature-line"></div>
            <div>Approved by Registrar</div>
        </div>
    </div>
</body>
</html>
