<?php
$host = "localhost";
$db_name = "enrollment_db";
$username = "root";
$password = "";
$port = 3307;

// Turn on exceptions for mysqli so we can catch errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Try connecting directly to the database
    $conn = new mysqli($host, $username, $password, $db_name, $port);
} catch (mysqli_sql_exception $e) {
    // If database does not exist, create it and reconnect
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            $tmp = new mysqli($host, $username, $password, null, $port);
            $tmp->query("CREATE DATABASE IF NOT EXISTS `" . $tmp->real_escape_string($db_name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $tmp->close();
            // Reconnect to the newly created database
            $conn = new mysqli($host, $username, $password, $db_name, $port);
        } catch (mysqli_sql_exception $e2) {
            die('Database creation failed: ' . $e2->getMessage());
        }
    } else {
        // Other connection error
        die('Connection failed: ' . $e->getMessage());
    }
}

// If we reached here and $conn is set, it's connected
if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . ($conn->connect_error ?? 'Unknown error'));
}

// Ensure the students table exists for the enrollment system
$conn->query("CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    surname VARCHAR(255),
    lastname VARCHAR(255),
    firstname VARCHAR(255),
    middlename VARCHAR(255),
    address TEXT,
    sex VARCHAR(50),
    cellphone VARCHAR(100),
    email VARCHAR(255),
    gcash_number VARCHAR(100),
    bank_account_number VARCHAR(100),
    approval_status VARCHAR(20) DEFAULT 'pending',
    payment_ref VARCHAR(255),
    nationality VARCHAR(100),
    birthplace VARCHAR(100),
    birthdate DATE,
    school VARCHAR(255),
    course VARCHAR(255),
    year_level VARCHAR(50),
    semester VARCHAR(50),
    student_status VARCHAR(50),
    registration_fee DECIMAL(10,2) DEFAULT 0.00,
    tuition_fee DECIMAL(10,2) DEFAULT 0.00,
    lab_fee DECIMAL(10,2) DEFAULT 0.00,
    misc_fee DECIMAL(10,2) DEFAULT 0.00,
    upon_registration DECIMAL(10,2) DEFAULT 4700.00,
    prelim_fee DECIMAL(10,2) DEFAULT 0.00,
    midterm_fee DECIMAL(10,2) DEFAULT 0.00,
    semi_final_fee DECIMAL(10,2) DEFAULT 0.00,
    final_fee DECIMAL(10,2) DEFAULT 0.00,
    guardian VARCHAR(255),
    relationship VARCHAR(255),
    signature VARCHAR(255),
    matriculation_subjects TEXT NULL,
    payment_proof VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Add matriculation_subjects column if missing for older databases
$matricColumnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'matriculation_subjects'");
if ($matricColumnCheck && $matricColumnCheck->num_rows === 0) {
    $conn->query("ALTER TABLE students ADD COLUMN matriculation_subjects TEXT NULL AFTER signature");
}

// Add bank_account_number column if missing for older databases
$bankColumnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'bank_account_number'");
if ($bankColumnCheck && $bankColumnCheck->num_rows === 0) {
    $conn->query("ALTER TABLE students ADD COLUMN bank_account_number VARCHAR(100) AFTER gcash_number");
}

// Add approval_status column if missing for older databases
$approvalColumnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'approval_status'");
if ($approvalColumnCheck && $approvalColumnCheck->num_rows === 0) {
    $conn->query("ALTER TABLE students ADD COLUMN approval_status VARCHAR(20) DEFAULT 'pending' AFTER bank_account_number");
}

// Add semester column if missing
$semesterColumnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'semester'");
if ($semesterColumnCheck && $semesterColumnCheck->num_rows === 0) {
    $conn->query("ALTER TABLE students ADD COLUMN semester VARCHAR(50) AFTER year_level");
}

// Add fee fields if missing
$feeColumns = [
    'registration_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER student_status",
    'tuition_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER registration_fee",
    'lab_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER tuition_fee",
    'misc_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER lab_fee",
    'upon_registration' => "DECIMAL(10,2) DEFAULT 4700.00 AFTER misc_fee",
    'prelim_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER upon_registration",
    'midterm_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER prelim_fee",
    'semi_final_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER midterm_fee",
    'final_fee' => "DECIMAL(10,2) DEFAULT 0.00 AFTER semi_final_fee",
];
foreach ($feeColumns as $col => $definition) {
    $feeColumnCheck = $conn->query("SHOW COLUMNS FROM students LIKE '" . $conn->real_escape_string($col) . "'");
    if ($feeColumnCheck && $feeColumnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE students ADD COLUMN $col $definition");
    }
}

// Remove vaccination column if it exists
$vaccinationColumnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'vaccination'");
if ($vaccinationColumnCheck && $vaccinationColumnCheck->num_rows > 0) {
    $conn->query("ALTER TABLE students DROP COLUMN vaccination");
}

// Ensure the admins table exists for admin logins
$conn->query("CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// If the column exists in the table but not the schema, add lastname for compatibility
$columnCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'lastname'");
if ($columnCheck && $columnCheck->num_rows === 0) {
    $conn->query("ALTER TABLE students ADD COLUMN lastname VARCHAR(255) AFTER surname");
}
?>