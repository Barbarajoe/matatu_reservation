-- Create database
CREATE DATABASE IF NOT EXISTS `matatu_reservation`
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `matatu_reservation`;

-- ========================
-- USERS TABLE (With Roles)
-- ========================
CREATE TABLE `users` (
    `user_id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('passenger', 'operator', 'admin') DEFAULT 'passenger',
    `is_verified` BOOLEAN DEFAULT FALSE,
    `verification_token` VARCHAR(255),
    `reset_token` VARCHAR(255),
    `reset_expires` DATETIME,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==========================
-- PASSWORD HISTORY (Security)
-- ==========================
CREATE TABLE `password_history` (
    `password_history_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `hashed_password` VARCHAR(255) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ================
-- AUDIT LOGS TABLE
-- ================
CREATE TABLE `audit_logs` (
    `audit_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `action_type` VARCHAR(50) NOT NULL,
    `action_details` TEXT,
    `ip_address` VARCHAR(45),
    `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===========================
-- VEHICLES TABLE
-- ===========================
CREATE TABLE `vehicles` (
    `vehicle_id` INT PRIMARY KEY AUTO_INCREMENT,
    `registration` VARCHAR(20) UNIQUE NOT NULL,
    `seat_capacity` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===========================
-- ROUTES TABLE
-- ===========================
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

-- ============================
-- SCHEDULES TABLE (Operator)
-- ============================
CREATE TABLE `schedules` (
    `schedule_id` INT PRIMARY KEY AUTO_INCREMENT,
    `vehicle_id` INT NOT NULL,
    `route_id` INT NOT NULL,
    `departure_time` DATETIME NOT NULL,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`vehicle_id`),
    FOREIGN KEY (`route_id`) REFERENCES `routes`(`route_id`)
) ENGINE=InnoDB;

-- =======================
-- SEAT MAP TABLE
-- =======================
CREATE TABLE `seats` (
    `seat_id` INT PRIMARY KEY AUTO_INCREMENT,
    `vehicle_id` INT,
    `route_id` INT,
    `seat_number` VARCHAR(10),
    `is_booked` BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`vehicle_id`) ON DELETE CASCADE,
    FOREIGN KEY (`route_id`) REFERENCES `routes`(`route_id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================
-- BOOKINGS TABLE
-- =====================
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

-- =====================
-- PAYMENTS TABLE
-- =====================
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

-- ========================
-- USSD SESSIONS TABLE
-- ========================
CREATE TABLE `ussd_sessions` (
    `session_id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_phone` VARCHAR(15) NOT NULL,
    `current_step` VARCHAR(50),
    `session_data` TEXT,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================
-- INDEXES FOR PERFORMANCE
-- =========================
CREATE INDEX `idx_route_departure` ON `routes` (`departure_point`);
CREATE INDEX `idx_booking_date` ON `bookings` (`booking_date`);
CREATE INDEX `idx_user_email` ON `users` (`email`);
