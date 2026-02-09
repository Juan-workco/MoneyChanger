-- ============================================
-- Staging Database Setup
-- Generated from Laravel migrations and seeders
-- ============================================

-- Drop existing tables if they exist (in reverse order of dependencies)
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `cash_flows`;
DROP TABLE IF EXISTS `transaction_commissions`;
DROP TABLE IF EXISTS `commission_settings`;
DROP TABLE IF EXISTS `customer_uplines`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `receiving_accounts`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `exchange_rates`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `currencies`;
DROP TABLE IF EXISTS `permission_role`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `password_resets`;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- 1. Create users table
-- ============================================
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `users_email_unique` (`email`),
    UNIQUE INDEX `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. Add RBAC and additional fields to users table
-- ============================================
ALTER TABLE `users`
    ADD COLUMN `role` ENUM('admin', 'agent') NOT NULL DEFAULT 'admin' AFTER `password`,
    ADD COLUMN `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER `role`,
    ADD COLUMN `last_login_at` TIMESTAMP NULL DEFAULT NULL AFTER `remember_token`,
    ADD COLUMN `super_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
    ADD COLUMN `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `super_admin`,
    ADD COLUMN `role_id` INT UNSIGNED NULL AFTER `commission_rate`;

-- ============================================
-- 3. Create roles table (RBAC)
-- ============================================
CREATE TABLE `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. Create permissions table (RBAC)
-- ============================================
CREATE TABLE `permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. Create permission_role pivot table
-- ============================================
CREATE TABLE `permission_role` (
    `permission_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `role_id`),
    INDEX `permission_role_role_id_foreign` (`role_id`),
    INDEX `permission_role_permission_id_foreign` (`permission_id`),
    CONSTRAINT `permission_role_permission_id_foreign`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `permission_role_role_id_foreign`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. Add foreign key constraint for users.role_id
-- ============================================
ALTER TABLE `users`
    ADD CONSTRAINT `users_role_id_foreign`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
        ON DELETE CASCADE;

-- ============================================
-- 7. Create password_resets table
-- ============================================
CREATE TABLE `password_resets` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. Create currencies table
-- ============================================
CREATE TABLE `currencies` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(10) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `symbol` VARCHAR(10) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `currencies_code_unique` (`code`),
    INDEX `currencies_created_by_foreign` (`created_by`),
    CONSTRAINT `currencies_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. Create customers table
-- ============================================
CREATE TABLE `customers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `email` VARCHAR(100) NULL,
    `phone` VARCHAR(50) NOT NULL,
    `address` TEXT NULL,
    `country` VARCHAR(100) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `total_transactions` INT NOT NULL DEFAULT 0,
    `total_volume` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `agent_id` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `customers_agent_id_foreign` (`agent_id`),
    CONSTRAINT `customers_agent_id_foreign`
        FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. Add customer enhancements
-- ============================================
ALTER TABLE `customers`
    ADD COLUMN `group_name` VARCHAR(100) NULL AFTER `name`,
    ADD COLUMN `contact_info` TEXT NULL AFTER `email`;

-- ============================================
-- 11. Create customer_uplines pivot table
-- ============================================
CREATE TABLE `customer_uplines` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` VARCHAR(255) NOT NULL DEFAULT 'primary',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `customer_uplines_customer_id_user_id_unique` (`customer_id`, `user_id`),
    INDEX `customer_uplines_customer_id_foreign` (`customer_id`),
    INDEX `customer_uplines_user_id_foreign` (`user_id`),
    CONSTRAINT `customer_uplines_customer_id_foreign`
        FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `customer_uplines_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. Create exchange_rates table
-- ============================================
CREATE TABLE `exchange_rates` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `currency_from_id` INT UNSIGNED NOT NULL,
    `currency_to_id` INT UNSIGNED NOT NULL,
    `buy_rate` DECIMAL(10,6) NOT NULL,
    `sell_rate` DECIMAL(10,6) NOT NULL,
    `profit_margin` DECIMAL(10,6) GENERATED ALWAYS AS (sell_rate - buy_rate) STORED,
    `effective_date` DATE NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `exchange_rates_currency_from_id_foreign` (`currency_from_id`),
    INDEX `exchange_rates_currency_to_id_foreign` (`currency_to_id`),
    INDEX `exchange_rates_created_by_foreign` (`created_by`),
    CONSTRAINT `exchange_rates_currency_from_id_foreign`
        FOREIGN KEY (`currency_from_id`) REFERENCES `currencies` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `exchange_rates_currency_to_id_foreign`
        FOREIGN KEY (`currency_to_id`) REFERENCES `currencies` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `exchange_rates_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. Create receiving_accounts table
-- ============================================
CREATE TABLE `receiving_accounts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `account_type` ENUM('bank', 'usdt', 'other') NOT NULL,
    `account_name` VARCHAR(200) NOT NULL,
    `account_number` VARCHAR(200) NOT NULL,
    `bank_name` VARCHAR(200) NULL,
    `currency` VARCHAR(10) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. Create system_settings table
-- ============================================
CREATE TABLE `system_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NOT NULL,
    `setting_type` ENUM('general', 'currency', 'payment_method') NOT NULL DEFAULT 'general',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `system_settings_setting_key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 15. Create transactions table
-- ============================================
CREATE TABLE `transactions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_code` VARCHAR(50) NOT NULL UNIQUE,
    `customer_id` INT UNSIGNED NOT NULL,
    `currency_from_id` INT UNSIGNED NOT NULL,
    `currency_to_id` INT UNSIGNED NOT NULL,
    `exchange_rate_id` INT UNSIGNED NOT NULL,
    `amount_from` DECIMAL(15,2) NOT NULL,
    `amount_to` DECIMAL(15,2) NOT NULL,
    `buy_rate` DECIMAL(10,6) NOT NULL,
    `sell_rate` DECIMAL(10,6) NOT NULL,
    `payment_method` VARCHAR(100) NOT NULL,
    `status` ENUM('pending', 'accept', 'sent', 'cancel') NOT NULL DEFAULT 'pending',
    `transaction_date` DATETIME NOT NULL,
    `agent_commission` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `profit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `transactions_transaction_code_unique` (`transaction_code`),
    INDEX `transactions_customer_id_foreign` (`customer_id`),
    INDEX `transactions_currency_from_id_foreign` (`currency_from_id`),
    INDEX `transactions_currency_to_id_foreign` (`currency_to_id`),
    INDEX `transactions_exchange_rate_id_foreign` (`exchange_rate_id`),
    INDEX `transactions_created_by_foreign` (`created_by`),
    CONSTRAINT `transactions_customer_id_foreign`
        FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `transactions_currency_from_id_foreign`
        FOREIGN KEY (`currency_from_id`) REFERENCES `currencies` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `transactions_currency_to_id_foreign`
        FOREIGN KEY (`currency_to_id`) REFERENCES `currencies` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `transactions_exchange_rate_id_foreign`
        FOREIGN KEY (`exchange_rate_id`) REFERENCES `exchange_rates` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `transactions_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 16. Add Phase 3 transaction enhancements
-- ============================================
ALTER TABLE `transactions`
    ADD COLUMN `service_fee` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `amount_to`,
    ADD COLUMN `is_backdated` TINYINT(1) NOT NULL DEFAULT 0 AFTER `transaction_date`,
    ADD COLUMN `base_currency_rate` DECIMAL(10,6) NULL AFTER `exchange_rate_id`,
    ADD COLUMN `fx_profit_loss` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `profit_amount`;

-- ============================================
-- 17. Create commission_settings table
-- ============================================
CREATE TABLE `commission_settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `currency_pair` VARCHAR(20) NOT NULL,
    `points` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `commission_settings_user_id_currency_pair_unique` (`user_id`, `currency_pair`),
    INDEX `commission_settings_user_id_foreign` (`user_id`),
    CONSTRAINT `commission_settings_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 18. Create transaction_commissions table
-- ============================================
CREATE TABLE `transaction_commissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `points_used` DECIMAL(10,6) NULL,
    `calculation_details` TEXT NULL,
    `is_manual_override` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `transaction_commissions_transaction_id_foreign` (`transaction_id`),
    INDEX `transaction_commissions_user_id_foreign` (`user_id`),
    CONSTRAINT `transaction_commissions_transaction_id_foreign`
        FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `transaction_commissions_user_id_foreign`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 19. Create cash_flows table
-- ============================================
CREATE TABLE `cash_flows` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transaction_code` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('AP', 'AR', 'C2C') NOT NULL,
    `from_entity_type` VARCHAR(255) NULL,
    `from_entity_id` INT UNSIGNED NULL,
    `to_entity_type` VARCHAR(255) NULL,
    `to_entity_id` INT UNSIGNED NULL,
    `currency_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `transaction_date` DATETIME NOT NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `cash_flows_transaction_code_unique` (`transaction_code`),
    INDEX `cash_flows_currency_id_foreign` (`currency_id`),
    INDEX `cash_flows_created_by_foreign` (`created_by`),
    INDEX `cash_flows_from_entity_type_from_entity_id_index` (`from_entity_type`, `from_entity_id`),
    INDEX `cash_flows_to_entity_type_to_entity_id_index` (`to_entity_type`, `to_entity_id`),
    CONSTRAINT `cash_flows_currency_id_foreign`
        FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `cash_flows_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 20. Create activity_logs table
-- ============================================
CREATE TABLE `activity_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `model_type` VARCHAR(255) NULL,
    `model_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SEED DATA
-- ============================================

-- Insert default roles
INSERT INTO `roles` (`name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
('Super Admin', 'super-admin', 'Full access to everything', NOW(), NOW()),
('Admin', 'admin', 'Administrative access', NOW(), NOW()),
('Agent', 'agent', 'Standard agent access', NOW(), NOW());

-- Insert default permissions
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
('View Reports', 'view_reports', 'Access to view all reports', NOW(), NOW()),
('Manage Settings', 'manage_settings', 'Access to system settings', NOW(), NOW()),
('Manage Roles', 'manage_roles', 'Create and edit roles', NOW(), NOW()),
('Manage Users', 'manage_users', 'Create and edit users', NOW(), NOW()),
('View Currencies', 'view_currencies', 'View currency list', NOW(), NOW()),
('Manage Currencies', 'manage_currencies', 'Create and edit currencies', NOW(), NOW()),
('View Exchange Rates', 'view_exchange_rates', 'View exchange rates', NOW(), NOW()),
('Manage Exchange Rates', 'manage_exchange_rates', 'Create and edit exchange rates', NOW(), NOW()),
('View Customers', 'view_customers', 'View customer list', NOW(), NOW()),
('Manage Customers', 'manage_customers', 'Create and edit customers', NOW(), NOW()),
('View Transactions', 'view_transactions', 'View transaction list', NOW(), NOW()),
('Manage Transactions', 'manage_transactions', 'Create and edit transactions', NOW(), NOW());

-- Assign all permissions to Super Admin
INSERT INTO `permission_role` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p, `roles` r
WHERE r.slug = 'super-admin';

-- Create admin user (password: admin123)
INSERT INTO `users` (`username`, `name`, `email`, `password`, `role`, `status`, `super_admin`, `commission_rate`, `role_id`, `created_at`, `updated_at`) VALUES
('admin', 'Admin', 'admin@moneychanger.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1, 0.00, (SELECT id FROM roles WHERE slug = 'super-admin'), NOW(), NOW());

-- Create default currencies
INSERT INTO `currencies` (`code`, `name`, `symbol`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
('MYR', 'Malaysian Ringgit', 'RM', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW()),
('USD', 'US Dollar', '$', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW()),
('SGD', 'Singapore Dollar', 'S$', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW()),
('CNY', 'Chinese Yuan', '¥', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW()),
('EUR', 'Euro', '€', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW()),
('GBP', 'British Pound', '£', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW()),
('THB', 'Thai Baht', '฿', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW()),
('USDT', 'Tether', '₮', 1, (SELECT id FROM users LIMIT 1), NOW(), NOW());

-- Create default system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
('app_name', 'Money Changer Admin', 'general', NOW(), NOW()),
('default_currency', 'MYR', 'currency', NOW(), NOW()),
('payment_methods', '["Cash","Bank Transfer","USDT","Online Banking"]', 'payment_method', NOW(), NOW());

-- ============================================
-- DATABASE SETUP COMPLETE
-- ============================================
