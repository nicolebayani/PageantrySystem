-- Create database
CREATE DATABASE IF NOT EXISTS pageantry_system;
USE pageantry_system;

-- Users table (for admin and judges)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'judge') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Candidates table
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    candidate_number VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT,
    description TEXT,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criteria table
CREATE TABLE criteria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scores table
CREATE TABLE scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judge_id INT NOT NULL,
    candidate_id INT NOT NULL,
    criteria_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (judge_id) REFERENCES users(id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id),
    FOREIGN KEY (criteria_id) REFERENCES criteria(id),
    UNIQUE KEY unique_score (judge_id, candidate_id, criteria_id)
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role, full_name) VALUES 
('admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin', 'System Administrator'),
('judge1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'judge', 'Sample Judge');

-- Insert sample criteria
INSERT INTO criteria (name, percentage, description) VALUES 
('Beauty', 40.00, 'Physical appearance and overall beauty'),
('Intelligence', 30.00, 'Question and answer portion'),
('Talent', 20.00, 'Special talent presentation'),
('Personality', 10.00, 'Overall personality and charisma');
