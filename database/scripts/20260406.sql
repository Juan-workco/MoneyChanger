-- Exchange rate effective date modify to datetime
ALTER TABLE exchange_rates MODIFY effective_date DATETIME NOT NULL;

-- Telegram module updates
ALTER TABLE telegram_settings DROP COLUMN default_group_id;
ALTER TABLE users ADD COLUMN telegram_username VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN telegram_chat_id VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN telegram_active TINYINT(1) DEFAULT 0;
