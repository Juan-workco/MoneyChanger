# Telegram Integration Implementation Plan

**Feature:** Telegram Notification Integration and Bot Command  
**Objective:** Enable automatic transaction notifications to Telegram and provide bot commands for agents to interact with the system.  
**Estimated Time:** 11-12 hours

---

## Overview

This implementation will add Telegram integration to the Money Changer Admin system, enabling:
- Automatic notifications when transactions are created/updated
- Multi-agent support with separate Telegram groups
- Bot commands for creating orders and viewing data
- Customizable message templates

---

## Prerequisites

### 1. Telegram Bot Setup
- Create bot via [@BotFather](https://t.me/botfather)
- Obtain bot token
- Get chat/group IDs for each agent
- Set bot commands via BotFather

### 2. Required Package
```bash
composer require telegram-bot-sdk/telegram-bot-sdk
```

---

## Proposed Changes

### Core Components

#### [NEW] `config/telegram.php`

Configuration file for Telegram bot settings:
- Bot token
- Webhook URL
- Default notification settings
- Command routing

#### [NEW] `app/Services/TelegramService.php`

Main service for Telegram integration:
- `sendTransactionNotification($transaction)` - Send transaction alerts
- `sendCommissionReport($agent, $period)` - Send commission summary
- `sendBalanceSheet($agent, $date)` - Send balance information
- `formatMessage($template, $data)` - Format messages with custom templates
- `getAgentChatId($agentId)` - Get Telegram chat ID for agent

#### [NEW] `app/Http/Controllers/TelegramBotController.php`

Handle incoming webhook requests from Telegram:
- `handleWebhook()` - Process incoming updates
- `handleCommand($command, $message)` - Route commands to handlers
- Command handlers:
  - `commandCreateOrder($message)` - Create transaction via Telegram
  - `commandViewCommission($message)` - Show agent commission
  - `commandViewTransaction($message)` - Show transaction details
  - `commandViewBalance($message)` - Show balance sheet

---

### Database Changes

#### [NEW] Migration: `create_telegram_settings_table.php`

Store Telegram configuration per agent:
```php
Schema::create('telegram_settings', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id'); // Agent ID
    $table->string('chat_id')->nullable(); // Telegram chat/group ID
    $table->boolean('notifications_enabled')->default(true);
    $table->boolean('notify_on_create')->default(true);
    $table->boolean('notify_on_update')->default(true);
    $table->boolean('notify_on_status_change')->default(true);
    $table->text('message_template')->nullable();
    $table->timestamps();
    
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```

#### [NEW] Migration: `add_telegram_user_id_to_users_table.php`

Link Telegram user to system user:
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('telegram_user_id')->nullable()->after('commission_rate');
    $table->string('telegram_username')->nullable()->after('telegram_user_id');
});
```

---

### Modified Files

#### [MODIFY] `app/Http/Controllers/TransactionController.php`

Add Telegram notifications after transaction operations:
- In `store()` method: Send notification after creating transaction
- In `update()` method: Send notification after updating transaction
- In `updateStatus()` method: Send notification on status change

#### [MODIFY] `routes/web.php`

Add Telegram webhook route:
```php
Route::post('/telegram/webhook', 'TelegramBotController@handleWebhook');
```

#### [MODIFY] `app/Http/Controllers/SettingsController.php`

Add Telegram settings management:
- `telegram()` - Show Telegram settings page
- `updateTelegramSettings()` - Save Telegram configuration
- `testTelegramConnection()` - Test bot connection

---

### Views

#### [NEW] `resources/views/settings/telegram.blade.php`

Telegram settings page with:
- Bot token configuration (admin only)
- Agent chat ID mapping
- Notification preferences (per agent)
- Message template editor
- Test notification button
- Webhook setup instructions

---

## Bot Commands

### Command Structure

| Command | Description | Access Level |
|---------|-------------|--------------|
| `/start` | Welcome message with command list | All |
| `/help` | Show available commands | All |
| `/createorder` | Create new transaction | Agent |
| `/commission` | View commission summary | Agent |
| `/transaction [id]` | View transaction details | Agent |
| `/balance` | View balance sheet | Agent |
| `/settings` | Configure notifications | Agent |

### Implementation Details

#### `/createorder` Flow
1. Bot asks for customer name (or shows customer list)
2. Bot asks for currency pair (MYR → USD)
3. Bot asks for amount
4. Bot asks for payment method
5. Bot creates transaction and shows summary
6. Bot sends confirmation message

#### `/commission` Flow
1. Bot shows current month commission
2. Shows transaction count and total
3. Option to view different month

#### `/transaction [id]` Flow
1. If ID provided: Show transaction details
2. If no ID: Show recent transactions (last 10)
3. Option to search by customer name

#### `/balance` Flow
1. Bot asks for date (default: today)
2. Shows balance sheet for all currencies
3. Option to view specific currency

---

## Notification Templates

### Transaction Created
```
🆕 New Transaction Created

Customer: {customer_name}
From: {amount_from} {currency_from}
To: {amount_to} {currency_to}
Rate: {exchange_rate}
Status: {status}

Transaction ID: #{transaction_id}
```

### Transaction Status Updated
```
✅ Transaction Status Updated

Transaction ID: #{transaction_id}
Customer: {customer_name}
Old Status: {old_status}
New Status: {new_status}

Amount: {amount_from} {currency_from} → {amount_to} {currency_to}
```

### Daily Summary (Scheduled)
```
📊 Daily Summary - {date}

Total Transactions: {count}
Total Volume: {volume}
Total Profit: {profit}
Your Commission: {commission}

Status:
✅ Sent: {sent_count}
⏳ Pending: {pending_count}
📥 Received: {received_count}
```

---

## Environment Configuration

Add to `.env`:
```bash
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/telegram/webhook
TELEGRAM_ADMIN_CHAT_ID=your_admin_chat_id
```

---

## Security Considerations

**CRITICAL SECURITY MEASURES:**

1. **Webhook Validation:** Verify requests come from Telegram servers
2. **User Authentication:** Link Telegram users to system users before allowing commands
3. **Rate Limiting:** Prevent spam and abuse
4. **Sensitive Data:** Don't send full transaction details in notifications
5. **Chat ID Verification:** Verify agent can only access their own data

---

## Verification Plan

### Automated Tests
1. Unit tests for TelegramService methods
2. Test message formatting with various data
3. Test command routing

### Manual Verification
1. **Notification Testing:**
   - Create transaction → Verify notification received
   - Update transaction → Verify update notification
   - Change status → Verify status notification
   
2. **Bot Command Testing:**
   - Test `/createorder` complete flow
   - Test `/commission` with different date ranges
   - Test `/transaction` with and without ID
   - Test `/balance` for different dates
   
3. **Multi-Agent Testing:**
   - Configure 2+ agents with different chat IDs
   - Verify each agent only sees their data
   - Verify notifications go to correct groups

4. **Error Handling:**
   - Test with invalid bot token
   - Test with unreachable Telegram API
   - Test with invalid commands
   - Test with unauthorized users

---

## Implementation Timeline

| Phase | Task | Estimated Time |
|-------|------|----------------|
| 1 | Package installation & configuration | 30 min |
| 2 | Database migrations & models | 1 hour |
| 3 | TelegramService implementation | 2 hours |
| 4 | TelegramBotController & commands | 3 hours |
| 5 | Transaction notification integration | 1 hour |
| 6 | Settings UI & configuration | 2 hours |
| 7 | Testing & debugging | 2 hours |
| **Total** | | **~11-12 hours** |

---

## Future Enhancements

- Inline keyboard buttons for quick actions
- Photo/document upload support for receipts
- Multi-language support
- Scheduled reports (daily/weekly/monthly)
- Transaction approval workflow via Telegram
- Real-time exchange rate updates
