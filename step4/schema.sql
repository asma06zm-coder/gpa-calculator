CREATE DATABASE IF NOT EXISTS gpa_db;
USE gpa_db;

CREATE TABLE IF NOT EXISTS calculations (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    student    VARCHAR(100) NOT NULL,
    semester   VARCHAR(50)  NOT NULL,
    gpa        DECIMAL(4,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    calculation_id INT NOT NULL,
    course_name    VARCHAR(100) NOT NULL,
    credits        DECIMAL(4,1) NOT NULL,
    grade          DECIMAL(3,1) NOT NULL,
    grade_points   DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (calculation_id) REFERENCES calculations(id) ON DELETE CASCADE
);
