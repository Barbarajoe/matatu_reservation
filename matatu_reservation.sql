-- Database Creation
CREATE DATABASE IF NOT EXISTS `matatu_reservation` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `matatu_reservation`;

-- Users Table
CREATE TABLE `users` (
    `user_id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `is_verified` BOOLEAN DEFAULT FALSE,
    `verification_token` VARCHAR(255),
    `reset_token` VARCHAR(255),
    `reset_expires` DATETIME
) ENGINE=InnoDB;

-- Password History Table
CREATE TABLE `password_history` (
    `password_history_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `hashed_password` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Audit Logs Table
CREATE TABLE `audit_logs` (
    `audit_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `action_type` VARCHAR(50) NOT NULL,
    `action_details` TEXT,
    `ip_address` VARCHAR(45),
    `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Vehicles Table
CREATE TABLE `vehicles` (
    `vehicle_id` INT PRIMARY KEY AUTO_INCREMENT,
    `registration` VARCHAR(20) UNIQUE NOT NULL,
    `seat_capacity` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Routes Table
CREATE TABLE `routes` (
    `route_id` INT PRIMARY KEY AUTO_INCREMENT,
    `route_name` VARCHAR(100) NOT NULL,
    `departure_point` VARCHAR(100) NOT NULL,
    `arrival_point` VARCHAR(100) NOT NULL,
    `departure_time` DATETIME NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `vehicle_id` INT NOT NULL,
    `departure_coords` POINT NOT NULL,
    `arrival_coords` POINT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`vehicle_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bookings Table
CREATE TABLE `bookings` (
    `booking_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `route_id` INT NOT NULL,
    `seat_numbers` VARCHAR(255) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `booking_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    `payment_method` VARCHAR(50),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`route_id`) REFERENCES `routes`(`route_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Payments Table
CREATE TABLE `payments` (
    `payment_id` INT PRIMARY KEY AUTO_INCREMENT,
    `booking_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `transaction_id` VARCHAR(255) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `mpesa_code` VARCHAR(255),
    `card_last4` CHAR(4),
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Indexes
CREATE INDEX `idx_route_departure` ON `routes` (`departure_point`);
CREATE INDEX `idx_booking_date` ON `bookings` (`booking_date`);
CREATE INDEX `idx_user_email` ON `users` (`email`);

-- Sample Data (Optional)
INSERT INTO `vehicles` (`registration`, `seat_capacity`) VALUES
('KBC 123A', 14),
('KBM 456B', 16);

INSERT INTO `routes` (
    `route_name`, 
    `departure_point`, 
    `arrival_point`, 
    `departure_time`, 
    `price`, 
    `vehicle_id`,
    `departure_coords`,
    `arrival_coords`
) VALUES (
    'Nairobi-Nakuru', 
    'Nairobi CBD', 
    'Nakuru CBD', 
    '2023-12-25 08:00:00', 
    1200.00, 
    1,
    ST_GeomFromText('POINT(-1.286389 36.817223)'),
    ST_GeomFromText('POINT(-0.303099 36.080026)')
);
-- Insert sample users
INSERT INTO `users` (`username`, `email`, `password_hash`, `is_verified`) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1), -- password: "password"
('mary_smith', 'mary@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1); -- password: "password"

-- Password history
INSERT INTO `password_history` (`user_id`, `hashed_password`) VALUES
(1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- "OldPassword123!"
(1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- "AnotherPassword456!"

-- Audit logs
INSERT INTO `audit_logs` (`user_id`, `action_type`, `action_details`, `ip_address`) VALUES
(1, 'LOGIN', 'User logged in successfully', '192.168.1.101'),
(1, 'PASSWORD_CHANGE', 'Password updated', '192.168.1.101'),
(2, 'LOGIN', 'User logged in successfully', '192.168.1.102');

-- Vehicles
INSERT INTO `vehicles` (`registration`, `seat_capacity`) VALUES
('KBC 123A', 14),
('KBM 456B', 16),
('KBS 789C', 14);

-- Routes
INSERT INTO `routes` (
    `route_name`, 
    `departure_point`, 
    `arrival_point`, 
    `departure_time`, 
    `price`, 
    `vehicle_id`,
    `departure_coords`,
    `arrival_coords`
) VALUES 
(
    'Nairobi-Nakuru', 
    'Nairobi River Road', 
    'Nakuru CBD', 
    '2023-12-25 08:00:00', 
    1200.00, 
    1,
    ST_GeomFromText('POINT(-1.286389 36.817223)'),
    ST_GeomFromText('POINT(-0.303099 36.080026)')
),
(
    'Nairobi-Mombasa', 
    'Nairobi Bus Station', 
    'Mombasa Likoni', 
    '2023-12-25 10:30:00', 
    2500.00, 
    2,
    ST_GeomFromText('POINT(-1.286389 36.817223)'),
    ST_GeomFromText('POINT(-4.043477 39.668206)')
);

-- Bookings
INSERT INTO `bookings` (
    `user_id`, 
    `route_id`, 
    `seat_numbers`, 
    `total_amount`, 
    `status`, 
    `payment_method`
) VALUES
(1, 1, '1A,1B', 2400.00, 'confirmed', 'mpesa'),
(2, 2, '2A', 2500.00, 'confirmed', 'card');

-- Payments
INSERT INTO `payments` (
    `booking_id`, 
    `amount`, 
    `transaction_id`, 
    `payment_method`, 
    `status`, 
    `mpesa_code`, 
    `card_last4`
) VALUES
(1, 2400.00, 'MPE123456', 'mpesa', 'completed', 'NLJ7HFGH0', NULL),
(2, 2500.00, 'CHG789012', 'card', 'completed', NULL, '4242');