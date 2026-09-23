-- Student Record Management System Database Schema
-- Database: `srms_db`

CREATE DATABASE IF NOT EXISTS `srms_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `srms_db`;

-- 1. Admins Table
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `fullname` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Admin: username='admin', password='admin123'
-- Hash generated using password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO `admins` (`username`, `fullname`, `email`, `password`) VALUES
('admin', 'System Administrator', 'admin@college.edu', '$2y$10$amVu0a2ZEV9vhD4H.36kN.0AwXlGcqF2UAe38ErhXCVVD2XLdLVX.');

-- 2. Departments Table
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `dept_name` VARCHAR(100) NOT NULL,
    `dept_code` VARCHAR(20) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `departments` (`dept_name`, `dept_code`) VALUES
('Computer Science & Engineering', 'CSE'),
('Information Technology', 'IT'),
('Mechanical Engineering', 'ME'),
('Civil Engineering', 'CE'),
('Electronics & Communication', 'ECE'),
('Management & Computer Applications', 'MCA/BCA');

-- 3. Courses Table
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `dept_id` INT NOT NULL,
    `course_name` VARCHAR(100) NOT NULL,
    `course_code` VARCHAR(20) NOT NULL UNIQUE,
    `duration_years` INT NOT NULL DEFAULT 4,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`dept_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `courses` (`dept_id`, `course_name`, `course_code`, `duration_years`) VALUES
(1, 'B.Tech in Computer Science', 'BTECH-CS', 4),
(1, 'M.Tech in Computer Science', 'MTECH-CS', 2),
(2, 'B.Tech in Information Technology', 'BTECH-IT', 4),
(5, 'B.Tech in Electronics & Comm.', 'BTECH-ECE', 4),
(6, 'Bachelor of Computer Applications', 'BCA', 3),
(6, 'Master of Computer Applications', 'MCA', 2);

-- 4. Students Table
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `roll_no` VARCHAR(30) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `dob` DATE NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Male',
    `dept_id` INT NOT NULL,
    `course_id` INT NOT NULL,
    `semester` VARCHAR(20) NOT NULL,
    `admission_year` INT NOT NULL,
    `photo` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `status` ENUM('Active', 'Inactive', 'Graduated') NOT NULL DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`dept_id`) REFERENCES `departments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Student Records for instant rich demo
INSERT INTO `students` (`roll_no`, `full_name`, `email`, `phone`, `dob`, `gender`, `dept_id`, `course_id`, `semester`, `admission_year`, `address`, `status`) VALUES
('CS202301', 'Aarav Sharma', 'aarav.sharma@example.com', '+91 9876543210', '2003-05-14', 'Male', 1, 1, 'Semester 4', 2023, '124, Raj Nagar, New Delhi', 'Active'),
('CS202302', 'Ananya Verma', 'ananya.v@example.com', '+91 9811223344', '2004-02-18', 'Female', 1, 1, 'Semester 4', 2023, 'Plot 45, Sector 15, Noida', 'Active'),
('IT202315', 'Rohan Gupta', 'rohan.gupta@example.com', '+91 9822334455', '2003-11-25', 'Male', 2, 3, 'Semester 4', 2023, 'H.No 88, Civil Lines, Jaipur', 'Active'),
('BCA202401', 'Priya Patel', 'priya.patel@example.com', '+91 9733445566', '2005-08-09', 'Female', 6, 5, 'Semester 2', 2024, '52, Sunrise Enclave, Ahmedabad', 'Active'),
('MCA202409', 'Vikram Singh', 'vikram.singh@example.com', '+91 9944556677', '2001-04-30', 'Male', 6, 6, 'Semester 2', 2024, '21, Model Town, Lucknow', 'Active');
