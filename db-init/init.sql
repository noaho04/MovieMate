CREATE DATABASE IF NOT EXISTS moviemate;
USE moviemate;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    preferred_genre VARCHAR(50),
    is_admin BOOLEAN DEFAULT 0,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO users (username, email, password, is_admin) 
VALUES ("moviemateadminjohn", "admin@moviemate.com", "$2y$10$XitaEBxsnYiQdvHPg6Iq2eRsOKkgY5ygmNANi.G2EnjCYjawOYIYm", 1)
ON DUPLICATE KEY UPDATE username = username;