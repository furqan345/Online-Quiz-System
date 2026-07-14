-- ============================================================
-- Online Quiz and Result Management System
-- Database Schema (MySQL)
-- FYP Project - Roll No 10
-- ============================================================

CREATE DATABASE IF NOT EXISTS quiz_system;
USE quiz_system;

-- ============================================================
-- 1. USERS TABLE
-- Stores Students, Admins, and Super Admin
-- Password is stored as PLAIN TEXT (as requested for this project)
-- ============================================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,       -- plain text password
    role ENUM('student', 'admin', 'superadmin') NOT NULL DEFAULT 'student',
    contact_number VARCHAR(20),
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- 2. QUIZZES TABLE
-- Created by Admin/Teacher
-- ============================================================
CREATE TABLE quizzes (
    quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    total_marks INT NOT NULL DEFAULT 0,
    duration_minutes INT NOT NULL,        -- quiz time limit
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    teacher_id INT NOT NULL,              -- FK to users (admin who created it)
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- 3. QUESTIONS TABLE
-- Belongs to a quiz
-- ============================================================
CREATE TABLE questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type ENUM('mcq', 'truefalse', 'short') NOT NULL DEFAULT 'mcq',
    marks INT NOT NULL DEFAULT 1,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE
);

-- ============================================================
-- 4. OPTIONS TABLE
-- Used for MCQ / True-False questions
-- ============================================================
CREATE TABLE options (
    option_id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE
);

-- ============================================================
-- 5. STUDENT_QUIZ TABLE
-- Tracks which student attempted which quiz
-- ============================================================
CREATE TABLE student_quiz (
    student_quiz_id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    student_id INT NOT NULL,
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score INT DEFAULT 0,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- 6. ANSWERS TABLE
-- Stores each answer submitted by a student
-- ============================================================
CREATE TABLE answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    student_quiz_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option_id INT NULL,          -- for MCQ/True-False
    answer_text TEXT NULL,                -- for short answers
    marks_obtained INT DEFAULT 0,
    FOREIGN KEY (student_quiz_id) REFERENCES student_quiz(student_quiz_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(question_id) ON DELETE CASCADE,
    FOREIGN KEY (selected_option_id) REFERENCES options(option_id) ON DELETE SET NULL
);

-- ============================================================
-- 7. RESULTS TABLE
-- Final calculated result for each quiz attempt
-- ============================================================
CREATE TABLE results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    student_quiz_id INT NOT NULL,
    total_score INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    grade VARCHAR(5),
    result_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_quiz_id) REFERENCES student_quiz(student_quiz_id) ON DELETE CASCADE
);

-- ============================================================
-- 8. NOTIFICATIONS TABLE
-- For FR-16: notify students about upcoming quizzes
-- ============================================================
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE DATA (for testing)
-- ============================================================

-- Super Admin
INSERT INTO users (name, email, password, role) VALUES
('Super Admin', 'superadmin@quiz.com', 'admin123', 'superadmin');

-- Admin / Teacher
INSERT INTO users (name, email, password, role, department) VALUES
('Ali Raza', 'admin@quiz.com', 'admin123', 'admin', 'Computer Science');

-- Students
INSERT INTO users (name, email, password, role, department) VALUES
('Ahmed Khan', 'ahmed@quiz.com', 'student123', 'student', 'BSCS'),
('Sara Malik', 'sara@quiz.com', 'student123', 'student', 'BSCS');

-- Sample Quiz
INSERT INTO quizzes (title, description, total_marks, duration_minutes, teacher_id) VALUES
('PHP Basics Quiz', 'A quiz to test basic PHP knowledge', 10, 15, 2);

-- Sample Questions
INSERT INTO questions (quiz_id, question_text, question_type, marks) VALUES
(1, 'PHP stands for?', 'mcq', 5),
(1, 'PHP is a server-side language.', 'truefalse', 5);

-- Sample Options for Question 1 (MCQ)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(1, 'Personal Home Page', 0),
(1, 'PHP: Hypertext Preprocessor', 1),
(1, 'Preprocessor Home Page', 0);

-- Sample Options for Question 2 (True/False)
INSERT INTO options (question_id, option_text, is_correct) VALUES
(2, 'True', 1),
(2, 'False', 0);
