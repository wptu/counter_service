-- Database Schema for Shift Scheduler
-- Created: 2026-12-10

-- Users table (admin only)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Staff table
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    group_type ENUM('A', 'B') NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_group (group_type),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schedules table
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    date DATE NOT NULL,
    day_name VARCHAR(20),
    tp_a_id INT NULL,
    tp_b_id INT NULL,
    rs_id INT NULL,
    is_working BOOLEAN DEFAULT FALSE,
    is_weekend BOOLEAN DEFAULT FALSE,
    is_holiday BOOLEAN DEFAULT FALSE,
    holiday_name VARCHAR(200),
    remark TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tp_a_id) REFERENCES staff(id) ON DELETE SET NULL,
    FOREIGN KEY (tp_b_id) REFERENCES staff(id) ON DELETE SET NULL,
    FOREIGN KEY (rs_id) REFERENCES staff(id) ON DELETE SET NULL,
    INDEX idx_date (date),
    INDEX idx_year (year),
    UNIQUE KEY unique_year_date (year, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Schedule metadata
CREATE TABLE IF NOT EXISTS schedule_meta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT UNIQUE NOT NULL,
    working_days_count INT,
    rs_group_a_count INT,
    rs_group_b_count INT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_year (year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Password: minad!123! (hashed with bcrypt)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$rK8H9P.qvVJ3qvXxL7zWv.N2QqYbJc8qLKm0FW9vLO5kC8HgJqKZO', 'admin');

-- Note: To generate this hash in PHP, use:
-- password_hash('minad!123!', PASSWORD_DEFAULT);
