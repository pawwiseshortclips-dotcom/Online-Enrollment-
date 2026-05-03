<?php
require_once 'database.php';

$token = $_GET['token'] ?? '';
if ($token === '') {
    header('Location: index.php');
    exit;
}

// Fetch enrollment data
$stmt = $conn->prepare("SELECT id, firstname, lastname, middlename, course, year_level, student_status, created_at FROM students WHERE token = ? LIMIT 1");
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

// Sample subjects data (in a real system, this would come from a subjects table)
$subjects = [
    ['code' => 'IT101', 'title' => 'Introduction to Computing', 'units' => 3, 'time' => '8:00-9:30 AM', 'day' => 'MWF'],
    ['code' => 'IT102', 'title' => 'Programming Fundamentals', 'units' => 3, 'time' => '10:00-11:30 AM', 'day' => 'TTH'],
    ['code' => 'MATH101', 'title' => 'College Algebra', 'units' => 3, 'time' => '1:00-2:30 PM', 'day' => 'MWF'],
    ['code' => 'ENG101', 'title' => 'English Communication', 'units' => 3, 'time' => '3:00-4:30 PM', 'day' => 'TTH'],
    ['code' => 'PE101', 'title' => 'Physical Education', 'units' => 2, 'time' => '7:00-8:30 AM', 'day' => 'Sat'],
];

// Calculate total units
$total_units = array_sum(array_column($subjects, 'units'));

// Sample fees data
$fees = [
    'registration' => 500.00,
    'tuition' => 2500.00,
    'lab' => 300.00,
    'misc' => 200.00,
];
$total_fees = array_sum($fees);

// Payment schedule
$payment_schedule = [
    'upon_registration' => $total_fees * 0.3,
    'prelim' => $total_fees * 0.2,
    'midterm' => $total_fees * 0.2,
    'semi_final' => $total_fees * 0.15,
    'final' => $total_fees * 0.15,
];

// Current semester and school year (sample)
$current_semester = '1st Semester';
$current_school_year = '2026-2027';
$registration_date = date('F d, Y', strtotime($enrollment['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate of Matriculation</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .certificate {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
        }
        .student-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-item {
            display: flex;
        }
        .info-item label {
            font-weight: bold;
            width: 120px;
        }
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .subjects-table th, .subjects-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .subjects-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
        }
        .fees-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .fees-table {
            width: 100%;
            border-collapse: collapse;
        }
        .fees-table th, .fees-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .fees-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .status-section {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
            font-size: 14px;
        }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 40px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 100%;
            height: 30px;
            margin-bottom: 5px;
        }
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        .print-button button {
            padding: 10px 20px;
            font-size: 14px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-button button:hover {
            background: #0056b3;
        }
        @media print {
            .print-button {
                display: none;
            }
            body {
                padding: 0;
            }
            .certificate {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">🖨️ Print Certificate</button>
    </div>

    <div class="certificate">
        <div class="header">
            <h1>CERTIFICATE OF MATRICULATION</h1>
            <h2>School Name</h2>
            <h2>School Address</h2>
        </div>

        <div class="student-info">
            <div class="info-item">
                <label>Student Name:</label>
                <span><?php echo htmlspecialchars($enrollment['firstname'] . ' ' . $enrollment['middlename'] . ' ' . $enrollment['lastname']); ?></span>
            </div>
            <div class="info-item">
                <label>Course & Year:</label>
                <span><?php echo htmlspecialchars($enrollment['course'] . ' - ' . $enrollment['year_level']); ?></span>
            </div>
            <div class="info-item">
                <label>Semester:</label>
                <span><?php echo htmlspecialchars($current_semester); ?></span>
            </div>
            <div class="info-item">
                <label>School Year:</label>
                <span><?php echo htmlspecialchars($current_school_year); ?></span>
            </div>
            <div class="info-item">
                <label>Registration Date:</label>
                <span><?php echo htmlspecialchars($registration_date); ?></span>
            </div>
        </div>

        <h3>Enrolled Subjects</h3>
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
                <tr class="total-row">
                    <td colspan="2"><strong>Total Units</strong></td>
                    <td><strong><?php echo htmlspecialchars($total_units); ?></strong></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>

        <div class="fees-section">
            <div>
                <h3>Fees Breakdown</h3>
                <table class="fees-table">
                    <tbody>
                        <tr>
                            <td>Registration Fee</td>
                            <td>₱<?php echo number_format($fees['registration'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Tuition Fee</td>
                            <td>₱<?php echo number_format($fees['tuition'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Lab Fee</td>
                            <td>₱<?php echo number_format($fees['lab'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Miscellaneous Fee</td>
                            <td>₱<?php echo number_format($fees['misc'], 2); ?></td>
                        </tr>
                        <tr style="font-weight: bold;">
                            <td>Total Assessment Fee</td>
                            <td>₱<?php echo number_format($total_fees, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div>
                <h3>Payment Schedule</h3>
                <table class="fees-table">
                    <tbody>
                        <tr>
                            <td>Upon Registration</td>
                            <td>₱<?php echo number_format($payment_schedule['upon_registration'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Prelim</td>
                            <td>₱<?php echo number_format($payment_schedule['prelim'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Midterm</td>
                            <td>₱<?php echo number_format($payment_schedule['midterm'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Semi-final</td>
                            <td>₱<?php echo number_format($payment_schedule['semi_final'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Final</td>
                            <td>₱<?php echo number_format($payment_schedule['final'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="status-section">
            ENROLLED<br>
            Date: <?php echo htmlspecialchars($registration_date); ?>
        </div>

        <div class="signatures">
            <div>
                <div class="signature-line"></div>
                <div style="text-align: center;">Student Signature</div>
            </div>
            <div>
                <div class="signature-line"></div>
                <div style="text-align: center;">Approved By (Registrar)</div>
            </div>
        </div>
    </div>

    <div class="print-button">
        <button onclick="window.print()">🖨️ Print Certificate</button>
    </div>
</body>
</html>
