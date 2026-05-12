CREATE DATABASE IF NOT EXISTS smart_bus;
USE smart_bus;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('passenger', 'driver', 'admin') NOT NULL,
    phone VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE buses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_number VARCHAR(20) UNIQUE NOT NULL,
    plate_number VARCHAR(20) NOT NULL,
    capacity INT NOT NULL,
    type ENUM('Ordinary', 'Air-conditioned') NOT NULL,
    status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active'
);

CREATE TABLE routes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    stops TEXT,
    distance_km DECIMAL(6,2),
    estimated_duration_minutes INT,
    origin_lat DECIMAL(10,8),
    origin_lng DECIMAL(11,8),
    destination_lat DECIMAL(10,8),
    destination_lng DECIMAL(11,8),
    status ENUM('active', 'inactive') DEFAULT 'active'
);

CREATE TABLE schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT,
    bus_id INT,
    driver_id INT,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    fare DECIMAL(8,2) NOT NULL,
    available_seats INT NOT NULL,
    trip_status ENUM('Scheduled','Boarding','Departed','En Route','Arrived','Cancelled','Delayed') DEFAULT 'Scheduled',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (route_id) REFERENCES routes(id),
    FOREIGN KEY (bus_id) REFERENCES buses(id),
    FOREIGN KEY (driver_id) REFERENCES users(id)
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id INT,
    schedule_id INT,
    seat_number INT NOT NULL,
    status ENUM('confirmed','cancelled','completed') DEFAULT 'confirmed',
    booked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    fare_paid DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (passenger_id) REFERENCES users(id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
);

CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT,
    schedule_id INT,
    description TEXT NOT NULL,
    incident_lat DECIMAL(10,8),
    incident_lng DECIMAL(11,8),
    reported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('open','resolved') DEFAULT 'open',
    FOREIGN KEY (driver_id) REFERENCES users(id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
);

-- Seed Data
INSERT INTO users (full_name, email, password, role, phone) VALUES
('Admin User', 'admin@drisingsun.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '09171234567'),
('Driver One', 'driver1@drisingsun.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', '09181234567'),
('Driver Two', 'driver2@drisingsun.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', '09191234567'),
('Passenger One', 'pass1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passenger', '09201234567'),
('Passenger Two', 'pass2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passenger', '09211234567'),
('Passenger Three', 'pass3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'passenger', '09221234567');

INSERT INTO buses (bus_number, plate_number, capacity, type) VALUES
('BUS-001', 'ABC-1234', 45, 'Air-conditioned'),
('BUS-002', 'DEF-5678', 60, 'Ordinary'),
('BUS-003', 'GHI-9012', 45, 'Air-conditioned');

INSERT INTO routes (origin, destination, stops, distance_km, estimated_duration_minutes, origin_lat, origin_lng, destination_lat, destination_lng) VALUES
('Manila', 'Baguio', 'Tarlac, Pangasinan', 250, 360, 14.5995, 120.9842, 16.4023, 120.5960),
('Manila', 'Batangas City', 'Laguna', 110, 150, 14.5995, 120.9842, 13.7565, 121.0583),
('Manila', 'Lucena City', 'San Pablo, Tiaong', 130, 240, 14.5995, 120.9842, 13.9314, 121.6111);

INSERT INTO schedules (route_id, bus_id, driver_id, departure_time, arrival_time, fare, available_seats) VALUES
(1, 1, 2, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL '1 6' DAY_HOUR), 650.00, 45),
(2, 2, 3, DATE_ADD(NOW(), INTERVAL 2 HOUR), DATE_ADD(NOW(), INTERVAL '4' HOUR), 250.00, 60),
(3, 3, 2, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL '1 4' DAY_HOUR), 350.00, 45),
(1, 1, 3, DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL '-1 6' DAY_HOUR), 650.00, 45),
(2, 2, 2, DATE_ADD(NOW(), INTERVAL -2 HOUR), DATE_ADD(NOW(), INTERVAL '2' HOUR), 250.00, 60);

UPDATE schedules SET trip_status = 'Arrived' WHERE id = 4;
UPDATE schedules SET trip_status = 'En Route' WHERE id = 5;

INSERT INTO bookings (passenger_id, schedule_id, seat_number, status, fare_paid) VALUES
(4, 1, 1, 'confirmed', 650.00),
(4, 1, 2, 'confirmed', 650.00),
(5, 2, 5, 'confirmed', 250.00),
(6, 4, 10, 'completed', 650.00);

UPDATE schedules SET available_seats = available_seats - 2 WHERE id = 1;
UPDATE schedules SET available_seats = available_seats - 1 WHERE id = 2;
UPDATE schedules SET available_seats = available_seats - 1 WHERE id = 4;
