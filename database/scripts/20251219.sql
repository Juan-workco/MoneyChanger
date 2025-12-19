CREATE TABLE `currencies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currencies_code_unique` (`code`),
  KEY `currencies_created_by_foreign` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `total_transactions` int NOT NULL DEFAULT '0',
  `total_volume` decimal(15,2) NOT NULL DEFAULT '0.00',
  `agent_id` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_agent_id_foreign` (`agent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `exchange_rates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `currency_from_id` int unsigned NOT NULL,
  `currency_to_id` int unsigned NOT NULL,
  `buy_rate` decimal(10,6) NOT NULL,
  `sell_rate` decimal(10,6) NOT NULL,
  `profit_margin` decimal(10,6) GENERATED ALWAYS AS ((`sell_rate` - `buy_rate`)) STORED,
  `effective_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exchange_rates_currency_from_id_foreign` (`currency_from_id`),
  KEY `exchange_rates_currency_to_id_foreign` (`currency_to_id`),
  KEY `exchange_rates_created_by_foreign` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `receiving_accounts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `account_type` enum('bank','usdt','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_type` enum('general','currency','payment_method') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_setting_key_unique` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int unsigned NOT NULL,
  `currency_from_id` int unsigned NOT NULL,
  `currency_to_id` int unsigned NOT NULL,
  `exchange_rate_id` int unsigned NOT NULL,
  `amount_from` decimal(15,2) NOT NULL,
  `amount_to` decimal(15,2) NOT NULL,
  `buy_rate` decimal(10,6) NOT NULL,
  `sell_rate` decimal(10,6) NOT NULL,
  `payment_method` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','accept','sent','cancel') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `transaction_date` datetime NOT NULL,
  `agent_id` int unsigned DEFAULT NULL,
  `agent_commission` decimal(10,2) NOT NULL DEFAULT '0.00',
  `profit_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`),
  KEY `transactions_customer_id_foreign` (`customer_id`),
  KEY `transactions_currency_from_id_foreign` (`currency_from_id`),
  KEY `transactions_currency_to_id_foreign` (`currency_to_id`),
  KEY `transactions_exchange_rate_id_foreign` (`exchange_rate_id`),
  KEY `transactions_agent_id_foreign` (`agent_id`),
  KEY `transactions_created_by_foreign` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','agent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `role_id` int unsigned DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `users_role_id_foreign` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permission_role` (
  `permission_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users (Admin)
INSERT INTO `users` (`username`, `name`, `email`, `password`, `role`, `role_id`, `status`, `super_admin`, `remember_token`, `last_login_at`, `created_at`, `updated_at`) VALUES
('admin', 'Admin', 'admin@moneychanger.com', '$2y$10$j.6RT.cjlqJWsI/ZfuHJ1OodEcG/7EDGiKOK72ZaRQgQcYUao2bbO', 'admin', 1, 'active', 1, NULL, NULL, NOW(), NOW());

-- Roles
INSERT INTO `roles` (`name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
('Super Admin', 'super-admin', 'Full access to everything', NOW(), NOW()),
('Agent', 'agent', 'Standard agent access', NOW(), NOW());

-- Permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
('View Reports', 'view_reports', 'Access to view all reports', NOW(), NOW()),
('Manage Settings', 'manage_settings', 'Access to system settings', NOW(), NOW()),
('Manage Roles', 'manage_roles', 'Create and edit roles', NOW(), NOW()),
('Manage Users', 'manage_users', 'Create and edit users', NOW(), NOW());

-- Permission Role (Super Admin id=1 gets all permissions 1-4)
INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 1), (2, 1), (3, 1), (4, 1);

-- Currencies
INSERT INTO `currencies` (`code`, `name`, `symbol`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
('MYR', 'Malaysian Ringgit', 'RM', 1, 1, NOW(), NOW()),
('THB', 'Thai Baht', '฿', 1, 1, NOW(), NOW()),
('USD', 'US Dollar', '$', 1, 1, NOW(), NOW()),
('SGD', 'Singapore Dollar', 'S$', 1, 1, NOW(), NOW());

-- System Settings
-- Payment methods JSON: ["Cash","Bank Transfer","USDT","Online Banking"]
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
('payment_methods', '[\"Cash\",\"Bank Transfer\",\"USDT\"]', 'payment_method', NOW(), NOW()),
('app_name', 'Money Changer', 'general', NOW(), NOW()),
('default_currency', 'MYR', 'general', NOW(), NOW());