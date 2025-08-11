-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2025 at 10:33 PM
-- Server version: 8.0.42
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `wemart`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int NOT NULL,
  `user_id` int NOT NULL,
  `address_line` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `address_type` enum('billing','shipping') COLLATE utf8mb4_general_ci DEFAULT 'billing',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `address_line`, `city`, `state`, `postal_code`, `country`, `address_type`, `created_at`) VALUES
(5, 5, '12 king street', 'ayr', 'ontario', 'N2P2X5', 'canada', 'billing', '2025-07-31 20:50:53'),
(21, 21, '12 main street', 'new york', 'new york', 'N2P 2X2', 'usa', 'billing', '2025-08-03 00:41:08'),
(22, 22, '78 York street', 'Gotham', 'Newyork', 'N2P2S2', 'usa', 'billing', '2025-08-03 00:44:52'),
(26, 26, '30 water street', 'cambridge', 'ontario', 'n2p2s2', 'canada', 'billing', '2025-08-03 17:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `category_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`category_id`, `name`) VALUES
(1, 'Tips'),
(2, 'News'),
(3, 'Trends');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `post_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `brand_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(11, 1, 2, 2, '2025-08-02 05:38:34'),
(13, 1, 2, 1, '2025-08-02 18:08:00'),
(14, 1, 2, 1, '2025-08-02 18:10:05'),
(23, 22, 2, 1, '2025-08-03 00:53:41'),
(157, 26, 1, 20, '2025-08-03 22:48:50'),
(160, 5, 1, 1, '2025-08-03 23:38:21');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `image`) VALUES
(1, 'Electronics', NULL),
(2, 'Clothing', NULL),
(3, 'Home & Furniture', NULL),
(4, 'Groceries', NULL),
(5, 'Toys', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `verification_id` int NOT NULL,
  `user_id` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL DEFAULT ((now() + interval 24 hour))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`verification_id`, `user_id`, `token`, `created_at`, `expires_at`) VALUES
(1, 1, 'abc123xyz789', '2025-07-11 23:04:58', '2025-07-12 23:04:58'),
(2, 2, 'def456uvw012', '2025-07-11 23:04:58', '2025-07-12 23:04:58'),
(3, 3, 'd64b647644badba18e8695c3c2f5ac5c', '2025-07-11 23:08:18', '2025-07-13 05:08:18');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','completed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `address_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `order_date`, `status`, `address_id`) VALUES
(1, 5, 799.98, '2025-07-31 21:14:37', 'pending', NULL),
(2, 5, 824.97, '2025-07-31 21:36:07', 'pending', NULL),
(3, 5, 799.98, '2025-07-31 21:50:49', 'pending', NULL),
(4, 5, 824.97, '2025-07-31 21:57:06', 'pending', NULL),
(5, 5, 824.97, '2025-07-31 22:07:55', 'pending', NULL),
(6, 5, 24.99, '2025-07-31 22:08:56', 'pending', NULL),
(7, 5, 24.99, '2025-07-31 22:18:26', 'pending', NULL),
(8, 15, 24.99, '2025-08-02 17:46:32', 'pending', NULL),
(9, 15, 24.99, '2025-08-02 18:28:35', 'pending', NULL),
(10, 18, 89.99, '2025-08-02 18:40:08', 'pending', NULL),
(11, 5, 24.99, '2025-08-02 18:58:03', 'pending', NULL),
(12, 5, 24.99, '2025-08-03 00:23:07', 'pending', NULL),
(13, 5, 24.99, '2025-08-03 00:23:59', 'pending', NULL),
(14, 21, 89.99, '2025-08-03 00:43:10', 'pending', NULL),
(15, 21, 24.99, '2025-08-03 00:43:44', 'pending', NULL),
(16, 22, 24.99, '2025-08-03 00:51:57', 'pending', NULL),
(17, 5, 749.97, '2025-08-03 06:34:38', 'pending', NULL),
(18, 5, 0.00, '2025-08-03 06:35:18', 'pending', NULL),
(19, 5, 89.99, '2025-08-03 06:39:20', 'pending', NULL),
(20, 5, 118.97, '2025-08-03 06:40:05', 'pending', NULL),
(21, 5, 93.96, '2025-08-03 06:43:07', 'pending', NULL),
(22, 5, 869.96, '2025-08-03 06:44:56', 'pending', NULL),
(23, 5, 24.99, '2025-08-03 06:50:51', 'pending', NULL),
(24, 5, 929.96, '2025-08-03 06:55:28', 'pending', NULL),
(25, 5, 24.99, '2025-08-03 07:06:06', 'pending', NULL),
(26, 5, 124.95, '2025-08-03 17:12:10', 'pending', NULL),
(27, 5, 399.99, '2025-08-03 21:34:28', 'pending', NULL),
(28, 5, 134.93, '2025-08-03 23:22:01', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 2, 399.99),
(2, 2, 1, 2, 399.99),
(3, 2, 2, 1, 24.99),
(4, 3, 1, 2, 399.99),
(5, 4, 1, 2, 399.99),
(6, 4, 2, 1, 24.99),
(7, 5, 1, 2, 399.99),
(8, 5, 2, 1, 24.99),
(9, 6, 2, 1, 24.99),
(10, 7, 2, 1, 24.99),
(11, 8, 2, 1, 24.99),
(12, 9, 2, 1, 24.99),
(13, 10, 3, 1, 89.99),
(14, 11, 2, 1, 24.99),
(15, 12, 2, 1, 24.99),
(16, 13, 2, 1, 24.99),
(17, 14, 3, 1, 89.99),
(18, 15, 2, 1, 24.99),
(19, 16, 2, 1, 24.99),
(20, 17, 8, 1, 699.99),
(21, 17, 2, 2, 24.99),
(22, 19, 3, 1, 89.99),
(23, 20, 4, 1, 3.99),
(24, 20, 3, 1, 89.99),
(25, 20, 2, 1, 24.99),
(26, 21, 7, 1, 49.99),
(27, 21, 10, 1, 14.99),
(28, 21, 4, 1, 3.99),
(29, 21, 2, 1, 24.99),
(30, 22, 7, 1, 49.99),
(31, 22, 5, 1, 19.99),
(32, 22, 1, 2, 399.99),
(33, 23, 2, 1, 24.99),
(34, 24, 8, 1, 699.99),
(35, 24, 6, 1, 129.99),
(36, 24, 7, 2, 49.99),
(37, 25, 2, 1, 24.99),
(38, 26, 2, 5, 24.99),
(39, 27, 1, 1, 399.99),
(40, 28, 3, 1, 89.99),
(41, 28, 4, 5, 3.99),
(42, 28, 2, 1, 24.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `category_id` int DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stock` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `featured` tinyint(1) DEFAULT '0',
  `deal_price` decimal(10,2) DEFAULT NULL,
  `sales_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `category_id`, `image`, `stock`, `created_at`, `featured`, `deal_price`, `sales_count`) VALUES
(1, '4K Smart TV', '55-inch LED Smart TV with HDR', 399.99, 1, '4k tv.png', 20, '2025-07-11 13:57:29', 0, NULL, 0),
(2, 'Men’s Denim Jeans', 'Comfort-fit blue jeans', 24.99, 2, 'jeans.jpg', 99, '2025-07-11 13:57:29', 0, NULL, 0),
(3, 'Coffee Table', 'Modern wooden coffee table', 89.99, 3, 'table.jpg\r\n', 14, '2025-07-11 13:57:29', 0, NULL, 0),
(4, 'Organic Apples', '1lb bag of organic gala apples', 3.99, 4, 'apples.jpg', 195, '2025-07-11 13:57:29', 0, NULL, 0),
(5, 'Action Figure Set', 'Superhero action figure collection', 19.99, 5, 'toy.jpg', 50, '2025-07-11 13:57:29', 0, NULL, 0),
(6, 'Game Console', 'Classic handheld game console with color display.', 129.99, 1, 'gameconsole.jpg', 30, '2025-08-03 04:34:26', 1, NULL, 0),
(7, 'Wireless Joystick', 'Ergonomic gaming joystick controller.', 49.99, 1, 'joystick.jpg', 45, '2025-08-03 04:34:26', 1, NULL, 0),
(8, 'Smartphone', 'Modern smartphone with sleek design and powerful features.', 699.99, 1, 'phone.jpg', 60, '2025-08-03 04:34:26', 1, NULL, 0),
(9, 'Fresh Strawberries', 'Freshly picked juicy strawberries – 1 lb.', 4.49, 4, 'strawberry.jpg', 150, '2025-08-03 04:34:26', 0, NULL, 0),
(10, 'Teddy Bear', 'Soft and cuddly teddy bear for kids.', 14.99, 5, 'teddy.jpg', 100, '2025-08-03 04:34:26', 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_general_ci DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(3, 'Mary Jain', 'maryjainrj@gmail.com', '$2y$10$O3lEGIWXffcfvQQLOPwnpuN7g2sYP/PNi.5DDx9OTSUI64Rk2i0hK', '3828852320', 'user', '2025-07-11 23:08:18'),
(5, 'tester', 'test@gmail.com', '$2y$10$wOod39Y/42vPXjp4Gb4vx.X7uCw1mi/IcXIn91c.1Sn3m3H9EY2dy', '1234567891', 'user', '2025-07-31 20:50:53'),
(6, 'Mohamed', 'mdijas@gmail.com', '$2y$10$oNKNyJGixogmftv/w2LwEerSLDm0pTZOlJesG7yQX.srouEKHch6a', '5296422222', 'user', '2025-08-01 05:37:59'),
(21, 'Tony Stark', 'tony@gmail.com', '$2y$10$NGW7n5IKFy6hm45q7/fBaOxK5eg6LixYcnLmDsachP6hWAMJ.ihTC', '9876543210', 'user', '2025-08-03 00:41:08'),
(22, 'peter parker', 'peter@gmail.com', '$2y$10$vhJvJBp0HTH/jwyshWkTb.3AjDuvA0dG8gpM5.EmCSzY7wr9CcOcu', '9876543211', 'user', '2025-08-03 00:44:52'),
(26, 'testadmin', 'admin@gmail.com', '$2y$10$AvKQTGQldL71882iX/Nbs.UBL2z7L3rizUPX1yy2iVCOC4o2ZLOxm', '9876543210', 'admin', '2025-08-03 17:17:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `addresses_ibfk_1` (`user_id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`verification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `post_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `verification_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
