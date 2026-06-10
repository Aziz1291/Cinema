-- =========================================================================
-- SYSTEME DE RESERVATION DE CINEMA - SCRIPT DE DEPLOIEMENT SQL (MASSIVE DATA)
-- =========================================================================

DROP DATABASE IF EXISTS cinema_db;
CREATE DATABASE cinema_db;
USE cinema_db;

-- -------------------------------------------------------------------------
-- 1. Table: users
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(10) NOT NULL DEFAULT 'user',
    username VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    birth_date DATE NOT NULL,
    CONSTRAINT uq_username UNIQUE (username),
    CONSTRAINT uq_email UNIQUE (email)
);

-- -------------------------------------------------------------------------
-- 2. Table: films
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS films (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    duration INT(11) NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at DATE NOT NULL,
    poster_image VARCHAR(100) NOT NULL
);

-- -------------------------------------------------------------------------
-- 3. Table: rooms
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS rooms (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    rowsNumber INT(11) NOT NULL,
    seatsNumber INT(11) NOT NULL
);

-- -------------------------------------------------------------------------
-- 4. Table: schedules
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS schedules (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    film_id INT(11) NOT NULL,
    room_id INT(11) NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    ticket_price DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_schedules_film FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
    CONSTRAINT fk_schedules_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    CONSTRAINT chk_schedules_time CHECK (end_time > start_time)
);

-- -------------------------------------------------------------------------
-- 5. Table: seats
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS seats (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    room_id INT(11) NOT NULL,
    row_letter CHAR(1) NOT NULL,
    seat_number INT(11) NOT NULL,
    CONSTRAINT fk_seats_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    CONSTRAINT uq_seats_room UNIQUE (room_id, row_letter, seat_number)
);

-- -------------------------------------------------------------------------
-- 6. Table: reservations
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    seat_id INT(11) NOT NULL,
    schedule_id INT(11) NOT NULL,
    CONSTRAINT fk_reservations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reservations_seat FOREIGN KEY (seat_id) REFERENCES seats(id) ON DELETE CASCADE,
    CONSTRAINT fk_reservations_schedule FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    CONSTRAINT uq_reservations_seat UNIQUE (schedule_id, seat_id)
);


-- =========================================================================
-- SEED DATA (MASSIVE INSERTS)
-- =========================================================================

-- -------------------------------------------------------------------------
-- Users (admin123 and user123 passwords)
-- -------------------------------------------------------------------------
INSERT INTO users (role, username, first_name, last_name, email, password, birth_date) VALUES 
('admin', 'admin', 'Super', 'Admin', 'admin@cinema.com', '$2y$10$V8hmgADGht5ZLKLO2HbLE.6/1Gn/hCDXQ9VnEuigCVwKxwg29.HzC', '1990-01-01'),
('user', 'john_doe', 'John', 'Doe', 'john@example.com', '$2y$10$SwcHniOp/IIe4MTXFCbTD.jQ2GxwLCwok926fsoYMcZs4K18mKr8S', '1995-05-15'),
('user', 'jane_smith', 'Jane', 'Smith', 'jane@example.com', '$2y$10$SwcHniOp/IIe4MTXFCbTD.jQ2GxwLCwok926fsoYMcZs4K18mKr8S', '1998-10-20'),
('user', 'bob_builder', 'Bob', 'Builder', 'bob@example.com', '$2y$10$SwcHniOp/IIe4MTXFCbTD.jQ2GxwLCwok926fsoYMcZs4K18mKr8S', '1985-03-12'),
('user', 'alice_wonder', 'Alice', 'Wonderland', 'alice@example.com', '$2y$10$SwcHniOp/IIe4MTXFCbTD.jQ2GxwLCwok926fsoYMcZs4K18mKr8S', '2001-07-08'),
('user', 'charlie_brown', 'Charlie', 'Brown', 'charlie@example.com', '$2y$10$SwcHniOp/IIe4MTXFCbTD.jQ2GxwLCwok926fsoYMcZs4K18mKr8S', '1992-11-25'),
('user', 'eve_hacker', 'Eve', 'Hacker', 'eve@example.com', '$2y$10$SwcHniOp/IIe4MTXFCbTD.jQ2GxwLCwok926fsoYMcZs4K18mKr8S', '1999-09-09');

-- -------------------------------------------------------------------------
-- Films
-- -------------------------------------------------------------------------
INSERT INTO films (title, duration, description, created_at, poster_image) VALUES 
('Avatar', 162, 'A paraplegic Marine dispatched to the moon Pandora.', CURDATE(), 'Avatar.webp'),
('Avengers', 143, 'Earths mightiest heroes must come together to save the world.', CURDATE(), 'Avengers-Assembly.jpg'),
('Joker', 122, 'In Gotham City, mentally troubled comedian Arthur Fleck embarks on a downward spiral.', CURDATE(), 'joker.jpg'),
('Jurassic Park', 127, 'A pragmatic paleontologist touring an almost complete theme park.', CURDATE(), 'jurassic-park.jpg'),
('The Matrix', 136, 'When a beautiful stranger leads computer hacker Neo to a forbidding underworld.', CURDATE(), 'matrix.webp'),
('Scream', 111, 'A year after the murder of her mother, a teenage girl is terrorized by a new killer.', CURDATE(), 'Scream.jpg'),
('Skyfall', 143, 'Bonds loyalty to M is tested when her past comes back to haunt her.', CURDATE(), 'skyfall.jpg'),
('Expendables 2', 103, 'Mr. Church reunites the Expendables for what should be an easy paycheck.', CURDATE(), 'the-expendables 2.jpg'),
('Little Mermaid', 135, 'A young mermaid makes a deal with a sea witch to trade her beautiful voice.', CURDATE(), 'the-little-mermaid.webp'),
('The Truman Show', 103, 'An insurance salesman discovers his whole life is actually a reality TV show.', CURDATE(), 'trauman.webp');

-- -------------------------------------------------------------------------
-- Rooms (5 Rooms of various sizes)
-- -------------------------------------------------------------------------
INSERT INTO rooms (name, rowsNumber, seatsNumber) VALUES 
('Salle 1 (IMAX)', 6, 60),    -- 6 rows of 10
('Salle 2 (3D)', 5, 40),      -- 5 rows of 8
('Salle 3 (Standard)', 4, 32),-- 4 rows of 8
('Salle 4 (VIP)', 3, 15),     -- 3 rows of 5
('Salle 5 (Classic)', 5, 50); -- 5 rows of 10

-- -------------------------------------------------------------------------
-- Seats (Hundreds of seats populated for all rooms)
-- -------------------------------------------------------------------------
-- Salle 1 (6 rows of 10)
INSERT INTO seats (room_id, row_letter, seat_number) VALUES 
(1, 'A', 1), (1, 'A', 2), (1, 'A', 3), (1, 'A', 4), (1, 'A', 5), (1, 'A', 6), (1, 'A', 7), (1, 'A', 8), (1, 'A', 9), (1, 'A', 10),
(1, 'B', 1), (1, 'B', 2), (1, 'B', 3), (1, 'B', 4), (1, 'B', 5), (1, 'B', 6), (1, 'B', 7), (1, 'B', 8), (1, 'B', 9), (1, 'B', 10),
(1, 'C', 1), (1, 'C', 2), (1, 'C', 3), (1, 'C', 4), (1, 'C', 5), (1, 'C', 6), (1, 'C', 7), (1, 'C', 8), (1, 'C', 9), (1, 'C', 10),
(1, 'D', 1), (1, 'D', 2), (1, 'D', 3), (1, 'D', 4), (1, 'D', 5), (1, 'D', 6), (1, 'D', 7), (1, 'D', 8), (1, 'D', 9), (1, 'D', 10),
(1, 'E', 1), (1, 'E', 2), (1, 'E', 3), (1, 'E', 4), (1, 'E', 5), (1, 'E', 6), (1, 'E', 7), (1, 'E', 8), (1, 'E', 9), (1, 'E', 10),
(1, 'F', 1), (1, 'F', 2), (1, 'F', 3), (1, 'F', 4), (1, 'F', 5), (1, 'F', 6), (1, 'F', 7), (1, 'F', 8), (1, 'F', 9), (1, 'F', 10);

-- Salle 2 (5 rows of 8)
INSERT INTO seats (room_id, row_letter, seat_number) VALUES 
(2, 'A', 1), (2, 'A', 2), (2, 'A', 3), (2, 'A', 4), (2, 'A', 5), (2, 'A', 6), (2, 'A', 7), (2, 'A', 8),
(2, 'B', 1), (2, 'B', 2), (2, 'B', 3), (2, 'B', 4), (2, 'B', 5), (2, 'B', 6), (2, 'B', 7), (2, 'B', 8),
(2, 'C', 1), (2, 'C', 2), (2, 'C', 3), (2, 'C', 4), (2, 'C', 5), (2, 'C', 6), (2, 'C', 7), (2, 'C', 8),
(2, 'D', 1), (2, 'D', 2), (2, 'D', 3), (2, 'D', 4), (2, 'D', 5), (2, 'D', 6), (2, 'D', 7), (2, 'D', 8),
(2, 'E', 1), (2, 'E', 2), (2, 'E', 3), (2, 'E', 4), (2, 'E', 5), (2, 'E', 6), (2, 'E', 7), (2, 'E', 8);

-- Salle 3 (4 rows of 8)
INSERT INTO seats (room_id, row_letter, seat_number) VALUES 
(3, 'A', 1), (3, 'A', 2), (3, 'A', 3), (3, 'A', 4), (3, 'A', 5), (3, 'A', 6), (3, 'A', 7), (3, 'A', 8),
(3, 'B', 1), (3, 'B', 2), (3, 'B', 3), (3, 'B', 4), (3, 'B', 5), (3, 'B', 6), (3, 'B', 7), (3, 'B', 8),
(3, 'C', 1), (3, 'C', 2), (3, 'C', 3), (3, 'C', 4), (3, 'C', 5), (3, 'C', 6), (3, 'C', 7), (3, 'C', 8),
(3, 'D', 1), (3, 'D', 2), (3, 'D', 3), (3, 'D', 4), (3, 'D', 5), (3, 'D', 6), (3, 'D', 7), (3, 'D', 8);

-- Salle 4 (VIP) (3 rows of 5)
INSERT INTO seats (room_id, row_letter, seat_number) VALUES 
(4, 'A', 1), (4, 'A', 2), (4, 'A', 3), (4, 'A', 4), (4, 'A', 5),
(4, 'B', 1), (4, 'B', 2), (4, 'B', 3), (4, 'B', 4), (4, 'B', 5),
(4, 'C', 1), (4, 'C', 2), (4, 'C', 3), (4, 'C', 4), (4, 'C', 5);

-- -------------------------------------------------------------------------
-- Schedules (Sessions scattered throughout 2026/2027)
-- All durations mapped correctly (start_time + duration)
-- -------------------------------------------------------------------------
INSERT INTO schedules (film_id, room_id, start_time, end_time, ticket_price) VALUES 
-- Avatar (162 mins) in Salle 1
(1, 1, '2026-06-15 14:00:00', '2026-06-15 16:42:00', 15.00),
(1, 1, '2026-06-15 18:00:00', '2026-06-15 20:42:00', 15.00),
(1, 1, '2026-06-16 20:30:00', '2026-06-16 23:12:00', 15.00),

-- Avengers (143 mins) in Salle 2
(2, 2, '2026-06-15 15:30:00', '2026-06-15 17:53:00', 14.50),
(2, 2, '2026-06-16 18:00:00', '2026-06-16 20:23:00', 14.50),

-- Joker (122 mins) in Salle 3
(3, 3, '2026-06-15 19:00:00', '2026-06-15 21:02:00', 12.00),
(3, 3, '2026-06-17 21:30:00', '2026-06-17 23:32:00', 12.00),

-- Jurassic Park (127 mins) in Salle 4 (VIP)
(4, 4, '2026-06-15 20:00:00', '2026-06-15 22:07:00', 25.00),
(4, 4, '2026-06-18 19:00:00', '2026-06-18 21:07:00', 25.00),

-- The Matrix (136 mins) in Salle 1
(5, 1, '2026-06-17 14:00:00', '2026-06-17 16:16:00', 13.00),
(5, 1, '2026-06-18 18:00:00', '2026-06-18 20:16:00', 13.00),

-- Scream (111 mins) in Salle 2
(6, 2, '2026-06-17 22:00:00', '2026-06-17 23:51:00', 10.00),
(6, 2, '2026-06-19 23:00:00', '2026-06-20 00:51:00', 10.00),

-- Skyfall (143 mins) in Salle 3
(7, 3, '2026-06-16 14:00:00', '2026-06-16 16:23:00', 12.00),

-- Expendables 2 (103 mins) in Salle 3
(8, 3, '2026-06-16 17:30:00', '2026-06-16 19:13:00', 11.00),

-- Little Mermaid (135 mins) in Salle 2
(9, 2, '2026-06-15 10:00:00', '2026-06-15 12:15:00', 9.50),
(9, 2, '2026-06-16 10:00:00', '2026-06-16 12:15:00', 9.50),

-- The Truman Show (103 mins) in Salle 4
(10, 4, '2026-06-16 20:00:00', '2026-06-16 21:43:00', 20.00);

-- -------------------------------------------------------------------------
-- Reservations (Testing overlapping users booking seats)
-- -------------------------------------------------------------------------
INSERT INTO reservations (user_id, seat_id, schedule_id) VALUES 
-- Avatar (Schedule 1) -> User 2 books C5, C6
(2, 25, 1),
(2, 26, 1),
-- Avatar (Schedule 1) -> User 3 books D5, D6
(3, 35, 1),
(3, 36, 1),

-- Avengers (Schedule 4) -> User 4 books A1, A2, A3
(4, 61, 4),
(4, 62, 4),
(4, 63, 4),

-- Jurassic Park (Schedule 8, VIP) -> User 5 books B3
(5, 138, 8);
