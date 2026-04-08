<?php

namespace App\Http\Controllers;

use App\TelegramSetting;
use App\Customer;
use App\CustomerBalance;
use App\ExchangeRate;
use App\Transaction;
use App\TransactionCommission;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Show Telegram Settings page
     */
    public function settings()
    {
        if (!auth()->user()->hasPermission('manage_settings')) {
            return redirect()->route('dashboard')->with('error', 'Permission denied.');
        }

        $setting = TelegramSetting::first() ?: new TelegramSetting();
        return view('settings.telegram', compact('setting'));
    }

    /**
     * Update Telegram Settings
     */
    public function updateSettings(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_settings')) {
            return redirect()->route('dashboard')->with('error', 'Permission denied.');
        }

        $validated = $request->validate([
            'bot_token' => 'required|string',
        ]);

        $setting = TelegramSetting::first() ?: new TelegramSetting();
        $setting->fill($validated);
        $setting->is_active = $request->has('is_active');
        $setting->webhook_url = route('telegram.webhook', ['token' => $setting->bot_token]);
        $setting->save();

        // Auto-register webhook with Telegram API
        $response = self::callTelegram("https://api.telegram.org/bot{$setting->bot_token}/setWebhook", [
            'url' => $setting->webhook_url
        ]);

        if ($response['ok']) {
            return back()->with('success', 'Telegram settings updated and webhook registered successfully.');
        } else {
            return back()->with('warning', 'Settings saved but failed to register webhook automatically: ' . ($response['description'] ?? 'Unknown error'));
        }
    }

    /**
     * Handle incoming webhooks from Telegram
     */
    public function webhook(Request $request, $token)
    {
        // Always return 200 OK to Telegram — if we don't, Telegram will retry
        // the same update endlessly, blocking all subsequent commands.
        try {
            $setting = TelegramSetting::where('bot_token', $token)->where('is_active', true)->first();
            if (!$setting) {
                return response('Unauthorized or Inactive', 403);
            }

            $update = $request->all();
            Log::info('Telegram Webhook Received:', $update);

            // Auto-map chat_id to system user based on telegram_username
            try {
                if (isset($update['message']['from']['username'])) {
                    $tgUsername = ltrim($update['message']['from']['username'], '@');
                    $chatId = $update['message']['chat']['id'];
                    $mappedUser = User::whereNotNull('telegram_username')
                        ->where('telegram_active', true)
                        ->get()
                        ->first(function ($u) use ($tgUsername) {
                            return ltrim($u->telegram_username, '@') === $tgUsername;
                        });

                    if ($mappedUser && $mappedUser->telegram_chat_id !== (string) $chatId) {
                        $mappedUser->telegram_chat_id = (string) $chatId;
                        $mappedUser->save();
                        Log::info("Telegram: Auto-mapped chat_id {$chatId} to user {$mappedUser->username}");
                    }
                }
            } catch (\Exception $e) {
                Log::error('Telegram Webhook: Failed to auto-map chat_id. Error: ' . $e->getMessage(), [
                    'update' => $update,
                ]);
            }

            if (isset($update['message']['text'])) {
                $text = trim($update['message']['text']);
                $chatId = $update['message']['chat']['id'];

                try {
                    if (\strpos($text, '/start') === 0 || \strpos($text, '/help') === 0) {
                        $this->handleStart($setting, $chatId);
                    } elseif (\strpos($text, '/createorder') === 0) {
                        $this->handleCreateOrder($setting, $chatId);
                    } elseif (\strpos($text, '/commission') === 0) {
                        $this->handleCommission($setting, $chatId, $update);
                    } elseif (\strpos($text, '/transaction') === 0) {
                        $this->handleTransaction($setting, $chatId, $text);
                    } elseif (\strpos($text, '/balance') === 0) {
                        $this->handleBalance($setting, $chatId, $text);
                    } elseif (\strpos($text, '/rate') === 0) {
                        $this->handleRate($setting, $chatId);
                    }
                } catch (\Exception $e) {
                    Log::error('Telegram Webhook: Command handler failed. Command: "' . $text . '". Error: ' . $e->getMessage(), [
                        'chat_id' => $chatId,
                        'trace'   => $e->getTraceAsString(),
                    ]);
                    // Notify the user so they know something went wrong, not just silence
                    try {
                        $this->sendMessage($setting->bot_token, $chatId, '⚠️ Sorry, something went wrong while processing your command. Please try again later.');
                    } catch (\Exception $sendEx) {
                        Log::error('Telegram Webhook: Failed to send error reply. Error: ' . $sendEx->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            // Last-resort catch — log and fall through to return 200 below
            Log::error('Telegram Webhook: Unhandled exception. Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Always return 200 so Telegram does not retry this update
        return response('OK', 200);
    }

    // ─── Bot Command Handlers ──────────────────────────────────

    /**
     * /balance [CustomerName] — Show customer multi-currency balances
     */
    private function handleBalance($setting, $chatId, $text)
    {
        $parts = explode(' ', $text, 2);
        if (count($parts) < 2 || empty(trim($parts[1]))) {
            $this->sendMessage($setting->bot_token, $chatId, "Usage: /balance [Customer Name]");
            return;
        }

        $search = strip_tags(trim($parts[1]));
        $customer = Customer::where('name', 'LIKE', "%{$search}%")
            ->first();

        if (!$customer) {
            $this->sendMessage($setting->bot_token, $chatId, "❌ Customer \"{$search}\" not found.");
            return;
        }

        $balances = CustomerBalance::where('customer_id', $customer->id)
            ->with('currency')
            ->get();

        if ($balances->isEmpty()) {
            $this->sendMessage($setting->bot_token, $chatId, "📊 {$customer->name} has no recorded balances.");
            return;
        }

        $msg = "📊 *Balances for {$customer->name}*\n";
        foreach ($balances as $bal) {
            /** @var CustomerBalance $bal */
            $code = $bal->currency ? $bal->currency->code : 'N/A';
            $amount = number_format($bal->balance, 4);
            $direction = $bal->balance >= 0 ? '(Owes Us)' : '(We Owe)';
            $msg .= "• {$code}: {$amount} {$direction}\n";
        }

        $this->sendMessage($setting->bot_token, $chatId, $msg, 'Markdown');
    }

    /**
     * /rate — Show current active exchange rates
     */
    private function handleRate($setting, $chatId)
    {
        $rates = ExchangeRate::with(['currencyFrom', 'currencyTo'])
            ->where('is_active', true)
            ->orderBy('effective_date', 'desc')
            ->get();

        if ($rates->isEmpty()) {
            $this->sendMessage($setting->bot_token, $chatId, "No active exchange rates found.");
            return;
        }

        $msg = "💱 *Current Exchange Rates*\n";
        foreach ($rates as $rate) {
            $from = $rate->currencyFrom->code ?? '?';
            $to = $rate->currencyTo->code ?? '?';
            $buy = number_format($rate->buy_rate, 4);
            $sell = number_format($rate->sell_rate, 4);
            $msg .= "• {$from}/{$to} — Buy: {$buy} | Sell: {$sell}\n";
        }

        $this->sendMessage($setting->bot_token, $chatId, $msg, 'Markdown');
    }

    /**
     * /commission — Show current month commission summary for the caller
     */
    private function handleCommission($setting, $chatId, $update)
    {
        // Find the user by their telegram_chat_id
        $user = User::where('telegram_chat_id', (string) $chatId)->first();

        if (!$user) {
            $this->sendMessage($setting->bot_token, $chatId, "❌ Your Telegram account is not linked to any system user. Please set your Telegram Chat ID in your profile.");
            return;
        }

        $month = date('Y-m');
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        $total = TransactionCommission::whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('status', 'sent');
        })->where('user_id', $user->id)->sum('amount');

        $msg = "💰 *Commission Summary ({$month})*\n";
        $msg .= "Agent: {$user->name}\n";
        $msg .= "Total: " . number_format($total, 2) . " USDT\n";

        $this->sendMessage($setting->bot_token, $chatId, $msg, 'Markdown');
    }

    /**
     * /start or /help — Welcome message and show all commands
     */
    private function handleStart($setting, $chatId)
    {
        $msg = "👋 *Welcome to Money Changer Bot!*\n";
        $msg .= "I can help you manage your transactions, check balances, and view commissions.\n\n";
        $msg .= "🛠️ *Available Commands*\n";
        $msg .= "• /start - Welcome message and show commands\n";
        $msg .= "• /createorder - Create a new transaction\n";
        $msg .= "• /commission - View your commission summary\n";
        $msg .= "• /transaction [Order ID] - View transaction details\n";
        $msg .= "• /balance [Customer Name] - View balance sheet\n";

        $this->sendMessage($setting->bot_token, $chatId, $msg, 'Markdown');
    }

    /**
     * /transaction [OrderID] — View transaction details
     */
    private function handleTransaction($setting, $chatId, $text)
    {
        $parts = explode(' ', $text, 2);
        if (count($parts) < 2 || empty(trim($parts[1]))) {
            $this->sendMessage($setting->bot_token, $chatId, "Usage: /transaction [Order ID]");
            return;
        }

        $orderId = strip_tags(trim($parts[1]));
        $tx = Transaction::with(['customer', 'currencyFrom', 'currencyTo'])
            ->where('order_id', $orderId)
            ->first();

        if (!$tx) {
            $this->sendMessage($setting->bot_token, $chatId, "❌ Order \"{$orderId}\" not found.");
            return;
        }

        $msg = "📋 *Order {$tx->order_id}*\n";
        $msg .= "Customer: {$tx->customer->name}\n";
        $msg .= "From: {$tx->amount_from} {$tx->currencyFrom->code}\n";
        $msg .= "To: {$tx->amount_to} {$tx->currencyTo->code}\n";
        $msg .= "Rate: {$tx->sell_rate}\n";
        $msg .= "Status: *" . strtoupper($tx->status) . "*\n";
        $msg .= "Date: {$tx->transaction_date}";

        $this->sendMessage($setting->bot_token, $chatId, $msg, 'Markdown');
    }

    /**
     * /createorder — Create a new transaction
     */
    private function handleCreateOrder($setting, $chatId)
    {
        $url = url('/transactions/create');
        $msg = "📝 *Create New Order*\n";
        $msg .= "Click the link below to create a new Sales Order:\n";
        $msg .= $url;

        $this->sendMessage($setting->bot_token, $chatId, $msg, 'Markdown');
    }



    // ─── Auto-Notification Dispatcher ──────────────────────────

    /**
     * Send a notification to the default Telegram group when a Sales Order is created or status changes.
     * Call this statically from TransactionController.
     */
    public static function notifyOrderEvent(Transaction $transaction, $eventType = 'created')
    {
        $setting = TelegramSetting::where('is_active', true)->first();
        if (!$setting) {
            return; // No active Telegram config
        }

        // Find the agent who created this transaction and has Telegram active
        $agent = $transaction->creator ?? null;
        if (!$agent) {
            // Try to load the relationship if not already loaded
            $transaction->load('creator');
            $agent = $transaction->creator;
        }

        if (!$agent || !$agent->telegram_active || empty($agent->telegram_chat_id)) {
            return; // Agent not configured for Telegram notifications
        }

        $customer = $transaction->customer->name ?? 'N/A';
        $from = $transaction->currencyFrom->code ?? '?';
        $to = $transaction->currencyTo->code ?? '?';

        if ($eventType === 'created') {
            $msg = "🆕 *New Sales Order*\n";
        } else {
            $msg = "🔄 *Order Status Changed*\n";
        }

        $msg .= "Order: {$transaction->order_id}\n";
        $msg .= "Customer: {$customer}\n";
        $msg .= "From: {$transaction->amount_from} {$from}\n";
        $msg .= "To: {$transaction->amount_to} {$to}\n";
        $msg .= "Rate: {$transaction->sell_rate}\n";
        $msg .= "Status: *" . strtoupper($transaction->status) . "*\n";
        $msg .= "By: {$agent->name}";

        self::callTelegram("https://api.telegram.org/bot{$setting->bot_token}/sendMessage", [
            'chat_id' => $agent->telegram_chat_id,
            'text' => $msg,
            'parse_mode' => 'Markdown',
        ]);
    }

    // ─── Helpers ───────────────────────────────────────────────

    /**
     * Send a Telegram message
     */
    private function sendMessage($token, $chatId, $text, $parseMode = null)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        if ($parseMode) {
            $params['parse_mode'] = $parseMode;
        }

        self::callTelegram("https://api.telegram.org/bot{$token}/sendMessage", $params);
    }

    /**
     * Helper to make HTTP requests to Telegram API using cURL (No Guzzle dependency)
     */
    private static function callTelegram($url, $params = [])
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local/vagrant environments
            $result = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($result, true);
            if (!$response || !isset($response['ok'])) {
                Log::error("Telegram API Error (Invalid Response): " . $result);
                return ['ok' => false, 'description' => 'Invalid response from Telegram'];
            }
            return $response;
        } catch (\Exception $e) {
            Log::error("Telegram cURL Error: " . $e->getMessage());
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }
}
