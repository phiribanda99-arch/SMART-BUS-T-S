CREATE DATABASE IF NOT EXISTS smartbus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartbus;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    id_number VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(50) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('Male','Female','Other') DEFAULT NULL,
    address TEXT DEFAULT NULL,
    role ENUM('admin','operator','passenger') NOT NULL DEFAULT 'passenger',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE drivers (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED DEFAULT NULL,
    license_number VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(50) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_driver_user (user_id),
    CONSTRAINT fk_drivers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE buses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    registration_number VARCHAR(80) NOT NULL UNIQUE,
    make VARCHAR(80) NOT NULL,
    model VARCHAR(80) NOT NULL,
    year INT UNSIGNED DEFAULT NULL,
    bus_type VARCHAR(50) DEFAULT NULL,
    seating_capacity INT UNSIGNED NOT NULL,
    driver_id INT UNSIGNED DEFAULT NULL,
    status ENUM('Available','Assigned','On Trip','Maintenance','Inactive') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_bus_driver (driver_id),
    CONSTRAINT fk_buses_driver FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE routes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    route_code VARCHAR(50) NOT NULL UNIQUE,
    origin VARCHAR(120) NOT NULL,
    origin_district VARCHAR(120) NOT NULL,
    destination VARCHAR(120) NOT NULL,
    destination_district VARCHAR(120) NOT NULL,
    distance_km DECIMAL(8,2) DEFAULT NULL,
    estimated_duration VARCHAR(50) DEFAULT NULL,
    fare DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE schedules (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    route_id INT UNSIGNED NOT NULL,
    bus_id INT UNSIGNED NOT NULL,
    travel_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    fare DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    available_seats INT UNSIGNED NOT NULL,
    status ENUM('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_schedules_route (route_id),
    KEY idx_schedules_bus (bus_id),
    KEY idx_schedule_date (travel_date),
    CONSTRAINT fk_schedules_route FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
    CONSTRAINT fk_schedules_bus FOREIGN KEY (bus_id) REFERENCES buses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_reference VARCHAR(50) NOT NULL UNIQUE,
    user_id INT UNSIGNED NOT NULL,
    schedule_id INT UNSIGNED NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    booking_status ENUM('Pending','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_bookings_user (user_id),
    KEY idx_bookings_schedule (schedule_id),
    UNIQUE KEY uq_booking_seat (schedule_id, seat_number),
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash','Mobile Money','Bank','Card') NOT NULL,
    transaction_reference VARCHAR(100) DEFAULT NULL,
    payment_status ENUM('Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_payments_booking (booking_id),
    KEY idx_payments_status (payment_status),
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tickets (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    ticket_number VARCHAR(80) NOT NULL UNIQUE,
    qr_code VARCHAR(255) DEFAULT NULL,
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_tickets_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE travel_history (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED DEFAULT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    schedule_id INT UNSIGNED DEFAULT NULL,
    travel_date DATE DEFAULT NULL,
    trip_status ENUM('completed','cancelled','no_show') NOT NULL DEFAULT 'completed',
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_history_booking (booking_id),
    KEY idx_history_user (user_id),
    CONSTRAINT fk_history_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    CONSTRAINT fk_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users (full_name, id_number, email, phone, password, gender, address, role, status)
VALUES
('System Admin', 'ADMIN-001', 'admin@example.com', '+254700000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 'Nairobi', 'admin', 'active'),
('John Passenger', 'P1001', 'passenger@example.com', '+254700000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Male', 'Kisumu', 'passenger', 'active'),
('Jane Operator', 'OP1001', 'operator@example.com', '+254700000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Female', 'Mombasa', 'operator', 'active');

INSERT INTO drivers (user_id, license_number, phone, status)
VALUES
(3, 'DL-201', '+254700000010', 'active'),
(3, 'DL-202', '+254700000011', 'active'),
(3, 'DL-203', '+254700000012', 'active');

INSERT INTO buses (registration_number, make, model, year, bus_type, seating_capacity, driver_id, status)
VALUES
('KDA 101A', 'Scania', 'K360', 2021, 'Coach', 48, 1, 'Available'),
('KDA 202B', 'Mercedes', 'Travego', 2020, 'Coach', 52, 2, 'Available'),
('KDA 303C', 'Volvo', 'B9R', 2022, 'Executive', 40, 3, 'Assigned'),
('KDA 404D', 'Isuzu', 'Urban', 2019, 'City', 30, NULL, 'Maintenance'),
('KDA 505E', 'Toyota', 'Coaster', 2023, 'Mini', 24, NULL, 'Available');

INSERT INTO routes (route_code, origin, origin_district, destination, destination_district, distance_km, estimated_duration, fare, status)
VALUES
('R001', 'Lusaka', 'Lusaka', 'Copperbelt', 'Kitwe', 480.00, '8 hours', 1500.00, 'active'),
('R002', 'Lusaka', 'Lusaka', 'Central', 'Kabwe', 350.50, '6 hours', 1200.00, 'active'),
('R003', 'Lusaka', 'Lusaka', 'Southern', 'Livingstone', 470.00, '7 hours', 1400.00, 'active'),
('R004', 'Copperbelt', 'Kitwe', 'Lusaka', 'Lusaka', 480.00, '8 hours', 1500.00, 'active'),
('R005', 'Southern', 'Livingstone', 'Lusaka', 'Lusaka', 470.00, '7 hours', 1400.00, 'active');

INSERT INTO schedules (route_id, bus_id, travel_date, departure_time, arrival_time, fare, available_seats, status)
VALUES
(1, 1, '2026-09-10', '08:00:00', '16:00:00', 1500.00, 48, 'scheduled'),
(2, 2, '2026-09-12', '09:30:00', '15:30:00', 1200.00, 52, 'scheduled'),
(3, 3, '2026-09-15', '07:30:00', '10:30:00', 700.00, 40, 'scheduled'),
(4, 2, '2026-09-18', '08:00:00', '14:00:00', 1200.00, 52, 'scheduled');

INSERT INTO bookings (booking_reference, user_id, schedule_id, seat_number, amount, booking_status)
VALUES
('BK-1001', 2, 1, '12', 1500.00, 'Confirmed'),
('BK-1002', 2, 2, '20', 1200.00, 'Pending');

INSERT INTO payments (booking_id, amount, payment_method, transaction_reference, payment_status)
VALUES
(1, 1500.00, 'Mobile Money', 'MM-1001', 'Paid'),
(2, 1200.00, 'Card', 'CARD-1002', 'Pending');

INSERT INTO tickets (booking_id, ticket_number, qr_code)
VALUES
(1, 'TKT-1001', 'QR-1001');

INSERT INTO travel_history (booking_id, user_id, schedule_id, travel_date, trip_status)
VALUES
(1, 2, 1, '2026-09-10', 'completed');
