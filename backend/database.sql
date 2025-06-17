-- Database schema for Plateforme Auto-Entrepreneurs Tunisie

CREATE DATABASE IF NOT EXISTS autoentre_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE autoentre_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE,
    password VARCHAR(255),
    name VARCHAR(100) NOT NULL,
    photo VARCHAR(255),
    type ENUM('autoentrepreneur', 'client', 'admin') NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    google_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Profiles for auto-entrepreneurs
CREATE TABLE autoentrepreneur_profiles (
    user_id INT PRIMARY KEY,
    bio TEXT,
    location VARCHAR(100),
    categories VARCHAR(255),
    prices VARCHAR(255),
    experience TEXT,
    portfolio TEXT,
    availability VARCHAR(100),
    contact_method VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Services
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    price DECIMAL(10,2),
    images TEXT,
    availability VARCHAR(100),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Admin announcements
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dummy data
INSERT INTO users (email, phone, password, name, type, is_verified) VALUES
('ae1@email.com', '20000001', '$2y$10$testhash', 'Ali AE', 'autoentrepreneur', 1),
('client1@email.com', '20000002', '$2y$10$testhash', 'Sami Client', 'client', 1),
('admin@email.com', '20000003', '$2y$10$testhash', 'Admin', 'admin', 1);

INSERT INTO autoentrepreneur_profiles (user_id, bio, location, categories, prices, experience, portfolio, availability, contact_method) VALUES
(1, 'Développeur web auto-entrepreneur', 'Tunis', 'Développement,Web', '100-500', '5 ans', 'https://portfolio.com/ali', 'Disponible', 'whatsapp:20000001');

INSERT INTO services (user_id, title, description, category, price, images, availability) VALUES
(1, 'Création de site web', 'Je crée des sites web modernes.', 'Développement', 300, 'img1.jpg,img2.jpg', 'Disponible');

INSERT INTO reviews (service_id, reviewer_id, rating, comment) VALUES
(1, 2, 5, 'Excellent travail!');

INSERT INTO announcements (title, content) VALUES
('Bienvenue!', 'Lancement de la plateforme.');
