<?php
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// Collect form data
$surname = $_POST['surname'] ?? '';
$lastname = $_POST['lastname'] ?? '';$lastname = $_POST['lastname'] ?? $surname;$firstname = $_POST['firstname'] ?? '';
$middlename = $_POST['middlename'] ?? '';
$address = $_POST['address'] ?? '';
$sex = $_POST['sex'] ?? '';
$cellphone = $_POST['cellphone'] ?? '';
$email = $_POST['email'] ?? '';
$gcash_number = $_POST['gcash_number'] ?? '';
$bank_account_number = '1234567890123';
$payment_ref = $_POST['reference_number'] ?? '';
$nationality = $_POST['nationality'] ?? '';
$birthplace = $_POST['birthplace'] ?? '';
$birthdate = $_POST['birthdate'] ?? '';
$school = $_POST['school'] ?? '';
$course = $_POST['course'] ?? '';
$year_level = $_POST['year_level'] ?? '';
$semester = $_POST['semester'] ?? '';
$student_status = $_POST['student_status'] ?? '';
$guardian = $_POST['guardian'] ?? '';
$relationship = $_POST['relationship'] ?? '';
$signature = $_POST['signature'] ?? '';

$matriculationSubjectsJson = null;

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
$stmt = $conn->prepare("INSERT INTO students (surname, lastname, firstname, middlename, address, sex, cellphone, email, gcash_number, bank_account_number, payment_ref, nationality, birthplace, birthdate, school, course, year_level, semester, student_status, guardian, relationship, signature, matriculation_subjects, payment_proof, token, approval_status) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die('Query error: ' . $conn->error);
}

$approval_status = 'pending';

$stmt->bind_param('ssssssssssssssssssssssssss', $surname, $lastname, $firstname, $middlename, $address, $sex, $cellphone, $email, $gcash_number, $bank_account_number, $payment_ref, $nationality, $birthplace, $birthdate, $school, $course, $year_level, $semester, $student_status, $guardian, $relationship, $signature, $matriculationSubjectsJson, $payment_proof, $token, $approval_status);

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
