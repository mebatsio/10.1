-- Create database: campus_events

CREATE DATABASE campus_events;
USE campus_events;

-- Admins Table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100)
);

-- Users Table (Students)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(100)
);

-- Events Table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200),
    date DATE,
    time TIME,
    venue VARCHAR(200),
    description TEXT,
    image VARCHAR(255),
    registration_deadline DATE,
    max_participants INT
);

-- Registrations Table
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    registered_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS events;
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  event_date DATE NOT NULL,
  location VARCHAR(255) NOT NULL,
  image VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    department_id INT,
    photo VARCHAR(255),
    password VARCHAR(255)
);

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

INSERT INTO departments (name) VALUES ('Computer Science'), ('Engineering'), ('Business');