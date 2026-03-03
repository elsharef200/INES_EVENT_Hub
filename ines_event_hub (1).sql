-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 02, 2026 at 01:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ines_event_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `venue` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `poster_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `organizer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `price`, `venue`, `event_date`, `event_time`, `poster_image`, `created_at`, `organizer_id`) VALUES
(1, 'Kigali Music Festival', 'Biggest music festival in Rwanda!', 25000.00, 'BK Arena', '2026-03-11', '18:00:00', NULL, '2026-03-01 13:38:12', NULL),
(2, 'Tech Conference 2024', 'Learn from industry experts', 15000.00, 'Kigali Convention Centre', '2026-03-16', '09:00:00', NULL, '2026-03-01 13:38:12', NULL),
(3, 'Food Festival', 'Taste the best local cuisine', 10000.00, 'Kigali Heights', '2026-03-06', '12:00:00', NULL, '2026-03-01 13:38:12', NULL),
(5, 'INES Tech Innovation Day', 'A technology showcase where students present innovative projects and startups.', 1500.00, 'INES Main Hall', '2026-03-07', '18:00:00', NULL, '2026-03-01 20:53:03', NULL),
(6, 'Freshers Welcome Party', 'Social event to welcome new students with music, networking, and fun activities.', 3000.00, 'INES Sports Ground', '2026-04-11', '02:00:00', NULL, '2026-03-01 20:55:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `event_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `ticket_number`, `event_id`, `customer_name`, `customer_email`, `customer_phone`, `quantity`, `total_amount`, `payment_status`, `created_at`) VALUES
(1, 'INES-2026-19131', 3, 'NIYO GANZA Aristide', 'gnaristide@gmail.com', '', 1, 10000.00, 'paid', '2026-03-01 13:46:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_type` enum('admin','organizer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `user_type`, `created_at`) VALUES
(1, 'admin', '$2y$10$ImGra3Ufpi06iMHA28x50uX3kx3CaX6FHyCP9u7Eh7T90byNl.ykq', 'System Admin', 'admin', '2026-03-01 13:38:12'),
(2, 'organizer', '$2y$10$pRfsM0N5cUdWEYLP7WClse/cKwfRaDJnsZU01Wf4QtGaS7rBTgyGa', 'Main Organizer', 'organizer', '2026-03-01 13:38:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
