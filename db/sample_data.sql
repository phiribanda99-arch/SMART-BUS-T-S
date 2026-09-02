USE smart_bus;

-- Sample routes
INSERT INTO `routes` (`route_code`,`origin`,`destination`,`distance_km`,`estimated_time_min`) VALUES
('R001','Central Station','North Park',12.5,30),
('R002','Central Station','East Market',8.2,20),
('R003','North Park','East Market',18.0,45);

-- Sample buses
INSERT INTO `buses` (`registration_number`,`model`,`capacity`,`status`) VALUES
('KDA-123A','Volvo B9R',50,'active'),
('KDA-456B','Mercedes 0405',40,'active'),
('KDA-789C','Scania K310',45,'maintenance');

-- Sample drivers
INSERT INTO `drivers` (`license_number`,`phone`,`status`) VALUES
('DL-0001','+254700000001','active'),
('DL-0002','+254700000002','active');

-- Sample schedules (available_seats initially equals bus capacity)
INSERT INTO `schedules` (`route_id`,`bus_id`,`departure_datetime`,`arrival_datetime`,`fare`,`available_seats`,`status`) VALUES
(1,1,'2026-09-05 08:00:00','2026-09-05 08:30:00',200.00,50,'scheduled'),
(2,2,'2026-09-05 09:00:00','2026-09-05 09:20:00',150.00,40,'scheduled');

-- Sample passengers (no password yet — set password via PHP CLI per README)
INSERT INTO `users` (`first_name`,`last_name`,`email`,`phone`,`role`) VALUES
('John','Doe','johndoe@example.com','+254701000001','passenger'),
('Jane','Smith','janesmith@example.com','+254701000002','passenger');

-- Default admin user row (password empty; please set hashed password using README instructions)
INSERT INTO `users` (`first_name`,`last_name`,`email`,`phone`,`role`) VALUES
('Admin','User','admin@example.com','+254700000000','admin');
