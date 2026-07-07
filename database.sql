CREATE DATABASE IF NOT EXISTS facility_booking;
USE facility_booking;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') NOT NULL
);

CREATE TABLE facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL,
    capacity INT NULL,
    location VARCHAR(100) NULL,
    status ENUM('Available','Unavailable') DEFAULT 'Available'
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    facility_id INT NOT NULL,
    booking_date DATE NOT NULL,
    time_slot VARCHAR(20) NOT NULL,
    purpose VARCHAR(255),
    status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (facility_id) REFERENCES facilities(id)
);

INSERT INTO facilities (name, type, capacity, location, status) VALUES
('Seminar Room A', 'Room', 60, 'Block A, Level 2', 'Available'),
('Computer Lab 2', 'Room', 40, 'Block B, Level 1', 'Available'),
('Basketball Court', 'Court', NULL, 'Sports Complex', 'Available'),
('Projector Set', 'Equipment', NULL, 'Media Store', 'Available');
