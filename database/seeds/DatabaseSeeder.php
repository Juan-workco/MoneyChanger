<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Currency;
use App\SystemSetting;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Manually require the seeder file since composer dump-autoload failed
        require_once __DIR__ . '/RbacSeeder.php';

        $this->call(RbacSeeder::class);
        // Create admin user
        $admin = User::create([
            'username' => 'adm1',
            'name' => 'Admin',
            'email' => 'admin@moneychanger.com',
            'password' => bcrypt('aaaa9999'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        echo "Admin user created: admin@moneychanger.com / admin123\n";

        // Create common currencies
        $currencies = [
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$'],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥'],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
            ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿'],
        ];

        foreach ($currencies as $currency) {
            Currency::create([
                'code' => $currency['code'],
                'name' => $currency['name'],
                'symbol' => $currency['symbol'],
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
        }

        echo "Created " . count($currencies) . " currencies\n";

        // Create default payment methods
        $paymentMethods = ['Cash', 'Bank Transfer', 'USDT', 'Online Banking'];
        SystemSetting::setPaymentMethods($paymentMethods);

        echo "Payment methods configured\n";

        // Create default settings
        SystemSetting::set('app_name', 'Money Changer Admin');
        SystemSetting::set('default_currency', 'MYR');

        echo "System settings initialized\n";
    }
}
