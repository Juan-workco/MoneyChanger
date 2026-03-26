-- 20260313.sql

-- Insert new permission for managing customer uplines
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`) 
VALUES ('Manage Customer Uplines', 'manage_customer_uplines', 'Access to set Upline 1, Upline 2 and specify Commission values', NOW(), NOW());

-- Add alert_threshold column to receiving_accounts for balance alerts
ALTER TABLE `receiving_accounts` ADD COLUMN `alert_threshold` DECIMAL(18,4) DEFAULT NULL AFTER `is_active`;
