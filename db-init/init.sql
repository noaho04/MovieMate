CREATE DATABASE IF NOT EXISTS moviemate;
USE moviemate;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT 0,
    login_attempts INT DEFAULT 0,
    locked_until DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    preferred_genre VARCHAR(50),
    favorite_movie VARCHAR(255)
);

-- Create genres table
CREATE TABLE IF NOT EXISTS genres (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert default genres
INSERT IGNORE INTO genres (genre_name)
VALUES  ("Action"),
        ("Komedie"),
        ("Drama"),
        ("Skrekk"),
        ("Romantisk"),
        ("Sci-fi"),
        ("Thriller"),
        ("Animasjon"),
        ("Eventyr"),
        ("Krim")
ON DUPLICATE KEY UPDATE genre_name = genre_name;

-- Insert default admin user
INSERT INTO users (username, email, password, is_admin) 
VALUES ("moviemateadminjohn", "admin@moviemate.com", "$2y$10$XitaEBxsnYiQdvHPg6Iq2eRsOKkgY5ygmNANi.G2EnjCYjawOYIYm", 1)
ON DUPLICATE KEY UPDATE username = username;