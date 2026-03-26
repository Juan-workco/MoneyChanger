-- 20260326.sql

-- Add status, verified_by, verified_at columns to cash_flows table
ALTER TABLE `cash_flows`
  ADD COLUMN `verified_by` INT(10) UNSIGNED NULL AFTER `status`,
  ADD COLUMN `verified_at` TIMESTAMP NULL AFTER `verified_by`;

-- Add permission for verifying cash flows
INSERT INTO `permissions` (`name`, `slug`, `description`, `created_at`, `updated_at`)
VALUES ('Verify Cash Flows', 'verify_cash_flows', 'Approve or reject pending cash flow entries', NOW(), NOW());
