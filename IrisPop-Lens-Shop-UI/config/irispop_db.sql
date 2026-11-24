-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 09:43 AM
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
-- Database: `irispop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brandID` int(11) NOT NULL,
  `brandName` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`brandID`, `brandName`, `description`, `created_at`) VALUES
(1, 'OLENS', 'Korean colored lenses brand - premium quality cosmetic lenses', '2026-06-26 06:13:13'),
(2, 'Acuvue', 'Johnson & Johnson vision care - trusted daily and monthly lenses', '2026-06-26 06:13:13'),
(3, 'FreshLook', 'Color contact lenses - vibrant and natural shades', '2026-06-26 06:13:13'),
(4, 'Bausch + Lomb', 'Eye health products - solutions and lenses', '2026-06-26 06:13:13'),
(5, 'Generic', 'Non-branded accessories and generic products', '2026-06-26 06:13:13');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `categoryID` int(11) NOT NULL,
  `categoryName` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`categoryID`, `categoryName`, `description`, `created_at`) VALUES
(1, 'colored', 'Colored and cosmetic contact lenses for style enhancement', '2026-07-31 07:20:02'),
(2, 'clear', 'Clear daily vision correction lenses', '2026-07-31 07:20:02'),
(3, 'accessories', 'Lens cases, cleaning solutions, eye drops and tools', '2026-07-31 07:20:02');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `orderItemID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subTotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`orderItemID`, `orderID`, `productID`, `quantity`, `subTotal`) VALUES
(6, 3, 46, 2, 50000.00),
(7, 4, 50, 2, 54000.00),
(8, 5, 46, 2, 50000.00),
(9, 6, 47, 1, 28000.00),
(10, 7, 52, 1, 29000.00),
(14, 11, 46, 2, 50000.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `orderDate` datetime DEFAULT current_timestamp(),
  `orderStatus` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `totalAmount` decimal(10,2) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `userID`, `orderDate`, `orderStatus`, `totalAmount`, `shipping_address`, `phone`) VALUES
(3, 21, '2026-10-09 09:31:06', 'shipped', 50000.00, 'May 131', '09402007781'),
(4, 14, '2026-10-09 11:21:54', 'delivered', 54000.00, 'Mdy 131', '09402007781'),
(5, 17, '2026-10-09 11:58:24', 'pending', 50000.00, 'Mdy 123', '09302007781'),
(6, 17, '2026-10-09 11:59:07', 'pending', 28000.00, 'mdy 123', '09302007781'),
(7, 21, '2026-10-17 12:18:40', 'shipped', 29000.00, 'MDY 112', '09902007781'),
(11, 23, '2026-10-18 11:30:14', 'processing', 50000.00, 'mdy 132', '09402007781');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `productID` int(11) NOT NULL,
  `productName` varchar(200) NOT NULL,
  `brandID` int(11) DEFAULT NULL,
  `categoryID` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`productID`, `productName`, `brandID`, `categoryID`, `price`, `stock`, `description`, `image`, `created_at`) VALUES
(46, 'Korean Natural Brown', 1, 1, 25000.00, 46, 'Natural brown color, perfect for daily wear', 'public/assets/images/product1.jpg', '2026-07-31 07:22:54'),
(47, 'IrisPop Pink Gray', 1, 1, 28000.00, 44, 'Mystical gray with pink tone', 'public/assets/images/product5.jpg', '2026-07-31 07:22:54'),
(48, 'Korean Natural Gray', 1, 1, 25000.00, 40, 'Natural gray color', 'public/assets/images/product7.jpg', '2026-07-31 07:22:54'),
(49, 'IrisPop Baby Blue', 1, 1, 28000.00, 35, 'Bright baby blue', 'public/assets/images/product8.jpg', '2026-07-31 07:22:54'),
(50, 'Spanish Real Brown', 1, 1, 27000.00, 42, 'Realistic brown', 'public/assets/images/new-spanish-brown.jpg', '2026-07-31 07:22:54'),
(51, 'Spanish Real Gray', 1, 1, 27000.00, 38, 'Realistic gray', 'public/assets/images/new-spanish-gray.jpg', '2026-07-31 07:22:54'),
(52, 'Scandi Hazel', 1, 1, 29000.00, 60, 'Natural hazel color', 'public/assets/images/new-scandi-hazel.jpg', '2026-07-31 07:22:54'),
(53, 'Russian Velvet Brown', 1, 1, 32000.00, 29, 'Velvet brown finish', 'public/assets/images/new-russian-brown.jpg', '2026-07-31 07:22:54'),
(54, 'Russian Velvet Gray', 1, 1, 32000.00, 25, 'Velvet gray finish', 'public/assets/images/new-russian-gray.jpg', '2026-07-31 07:22:54'),
(55, 'Daily Disposable Clear', 2, 2, 15000.00, 100, 'Comfortable daily disposable lenses', 'public/assets/images/product2.jpg', '2026-07-31 07:22:54'),
(56, 'Daily Moisture Lens', 2, 2, 18000.00, 85, 'All-day moisture for comfortable wear', 'public/assets/images/product9.jpg', '2026-07-31 07:22:54'),
(57, 'Lens Solution 360ml', 4, 3, 8000.00, 200, 'Multi-purpose cleaning and storage solution', 'public/assets/images/product4.jpg', '2026-07-31 07:22:54'),
(58, 'Lens Case Set', 5, 3, 3000.00, 150, 'Lens case with tweezers and applicator', 'public/assets/images/product6.jpg', '2026-07-31 07:22:54'),
(59, 'Eye Drops', 4, 3, 5000.00, 120, 'Rewetting drops for dry eyes', 'public/assets/images/product13.jpg', '2026-07-31 07:22:54'),
(60, 'Lens Tweezers', 5, 3, 2000.00, 100, 'Soft grip tweezers for lens handling', 'public/assets/images/product14.jpg', '2026-07-31 07:22:54'),
(69, 'Korean Glow Brown', 4, 2, 26000.00, 39, 'Warm brown tone-up shade blends naturally for everyday wear', 'public/assets/images/black_friday_1.webp', '2026-10-18 05:02:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phoneNumber` varchar(20) DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `name`, `email`, `password`, `address`, `phoneNumber`, `role`, `created_at`) VALUES
(14, 'Cameron Moe', 'moecameron9@gmail.com', '$2y$10$JWYaf2IYz.r4fyfsjGIMm.jS56Cz66Mo8Pn6Ak1QHRv7.bnRgXI8e', '62/130', '09402007781', 'admin', '2026-08-06 15:50:45'),
(17, 'Yuyu', 'yuyu@gmail.com', '$2y$10$sNKvXWXe.HFAU/yi2cqOMe74Ula1ffij/LCz5qA4hJn0qidNhVBtS', 'Mandalay', '09902007781', 'customer', '2026-08-28 12:24:30'),
(20, 'Moe Wai Wai Tun', 'moemoe@gmail.com', '$2y$10$DtRG7/EO.rbhSsjzTfNplezmKd4omug6IbJdw3z3WYqv6bQ393tDa', '61 ,131B', '09402007781', 'admin', '2026-10-04 08:23:12'),
(21, 'Star', 'star@gmail.com', '$2y$10$QSuLZTbr0WIhxNzFNsgajOZTEtAOFFEU6dlzLf98EfUPHWuq9A6pi', 'Mdy 131', '09402007781', 'customer', '2026-10-07 06:03:38'),
(22, 'Wai yan moe', 'waiyanmoe@gmail.com', '$2y$10$qVWaOGTbnLucNkn9LMUzbOtHqumGiUiFy4Io623hkcJazZnch9Ykm', 'Mdy 139', '09802007781', 'customer', '2026-10-08 06:32:04'),
(23, 'rose me', 'rose@gmail.com', '$2y$10$TxnfEF5BzWZkoMtiOG7CyuHJYGGMy3rJCIAwv9IFBVL/zfLTn.eBK', 'Mdy 134', '09302007781', 'customer', '2026-10-18 04:58:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brandID`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`orderItemID`),
  ADD KEY `orderID` (`orderID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`productID`),
  ADD KEY `brandID` (`brandID`),
  ADD KEY `categoryID` (`categoryID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brandID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `orderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `products` (`productID`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brandID`) REFERENCES `brands` (`brandID`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`categoryID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
