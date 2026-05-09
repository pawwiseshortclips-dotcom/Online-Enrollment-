-- SQL script to create database and required tables for Enrollment System
-- Run this in MySQL (e.g. via phpMyAdmin or mysql CLI)

CREATE DATABASE IF NOT EXISTS enrollment_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE enrollment_db;

-- Students table
CREATE TABLE IF NOT EXISTS students (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Enrollments table (if not already created by submit.php)
CREATE TABLE IF NOT EXISTS enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(64) NOT NULL UNIQUE,
  firstname VARCHAR(255),
  lastname VARCHAR(255),
  email VARCHAR(255),
  phone VARCHAR(100),
  course VARCHAR(255),
  year_level VARCHAR(50),
  payment_ref VARCHAR(255),
  payment_proof VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Courses table
CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50),
  title VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: create an admin user using the provided PHP script create_admin.php
