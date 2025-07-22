
-- Insert sample users
INSERT INTO users (username, email, password) VALUES
('Alice', 'alice@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Bob', 'bob@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Charlie', 'charlie@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Diana', 'diana@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Eve', 'eve@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Frank', 'frank@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Grace', 'grace@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Hank', 'hank@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Ivy', 'ivy@example.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Jack', 'jack@example.com', '$2y$10$abcdefghijklmnopqrstuv');

-- Insert sample categories
INSERT INTO categories (name, color) VALUES
('Work', '#0d6efd'),
('Health', '#198754'),
('Personal', '#ffc107'),
('Study', '#6f42c1'),
('Fitness', '#fd7e14'),
('Finance', '#20c997'),
('Travel', '#6610f2'),
('Family', '#dc3545'),
('Chores', '#0dcaf0'),
('Social', '#198754');

-- Insert sample appointments
INSERT INTO appointments (user_id, title, description, category_id, status, type, start_date, end_date, start_time, end_time) VALUES
(1, 'Doctor Appointment', 'Checkup with Dr. Smith', 2, 'scheduled', 'private', '2025-07-25', '2025-07-25', '10:00:00', '11:00:00'),
(2, 'Work Meeting', 'Monthly team sync', 1, 'scheduled', 'public', '2025-07-26', '2025-07-26', '14:00:00', '15:00:00'),
(3, 'Yoga Class', 'Morning yoga session', 5, 'completed', 'private', '2025-07-20', '2025-07-20', '07:00:00', '08:00:00'),
(4, 'Birthday Party', 'At John’s house', 10, 'cancelled', 'public', '2025-07-30', '2025-07-30', '18:00:00', '22:00:00'),
(5, 'Grocery Shopping', 'Weekly groceries', 9, 'completed', 'private', '2025-07-22', '2025-07-22', '16:00:00', '17:00:00'),
(6, 'Dentist Visit', 'Teeth cleaning', 2, 'scheduled', 'private', '2025-07-28', '2025-07-28', '09:00:00', '10:00:00'),
(7, 'Flight to Berlin', 'Business trip', 7, 'scheduled', 'public', '2025-08-01', '2025-08-01', '06:00:00', '09:00:00'),
(8, 'Parent Meeting', 'School update', 8, 'ongoing', 'private', '2025-07-27', '2025-07-27', '11:00:00', '12:00:00'),
(9, 'Math Exam', 'Finals week', 4, 'scheduled', 'private', '2025-07-29', '2025-07-29', '13:00:00', '15:00:00'),
(10, 'Project Presentation', 'Client meeting', 1, 'scheduled', 'public', '2025-07-31', '2025-07-31', '10:00:00', '11:30:00');

-- Insert sample recurring appointments
INSERT INTO recurring_appointments (appointment_id, recurrence_pattern, repeat_until) VALUES
(1, 'weekly', '2025-12-31'),
(2, 'monthly', '2026-01-31'),
(3, 'daily', '2025-08-20'),
(4, 'yearly', '2030-07-30'),
(5, 'weekly', '2026-07-22'),
(6, 'monthly', '2026-07-28'),
(7, 'yearly', '2030-08-01'),
(8, 'daily', '2025-08-10'),
(9, 'weekly', '2026-07-29'),
(10, 'monthly', '2026-07-31');

-- Insert sample appointment shares
INSERT INTO appointment_shares (appointment_id, shared_with_user_id) VALUES
(1, 2),
(2, 3),
(3, 4),
(4, 5),
(5, 6),
(6, 7),
(7, 8),
(8, 9),
(9, 10),
(10, 1);
