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
    payment_ref VARCHAR(255),
    nationality VARCHAR(100),
    birthplace VARCHAR(100),
    birthdate DATE,
    school VARCHAR(255),
    course VARCHAR(255),
    year_level VARCHAR(50),
    student_status VARCHAR(50),
    guardian VARCHAR(255),
    relationship VARCHAR(255),
    vaccination VARCHAR(255),
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