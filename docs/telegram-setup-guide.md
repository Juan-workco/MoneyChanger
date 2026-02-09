# Telegram Bot Setup Guide

## Step 1: Create Your Telegram Bot

1. Open Telegram and search for **@BotFather**
2. Send `/newbot` command
3. Follow the prompts:
   - Choose a name for your bot (e.g., "Money Changer Bot")
   - Choose a username (must end in 'bot', e.g., "moneychanger_admin_bot")
4. Save the **bot token** you receive (looks like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)

## Step 2: Configure Bot Commands

Send this message to @BotFather to set up commands:

```
/setcommands
```

Then select your bot and paste:

```
start - Welcome message and setup
help - Show all available commands
createorder - Create a new transaction
commission - View your commission summary
transaction - View transaction details
balance - View balance sheet
settings - Configure notification preferences
```

## Step 3: Get Chat IDs

### For Private Chat:
1. Start a conversation with your bot
2. Send any message
3. Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
4. Find the `"chat":{"id":` value in the response

### For Group Chat:
1. Add your bot to a group
2. Send a message in the group
3. Visit the same URL as above
4. Find the `"chat":{"id":` value (will be a negative number for groups)

## Step 4: Configure Your Application

Add these lines to your `.env` file:

```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/telegram/webhook
TELEGRAM_ADMIN_CHAT_ID=your_admin_chat_id
```

## Step 5: Set Up Webhook (Production Only)

After deployment, set the webhook by visiting:

```
https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://yourdomain.com/telegram/webhook
```

Or use the test connection feature in Settings → Telegram Settings.

## Testing Your Bot

1. Send `/start` to your bot
2. You should receive a welcome message
3. Try `/help` to see available commands
4. Create a test transaction through the web interface
5. Check if notification arrives in Telegram

## Troubleshooting

- **Bot not responding:** Check bot token in `.env`
- **No notifications:** Verify chat ID is correctly configured in Telegram Settings
- **Commands not working:** Ensure webhook is properly set up
- **Permission errors:** Make sure agent is properly linked to their Telegram account

---

For detailed implementation guide, see `telegram-implementation-plan.md`
