-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 31, 2024 at 01:21 PM
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
-- Database: `web-based`
--

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` enum('Beverage','Food','Condiment','Other') NOT NULL,
  `quantity_in_stock` int(11) NOT NULL,
  `stock_status` enum('in stock','low stock','out of stock') NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `last_restocked_date` datetime NOT NULL,
  `date_added` date NOT NULL,
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `category`, `quantity_in_stock`, `stock_status`, `supplier_name`, `price_per_unit`, `last_restocked_date`, `date_added`, `supplier_id`) VALUES
(1, 'coffee bean', 'Beverage', 15, 'in stock', 'letscoopi', 30.09, '2024-10-14 00:00:00', '2024-10-15', 1),
(2, 'cup', 'Beverage', 10, 'in stock', 'wilayah plastik', 2.02, '2024-10-08 00:00:00', '2024-10-09', NULL),
(3, 'doughnut', 'Beverage', 0, 'in stock', 'hellosugar', 7.02, '2024-10-18 00:00:00', '2024-10-19', NULL),
(4, 'pans', 'Beverage', 9, 'in stock', 'test', 5.00, '2024-10-04 00:00:00', '2024-10-11', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_supplier` (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
