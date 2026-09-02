-- Smart Bus and Transport Management System - MySQL Schema
-- Create database
CREATE DATABASE IF NOT EXISTS smart_bus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_bus;

-- Users table (admin, operator/staff, passenger)
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `role` ENUM('admin','operator','passenger') NOT NULL DEFAULT 'passenger',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`email`)
) ENGINE=InnoDB;

-- Drivers table (drivers may be linked to users or standalone)
CREATE TABLE `drivers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `license_number` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE (`license_number`),
  INDEX (`user_id`),
  CONSTRAINT `fk_driver_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Buses table
CREATE TABLE `buses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `registration_number` VARCHAR(100) NOT NULL UNIQUE,
  `model` VARCHAR(100) DEFAULT NULL,
  `capacity` INT UNSIGNED NOT NULL DEFAULT 40,
  `status` ENUM('active','maintenance','decommissioned') DEFAULT 'active',
  `assigned_driver_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`assigned_driver_id`),
  CONSTRAINT `fk_bus_driver` FOREIGN KEY (`assigned_driver_id`) REFERENCES `drivers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Routes table
CREATE TABLE `routes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_code` VARCHAR(50) NOT NULL UNIQUE,
  `origin` VARCHAR(255) NOT NULL,
  `destination` VARCHAR(255) NOT NULL,
  `distance_km` DECIMAL(8,2) DEFAULT NULL,
  `estimated_time_min` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Schedules table (a scheduled trip instance)
CREATE TABLE `schedules` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_id` INT UNSIGNED NOT NULL,
  `bus_id` INT UNSIGNED NOT NULL,
  `departure_datetime` DATETIME NOT NULL,
  `arrival_datetime` DATETIME NOT NULL,
  `fare` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `available_seats` INT UNSIGNED NOT NULL,
  `status` ENUM('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`route_id`),
  INDEX (`bus_id`),
  CONSTRAINT `fk_schedule_route` FOREIGN KEY (`route_id`) REFERENCES `routes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_schedule_bus` FOREIGN KEY (`bus_id`) REFERENCES `buses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bookings table
CREATE TABLE `bookings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `schedule_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `seat_number` VARCHAR(10) NOT NULL,
  `status` ENUM('booked','checked_in','cancelled','no_show') DEFAULT 'booked',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_booking_seat` (`schedule_id`,`seat_number`),
  INDEX (`schedule_id`),
  INDEX (`user_id`),
  CONSTRAINT `fk_booking_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Payments table
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `method` ENUM('cash','mobile_money','bank','card') NOT NULL,
  `status` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  `transaction_reference` VARCHAR(255) DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`booking_id`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tickets table (electronic ticket generated for a booking)
CREATE TABLE `tickets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL UNIQUE,
  `ticket_code` VARCHAR(100) NOT NULL UNIQUE,
  `issued_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `qr_data` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX (`booking_id`),
  CONSTRAINT `fk_ticket_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Travel history (log of completed trips, passenger travel records)
CREATE TABLE `travel_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED DEFAULT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `schedule_id` INT UNSIGNED DEFAULT NULL,
  `bus_id` INT UNSIGNED DEFAULT NULL,
  `route_id` INT UNSIGNED DEFAULT NULL,
  `travel_date` DATE DEFAULT NULL,
  `status` ENUM('completed','cancelled','no_show') DEFAULT 'completed',
  `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`booking_id`),
  CONSTRAINT `fk_history_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Index suggestions
CREATE INDEX idx_schedules_departure ON `schedules` (`departure_datetime`);
CREATE INDEX idx_bookings_user ON `bookings` (`user_id`);
CREATE INDEX idx_payments_status ON `payments` (`status`);

-- Triggers or stored procs (example): maintain available_seats when booking inserted/removed
DELIMITER $$
CREATE TRIGGER trg_booking_insert AFTER INSERT ON `bookings`
FOR EACH ROW
BEGIN
  UPDATE `schedules` SET available_seats = available_seats - 1 WHERE id = NEW.schedule_id AND available_seats > 0;
END$$

CREATE TRIGGER trg_booking_delete AFTER DELETE ON `bookings`
FOR EACH ROW
BEGIN
  UPDATE `schedules` SET available_seats = available_seats + 1 WHERE id = OLD.schedule_id;
END$$
DELIMITER ;

-- Note: Additional constraints (e.g., preventing conflicting schedules) will be enforced at application level.
