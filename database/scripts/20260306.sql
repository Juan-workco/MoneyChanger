-- database/scripts/20260306.sql
-- DB changes for PRD Gap Analysis Features

-- 1. Double-Entry Ledger & Day Close Tables

CREATE TABLE `day_closes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `close_date` DATE NOT NULL,
    `closed_by` INT UNSIGNED NOT NULL,
    `status` ENUM('closed', 'reopened') DEFAULT 'closed',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `day_closes_date_unique` (`close_date`),
    FOREIGN KEY (`closed_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ledger_entries` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `transaction_date` DATE NOT NULL,
    `reference_type` VARCHAR(100) NOT NULL, -- e.g., 'sales_order', 'ap', 'ar', 'ctc', 'contra', 'adjustment'
    `reference_id` BIGINT UNSIGNED NOT NULL,
    `account_type` ENUM('customer', 'account') NOT NULL, -- 'account' means receiving_accounts (wallet/bank)
    `account_id` INT UNSIGNED NOT NULL,
    `currency_id` INT UNSIGNED NOT NULL,
    `debit` DECIMAL(18,4) DEFAULT 0.0000,
    `credit` DECIMAL(18,4) DEFAULT 0.0000,
    `running_balance` DECIMAL(18,4) NOT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
    INDEX `ledger_reference_idx` (`reference_type`, `reference_id`),
    INDEX `ledger_account_currency_idx` (`account_type`, `account_id`, `currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `daily_balances` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `balance_date` DATE NOT NULL,
    `account_type` ENUM('customer', 'account') NOT NULL,
    `account_id` INT UNSIGNED NOT NULL,
    `currency_id` INT UNSIGNED NOT NULL,
    `closing_balance` DECIMAL(18,4) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`),
    UNIQUE KEY `daily_balances_date_account_unique` (`balance_date`, `account_type`, `account_id`, `currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Contra / Netting

CREATE TABLE `contras` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contra_code` VARCHAR(50) NOT NULL UNIQUE,
    `customer_id` INT UNSIGNED NOT NULL,
    `currency_a_id` INT UNSIGNED NOT NULL,
    `amount_a` DECIMAL(18,4) NOT NULL,
    `currency_b_id` INT UNSIGNED NOT NULL,
    `amount_b` DECIMAL(18,4) NOT NULL,
    `exchange_rate` DECIMAL(10,6) NOT NULL,
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
    FOREIGN KEY (`currency_a_id`) REFERENCES `currencies`(`id`),
    FOREIGN KEY (`currency_b_id`) REFERENCES `currencies`(`id`),
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Customer Credit Limits

CREATE TABLE `customer_credit_limits` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `currency_id` INT UNSIGNED NOT NULL,
    `credit_limit` DECIMAL(18,4) NOT NULL,
    `enforcement` ENUM('block', 'warn') DEFAULT 'warn',
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    UNIQUE KEY `customer_currency_limit_unique` (`customer_id`, `currency_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`currency_id`) REFERENCES `currencies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Rate Margins / Auto-Markup

CREATE TABLE `rate_margins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `currency_pair_id` INT UNSIGNED NOT NULL UNIQUE,
    `buy_markup` DECIMAL(10,4) DEFAULT 0.0000,
    `sell_markup` DECIMAL(10,4) DEFAULT 0.0000,
    `auto_apply` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`currency_pair_id`) REFERENCES `currency_pairs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Notifications

CREATE TABLE `notifications` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `type` VARCHAR(255) NOT NULL,
    `notifiable_type` VARCHAR(255) NOT NULL,
    `notifiable_id` BIGINT UNSIGNED NOT NULL,
    `data` TEXT NOT NULL,
    `read_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    INDEX `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Telegram Settings

CREATE TABLE `telegram_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bot_token` VARCHAR(255) NULL,
    `webhook_url` VARCHAR(255) NULL,
    `default_group_id` VARCHAR(100) NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Import Logs

CREATE TABLE `import_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `import_type` VARCHAR(100) NOT NULL, -- e.g., 'customers', 'exchange_rates'
    `file_name` VARCHAR(255) NOT NULL,
    `total_rows` INT UNSIGNED DEFAULT 0,
    `successful_rows` INT UNSIGNED DEFAULT 0,
    `failed_rows` INT UNSIGNED DEFAULT 0,
    `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    `error_details` LONGTEXT NULL, -- JSON format of errors per row
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
