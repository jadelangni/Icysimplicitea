-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2026 at 05:33 PM
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
-- Database: `simplicitea`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `user_id`, `branch_id`, `date`, `time_in`, `time_out`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-12-18', '11:18:25', '11:18:40', 'late', NULL, '2025-12-18 03:18:25', '2025-12-18 03:18:40');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `manager_name` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `phone`, `manager_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Oslob Main', 'Main Street, Oslob, Cebu', '+63 912 345 6789', 'Maria Santos', 1, '2025-11-27 00:48:28', '2025-11-27 00:48:28'),
(2, 'Santander Poblacion', 'Poblacion, Santander, Cebu', '+63 912 345 6788', 'Juan dela Cruz', 1, '2025-11-27 00:48:28', '2025-11-27 00:48:28'),
(3, 'Looc Branch', 'Looc, Oslob, Cebu', '+63 912 345 6787', 'Ana Garcia', 1, '2025-11-27 00:48:28', '2025-11-27 00:48:28');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-cashier@simplicitea.com|127.0.0.1', 'i:2;', 1769277236),
('laravel-cache-cashier@simplicitea.com|127.0.0.1:timer', 'i:1769277236;', 1769277236);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Milk Tea', 'Various flavored milk teas', 1, '2025-11-27 00:48:28', '2025-11-27 00:48:28'),
(2, 'Fruit Tea', 'Fresh fruit-based teas', 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(3, 'Coffee', 'Hot and cold coffee drinks', 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(4, 'Snacks', 'Light snacks and pastries', 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'pieces',
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_stock_level` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `description`, `unit`, `quantity`, `min_stock_level`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Milk Tea Powder', 'Premium quality tea powder for milk tea base', 'kg', 15.50, 5.00, 1, '2025-12-03 08:13:13', '2025-12-03 08:13:13'),
(2, 'Fresh Milk', 'Whole milk for creamy texture', 'liters', 1.90, 5.00, 1, '2025-12-03 08:13:13', '2025-12-18 04:42:23'),
(3, 'Tapioca Pearls', 'Black tapioca pearls for boba', 'kg', 8.00, 3.00, 1, '2025-12-03 08:13:13', '2025-12-03 08:13:13'),
(4, 'Brown Sugar', 'Organic brown sugar for sweetening', 'kg', 12.00, 2.00, 1, '2025-12-03 08:13:13', '2025-12-03 08:13:13'),
(5, 'Ice Cubes', 'Filtered ice for cold beverages', 'kg', 0.50, 2.00, 1, '2025-12-03 08:13:13', '2025-12-03 08:13:13'),
(6, 'Chicken', 'For Chicken Sandwich', 'kg', 6.00, 4.00, 1, '2025-12-03 09:16:40', '2025-12-03 09:16:40');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `branch_id`, `product_id`, `quantity`, `min_stock_level`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 65, 5, '2025-11-27 00:48:31', '2025-12-03 09:50:51'),
(2, 1, 2, 93, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(3, 1, 3, 29, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(4, 1, 4, 31, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(5, 1, 5, 23, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(6, 1, 6, 27, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(7, 1, 7, 65, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(8, 1, 8, 81, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(9, 1, 9, 67, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(10, 1, 10, 47, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31'),
(11, 1, 11, 80, 5, '2025-11-27 00:48:31', '2025-11-27 00:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_10_144832_create_branches_table', 1),
(5, '2025_11_10_144953_create_categories_table', 1),
(6, '2025_11_10_145012_create_products_table', 1),
(7, '2025_11_10_145019_create_inventory_table', 1),
(8, '2025_11_10_145155_create_sales_table', 1),
(9, '2025_11_10_145202_create_sales_items_table', 1),
(10, '2025_11_10_145353_add_role_and_branch_to_users_table', 1),
(11, '2025_12_03_000000_add_options_to_products_table', 2),
(12, '2025_12_03_120000_add_options_to_sales_items_table', 3),
(13, '2025_12_03_140000_update_product_options_format', 4),
(14, '2025_12_03_161014_create_ingredients_table', 5),
(15, '2025_12_18_000000_create_attendances_table', 6),
(16, '2026_01_25_000000_create_user_activity_logs_table', 7),
(17, '2026_01_25_100000_add_qr_token_to_users_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `image`, `options`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Classic Milk Tea', 'Traditional milk tea with tapioca pearls', 65.00, 'products/QRNEE2z6wPUrBswN0p2k5NuI0coLHJndRsdVVioK.jpg', '[{\"name\":\"Size\",\"values\":[{\"label\":\"16oz\",\"price\":65},{\"label\":\"22oz\",\"price\":80}]}]', 1, '2025-11-27 00:48:29', '2025-12-18 05:23:20'),
(2, 1, 'Taro Milk Tea', 'Creamy taro flavored milk tea', 75.00, NULL, '[{\"name\":\"Size\",\"values\":[{\"label\":\"16oz\",\"price\":65},{\"label\":\"22oz\",\"price\":80}]}]', 1, '2025-11-27 00:48:29', '2025-12-03 00:46:29'),
(3, 1, 'Matcha Milk Tea', 'Japanese matcha milk tea', 60.00, NULL, '[{\"name\":\"Size\",\"values\":[{\"label\":\"16oz\",\"price\":60}]},{\"name\":\"Size\",\"values\":[{\"label\":\"22oz\",\"price\":80}]}]', 1, '2025-11-27 00:48:29', '2025-12-03 00:58:08'),
(4, 1, 'Chocolate Milk Tea', 'Rich chocolate milk tea', 70.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(5, 2, 'Lemon Honey Tea', 'Refreshing lemon tea with honey', 60.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(6, 2, 'Passion Fruit Tea', 'Tropical passion fruit tea', 70.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(7, 2, 'Mango Green Tea', 'Fresh mango with green tea', 65.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(8, 3, 'Iced Coffee', 'Cold brew coffee served with ice', 55.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(9, 3, 'Cappuccino', 'Classic cappuccino with steamed milk', 85.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(10, 4, 'Chicken Sandwich', 'Grilled chicken sandwich', 120.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29'),
(11, 4, 'Cookies', 'Homemade chocolate chip cookies', 45.00, NULL, NULL, 1, '2025-11-27 00:48:29', '2025-11-27 00:48:29');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cash','card','gcash') NOT NULL,
  `status` enum('completed','refunded','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `receipt_number`, `branch_id`, `user_id`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `amount_paid`, `change_amount`, `payment_method`, `status`, `created_at`, `updated_at`) VALUES
(4, 'OSL-20251203-0001', 1, 1, 80.00, 0.00, 0.00, 80.00, 100.00, 20.00, 'cash', 'completed', '2025-12-03 09:50:51', '2025-12-03 09:50:51'),
(5, 'OSL-20251203-0002', 1, 1, 145.00, 0.00, 0.00, 145.00, 150.00, 5.00, 'cash', 'completed', '2025-12-03 09:54:27', '2025-12-03 09:54:27'),
(6, 'OSL-20251203-0003', 1, 1, 145.00, 0.00, 0.00, 145.00, 150.00, 5.00, 'cash', 'completed', '2025-12-03 09:54:35', '2025-12-03 09:54:35'),
(7, 'OSL-20251203-0004', 1, 1, 145.00, 0.00, 0.00, 145.00, 150.00, 5.00, 'cash', 'completed', '2025-12-03 09:54:51', '2025-12-03 09:54:51'),
(8, 'OSL-20251203-0005', 1, 1, 145.00, 0.00, 0.00, 145.00, 150.00, 5.00, 'cash', 'completed', '2025-12-03 09:55:17', '2025-12-03 09:55:17'),
(9, 'OSL-20251203-0006', 1, 1, 125.00, 0.00, 0.00, 125.00, 150.00, 25.00, 'cash', 'completed', '2025-12-03 09:57:36', '2025-12-03 09:57:36'),
(10, 'OSL-20251203-0007', 1, 1, 380.00, 0.00, 0.00, 380.00, 500.00, 120.00, 'cash', 'completed', '2025-12-03 10:02:25', '2025-12-03 10:02:25'),
(11, 'OSL-20251203-0008', 1, 1, 325.00, 0.00, 0.00, 325.00, 400.00, 75.00, 'cash', 'completed', '2025-12-03 10:07:09', '2025-12-03 10:07:09'),
(12, 'OSL-20251203-0009', 1, 1, 60.00, 0.00, 0.00, 60.00, 100.00, 40.00, 'cash', 'completed', '2025-12-03 10:08:28', '2025-12-03 10:08:28'),
(13, 'OSL-20251203-0010', 1, 1, 135.00, 0.00, 0.00, 135.00, 200.00, 65.00, 'cash', 'completed', '2025-12-03 10:08:43', '2025-12-03 10:08:43'),
(14, 'OSL-20251203-0011', 1, 1, 130.00, 0.00, 0.00, 130.00, 150.00, 20.00, 'cash', 'completed', '2025-12-03 10:08:56', '2025-12-03 10:08:56'),
(15, 'OSL-20251203-0012', 1, 1, 140.00, 0.00, 0.00, 140.00, 140.00, 0.00, 'gcash', 'completed', '2025-12-03 10:09:37', '2025-12-03 10:09:37'),
(16, 'OSL-20251216-0001', 1, 1, 65.00, 0.00, 0.00, 65.00, 100.00, 35.00, 'cash', 'completed', '2025-12-16 07:47:18', '2025-12-16 07:47:18'),
(17, 'OSL-20251216-0002', 1, 1, 80.00, 0.00, 0.00, 80.00, 100.00, 20.00, 'cash', 'completed', '2025-12-16 07:51:31', '2025-12-16 07:51:31'),
(18, 'OSL-20251216-0003', 1, 1, 265.00, 0.00, 0.00, 265.00, 300.00, 35.00, 'cash', 'completed', '2025-12-16 08:01:08', '2025-12-16 08:01:08'),
(19, 'OSL-20251217-0001', 1, 1, 140.00, 0.00, 0.00, 140.00, 200.00, 60.00, 'cash', 'completed', '2025-12-17 05:41:19', '2025-12-17 05:41:19'),
(20, 'OSL-20251217-0002', 1, 2, 65.00, 0.00, 0.00, 65.00, 100.00, 35.00, 'cash', 'completed', '2025-12-17 05:44:56', '2025-12-17 05:44:56'),
(21, 'OSL-20251219-0001', 1, 1, 65.00, 0.00, 0.00, 65.00, 100.00, 35.00, 'cash', 'completed', '2025-12-18 16:51:35', '2025-12-18 16:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `sales_items`
--

CREATE TABLE `sales_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_items`
--

INSERT INTO `sales_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `total_price`, `options`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-03 09:50:51', '2025-12-03 09:50:51'),
(2, 5, 1, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-03 09:54:27', '2025-12-03 09:54:27'),
(3, 5, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-03 09:54:27', '2025-12-03 09:54:27'),
(4, 6, 1, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-03 09:54:35', '2025-12-03 09:54:35'),
(5, 6, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-03 09:54:35', '2025-12-03 09:54:35'),
(6, 7, 1, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-03 09:54:51', '2025-12-03 09:54:51'),
(7, 7, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-03 09:54:51', '2025-12-03 09:54:51'),
(8, 8, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-03 09:55:17', '2025-12-03 09:55:17'),
(9, 8, 1, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-03 09:55:17', '2025-12-03 09:55:17'),
(10, 9, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-03 09:57:36', '2025-12-03 09:57:36'),
(11, 9, 5, 1, 60.00, 60.00, NULL, '2025-12-03 09:57:36', '2025-12-03 09:57:36'),
(12, 10, 5, 1, 60.00, 60.00, NULL, '2025-12-03 10:02:25', '2025-12-03 10:02:25'),
(13, 10, 10, 2, 120.00, 240.00, NULL, '2025-12-03 10:02:25', '2025-12-03 10:02:25'),
(14, 10, 3, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-03 10:02:25', '2025-12-03 10:02:25'),
(15, 11, 9, 1, 85.00, 85.00, NULL, '2025-12-03 10:07:09', '2025-12-03 10:07:09'),
(16, 11, 8, 1, 55.00, 55.00, NULL, '2025-12-03 10:07:09', '2025-12-03 10:07:09'),
(17, 11, 7, 1, 65.00, 65.00, NULL, '2025-12-03 10:07:09', '2025-12-03 10:07:09'),
(18, 11, 10, 1, 120.00, 120.00, NULL, '2025-12-03 10:07:09', '2025-12-03 10:07:09'),
(19, 12, 5, 1, 60.00, 60.00, NULL, '2025-12-03 10:08:28', '2025-12-03 10:08:28'),
(20, 13, 6, 1, 70.00, 70.00, NULL, '2025-12-03 10:08:43', '2025-12-03 10:08:43'),
(21, 13, 7, 1, 65.00, 65.00, NULL, '2025-12-03 10:08:43', '2025-12-03 10:08:43'),
(22, 14, 11, 1, 45.00, 45.00, NULL, '2025-12-03 10:08:56', '2025-12-03 10:08:56'),
(23, 14, 9, 1, 85.00, 85.00, NULL, '2025-12-03 10:08:56', '2025-12-03 10:08:56'),
(24, 15, 9, 1, 85.00, 85.00, NULL, '2025-12-03 10:09:37', '2025-12-03 10:09:37'),
(25, 15, 8, 1, 55.00, 55.00, NULL, '2025-12-03 10:09:37', '2025-12-03 10:09:37'),
(26, 16, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-16 07:47:18', '2025-12-16 07:47:18'),
(27, 17, 1, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-16 07:51:31', '2025-12-16 07:51:31'),
(28, 18, 5, 1, 60.00, 60.00, NULL, '2025-12-16 08:01:08', '2025-12-16 08:01:08'),
(29, 18, 9, 1, 85.00, 85.00, NULL, '2025-12-16 08:01:08', '2025-12-16 08:01:08'),
(30, 18, 10, 1, 120.00, 120.00, NULL, '2025-12-16 08:01:08', '2025-12-16 08:01:08'),
(31, 19, 1, 1, 80.00, 80.00, '{\"Size\":\"22oz\"}', '2025-12-17 05:41:19', '2025-12-17 05:41:19'),
(32, 19, 5, 1, 60.00, 60.00, NULL, '2025-12-17 05:41:19', '2025-12-17 05:41:19'),
(33, 20, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-17 05:44:56', '2025-12-17 05:44:56'),
(34, 21, 1, 1, 65.00, 65.00, '{\"Size\":\"16oz\"}', '2025-12-18 16:51:35', '2025-12-18 16:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('TqYz6NsQKZzO1D1pAsjhy80kncuXdFJZhEXU6xq4', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidVltZVBFNnQ0OUVGakJaYVdrSVNTS09sV3hKVzBZZm1JeDVQWExkOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1769277814);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('owner','supervisor','cashier') NOT NULL DEFAULT 'cashier',
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `qr_token` varchar(64) DEFAULT NULL,
  `qr_token_generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `role`, `password`, `remember_token`, `qr_token`, `qr_token_generated_at`, `created_at`, `updated_at`, `branch_id`, `is_active`) VALUES
(1, 'Admin User', 'admin@simplicitea.com', NULL, 'owner', '$2y$12$/AdP0Uzqe2iGV3Kmm9qmtej84/7DuUooyJKCW95meSLlV351sst6G', NULL, NULL, NULL, '2025-11-27 00:48:29', '2025-11-27 00:48:29', 1, 1),
(2, 'John Cashier', 'cashier1@simplicitea.com', NULL, 'cashier', '$2y$12$ejmu7QMLIt4VjOoBEL/wd.REiK5f3bix95145SQ5ABlJrk04rIzBK', NULL, 'LYvtgMPHnsUPZse3QLqY9lzgZHuBLmZTOC7Ghx1myznWOf6X6FBlss9HexYT5xQi', '2026-01-24 09:58:59', '2025-11-27 00:48:31', '2026-01-24 09:58:59', 1, 1),
(3, 'Jane Supervisor', 'supervisor1@simplicitea.com', NULL, 'supervisor', '$2y$12$rcE2OeGzmetKRotlZNuOVucw3BhgvkOPObk1jCtaJzuJG6cYdqV4i', NULL, NULL, NULL, '2025-11-27 00:48:31', '2025-11-27 00:48:31', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` enum('login','logout') NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `branch_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, '2026-01-24 09:52:48', '2026-01-24 09:52:48'),
(2, 2, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, '2026-01-24 09:53:40', '2026-01-24 09:53:40'),
(3, 2, 'logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, '2026-01-24 10:01:30', '2026-01-24 10:01:30'),
(4, 1, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 1, '2026-01-24 10:01:37', '2026-01-24 10:01:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_user_id_date_unique` (`user_id`,`date`),
  ADD KEY `attendances_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_branch_id_product_id_unique` (`branch_id`,`product_id`),
  ADD KEY `inventory_product_id_foreign` (`product_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_receipt_number_unique` (`receipt_number`),
  ADD KEY `sales_branch_id_foreign` (`branch_id`),
  ADD KEY `sales_user_id_foreign` (`user_id`);

--
-- Indexes for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_items_sale_id_foreign` (`sale_id`),
  ADD KEY `sales_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_qr_token_unique` (`qr_token`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_activity_logs_branch_id_foreign` (`branch_id`),
  ADD KEY `user_activity_logs_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `user_activity_logs_action_created_at_index` (`action`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `sales_items`
--
ALTER TABLE `sales_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_items`
--
ALTER TABLE `sales_items`
  ADD CONSTRAINT `sales_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `user_activity_logs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
