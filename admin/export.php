<?php
session_start();
require_once __DIR__ . '/../database.php';
if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; }

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="enrollments_export_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output','w');
fputcsv($out, ['id','token','firstname','lastname','middlename','address','sex','nationality','birthplace','birthdate','school','guardian','relationship','signature','vaccination','email','phone','gcash_number','course','year_level','payment_ref','payment_proof','created_at']);

$q = trim($_GET['q'] ?? '');
$course = trim($_GET['course'] ?? '');
$year = trim($_GET['year'] ?? '');

$where = [];
$params = [];
$types = '';
if ($q !== '') { $where[] = "(firstname LIKE ? OR lastname LIKE ? OR email LIKE ? OR phone LIKE ? OR payment_ref LIKE ?)"; $like = "%$q%"; for ($i=0;$i<5;$i++){ $params[] = $like; $types .= 's'; } }
if ($course !== '') { $where[] = 'course = ?'; $params[] = $course; $types .= 's'; }
if ($year !== '') { $where[] = 'year_level = ?'; $params[] = $year; $types .= 's'; }

$sql = 'SELECT id, token, firstname, lastname, middlename, address, sex, nationality, birthplace, birthdate, school, guardian, relationship, signature, vaccination, email, phone, gcash_number, course, year_level, payment_ref, payment_proof, student_status, created_at FROM students';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }

$stmt = $conn->prepare($sql);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$res = $stmt->get_result();
while($r = $res->fetch_assoc()) {
    fputcsv($out, [$r['id'],$r['token'],$r['firstname'],$r['lastname'],$r['middlename'],$r['address'],$r['sex'],$r['nationality'],$r['birthplace'],$r['birthdate'],$r['school'],$r['guardian'],$r['relationship'],$r['signature'],$r['vaccination'],$r['email'],$r['phone'],$r['gcash_number'],$r['course'],$r['year_level'],$r['payment_ref'],$r['payment_proof'],$r['created_at']]);
}
fclose($out);
exit;
