<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Collect form data
$surname = $_POST['surname'] ?? '';
$firstname = $_POST['firstname'] ?? '';
$middlename = $_POST['middlename'] ?? '';
$address = $_POST['address'] ?? '';
$sex = $_POST['sex'] ?? '';
$cellphone = $_POST['cellphone'] ?? '';
$email = $_POST['email'] ?? '';
$gcash_number = $_POST['gcash_number'] ?? '';
$payment_ref = $_POST['reference_number'] ?? '';
$nationality = $_POST['nationality'] ?? '';
$birthplace = $_POST['birthplace'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$school = $_POST['school'] ?? '';
$course = $_POST['course'] ?? '';
$year_level = $_POST['year_level'] ?? '';
$student_status = $_POST['student_status'] ?? '';
$guardian = $_POST['guardian'] ?? '';
$relationship = $_POST['relationship'] ?? '';
$vaccination = isset($_POST['vax_status']) ? implode(', ', $_POST['vax_status']) : '';
$signature = $_POST['signature'] ?? '';

$subjectCodes = $_POST['subject_code'] ?? [];
$subjectTitles = $_POST['subject_title'] ?? [];
$subjectUnits = $_POST['subject_units'] ?? [];
$subjectTimes = $_POST['subject_time'] ?? [];
$subjectDays = $_POST['subject_day'] ?? [];

$matriculationSubjects = [];
for ($i = 0; $i < count($subjectCodes); $i++) {
    $code = trim($subjectCodes[$i] ?? '');
    $title = trim($subjectTitles[$i] ?? '');
    $unit = trim($subjectUnits[$i] ?? '');
    $time = trim($subjectTimes[$i] ?? '');
    $day = trim($subjectDays[$i] ?? '');

    if ($code === '' && $title === '') {
        continue;
    }

    $matriculationSubjects[] = [
        'code' => $code,
        'title' => $title,
        'units' => is_numeric($unit) ? (int) $unit : 0,
        'time' => $time,
        'day' => $day,
    ];
}

$matriculationSubjectsJson = null;
if (count($matriculationSubjects) > 0) {
    $matriculationSubjectsJson = json_encode($matriculationSubjects, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// Handle file upload
$payment_proof = null;
if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = time() . '_' . basename($_FILES['payment_proof']['name']);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $filepath)) {
        $payment_proof = $filepath;
    }
}

// Generate unique token
$token = bin2hex(random_bytes(16));

// Insert into database
$stmt = $conn->prepare("INSERT INTO students (surname, lastname, firstname, middlename, address, sex, cellphone, email, gcash_number, payment_ref, nationality, birthplace, birthdate, school, course, year_level, student_status, guardian, relationship, vaccination, signature, matriculation_subjects, payment_proof, token) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die('Query error: ' . $conn->error);
}

$stmt->bind_param('ssssssssssssssssssssssss', $surname, $surname, $firstname, $middlename, $address, $sex, $cellphone, $email, $gcash_number, $payment_ref, $nationality, $birthplace, $birthdate, $school, $course, $year_level, $student_status, $guardian, $relationship, $vaccination, $signature, $matriculationSubjectsJson, $payment_proof, $token);

if ($stmt->execute()) {
    $student_id = $stmt->insert_id;
    $stmt->close();
    
    // Redirect to matriculation page with token
    header('Location: matriculation.php?token=' . $token);
    exit;
} else {
    $stmt->close();
    die('Error: ' . $conn->error);
}
?>
