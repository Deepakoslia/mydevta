-- DEVTA Knowledge Services - Database Schema
-- For Hostinger: create the database in hPanel first, then import this file
-- (skip the CREATE DATABASE lines if your host already created `mydevta` / `uXXXX_mydevta`)

-- CREATE DATABASE IF NOT EXISTS mydevta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE mydevta;

-- Admin users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact form submissions
CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Service quote / contact requests (Get Quote modal)
CREATE TABLE IF NOT EXISTS service_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    service VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service (service),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin credentials:
--   Username: admin
--   Password: Admin@123
-- CHANGE THIS PASSWORD immediately after first login.
INSERT INTO users (username, password) VALUES
('admin', '$2y$12$GQW1KvW.AarWMvCpfyyovecpAqRvS3i4YT.7dtsEkz9tgvJwtfQCC')
ON DUPLICATE KEY UPDATE username = username;
