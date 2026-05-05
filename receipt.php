<?php
require_once 'database.php';

if (empty($_GET['token'])) {
    die('Missing token');
}
$token = $conn->real_escape_string($_GET['token']);

$stmt = $conn->prepare("SELECT id, firstname, lastname, middlename, address, sex, nationality, birthplace, birthdate, school, guardian, relationship, vaccination, email, cellphone, gcash_number, course, year_level, payment_ref, payment_proof, student_status, created_at FROM students WHERE token = ? LIMIT 1");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die('Receipt not found');
}
$row = $res->fetch_assoc();
$stmt->close();

$base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
$base = rtrim($base, '/\\');
$receipt_url = $base . '/receipt.php?token=' . urlencode($token);
$qr_src = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($receipt_url);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enrollment Receipt</title>
    <style>
        body{font-family: Arial, sans-serif; max-width:800px;margin:20px auto}
        .field{margin-bottom:8px}
        .header{display:flex;align-items:center;justify-content:space-between}
        .qr{margin-left:20px}
        img.proof{max-width:300px;border:1px solid #ddd;padding:4px}
        .actions{margin-top:16px}
        button, a.button{display:inline-block;padding:8px 12px;background:#2b6cb0;color:#fff;text-decoration:none;border-radius:4px;border:none}
    </style>
</head>
<body>

<div class="header">
    <div>
        <h2>Enrollment Receipt</h2>
        <div>Receipt Token: <strong><?php echo htmlspecialchars($token); ?></strong></div>
        <div>Submitted: <?php echo htmlspecialchars($row['created_at']); ?></div>
    </div>
    <div class="qr">
        <img src="<?php echo $qr_src; ?>" alt="QR Code" width="200" height="200">
        <div style="text-align:center;font-size:12px">Scan to view receipt</div>
    </div>
</div>

<hr>

<div class="field"><strong>First Name:</strong> <?php echo htmlspecialchars($row['firstname']); ?></div>
<div class="field"><strong>Middle Name:</strong> <?php echo htmlspecialchars($row['middlename']); ?></div>
<div class="field"><strong>Last Name:</strong> <?php echo htmlspecialchars($row['lastname']); ?></div>
<div class="field"><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></div>
<div class="field"><strong>Sex:</strong> <?php echo htmlspecialchars($row['sex']); ?></div>
<div class="field"><strong>Nationality:</strong> <?php echo htmlspecialchars($row['nationality']); ?></div>
<div class="field"><strong>Birthplace:</strong> <?php echo htmlspecialchars($row['birthplace']); ?></div>
<div class="field"><strong>Birthdate:</strong> <?php echo htmlspecialchars($row['birthdate']); ?></div>
<div class="field"><strong>School:</strong> <?php echo htmlspecialchars($row['school']); ?></div>
<div class="field"><strong>Guardian:</strong> <?php echo htmlspecialchars($row['guardian']); ?></div>
<div class="field"><strong>Relationship:</strong> <?php echo htmlspecialchars($row['relationship']); ?></div>
<div class="field"><strong>Vaccination:</strong> <?php echo htmlspecialchars($row['vaccination']); ?></div>
<div class="field"><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></div>
<div class="field"><strong>GCash Number:</strong> <?php echo htmlspecialchars($row['gcash_number']); ?></div>
<div class="field"><strong>Cellphone Number:</strong> <?php echo htmlspecialchars($row['cellphone']); ?></div>
<div class="field"><strong>Reference Number:</strong> <?php echo htmlspecialchars($row['payment_ref']); ?></div>
<div class="field"><strong>Course:</strong> <?php echo htmlspecialchars($row['course']); ?></div>

<?php if (!empty($row['payment_proof'])): ?>
    <div class="field"><strong>Payment Proof:</strong><br>
        <a href="<?php echo $base . '/uploads/' . rawurlencode($row['payment_proof']); ?>" target="_blank">
            <img class="proof" src="<?php echo $base . '/uploads/' . rawurlencode($row['payment_proof']); ?>" alt="Payment Proof">
        </a>
    </div>
<?php endif; ?>

<div class="actions">
    <a class="button" href="<?php echo $base . '/receipt_pdf.php?token=' . urlencode($token); ?>">Download PDF (server)</a>
    <button onclick="window.print()">Print / Save as PDF</button>
    <a class="button" href="<?php echo $base . '/index.php'; ?>">Back to Enrollment Form</a>
</div>

</body>
</html>
