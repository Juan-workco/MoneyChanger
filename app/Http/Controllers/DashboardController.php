<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Customer;
use App\Currency;
use App\ExchangeRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Show the dashboard
     */
    public function index()
    {
        // TEMPORARY: Run Migration and Seeder Logic
        try {
            if (!Schema::hasTable('roles')) {
                Schema::create('roles', function ($table) {
                    $table->increments('id');
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->text('description')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('permissions')) {
                Schema::create('permissions', function ($table) {
                    $table->increments('id');
                    $table->string('name');
                    $table->string('slug')->unique();
                    $table->text('description')->nullable();
                    $table->timestamps();
                });
            }

            if (!Schema::hasTable('permission_role')) {
                Schema::create('permission_role', function ($table) {
                    $table->unsignedInteger('permission_id');
                    $table->unsignedInteger('role_id');
                    $table->primary(['permission_id', 'role_id']);
                });
            }

            if (Schema::hasTable('users') && !Schema::hasColumn('users', 'role_id')) {
                Schema::table('users', function ($table) {
                    $table->unsignedInteger('role_id')->nullable()->after('id');
                });
            }

            // Seed Data
            if (\App\Role::count() == 0) {
                $superAdmin = \App\Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
                $agent = \App\Role::create(['name' => 'Agent', 'slug' => 'agent']);

                $perms = [
                    ['name' => 'View Reports', 'slug' => 'view_reports'],
                    ['name' => 'Manage Settings', 'slug' => 'manage_settings'],
                    ['name' => 'Manage Roles', 'slug' => 'manage_roles'],
                ];

                foreach ($perms as $p) {
                    $perm = \App\Permission::create($p);
                    $superAdmin->permissions()->attach($perm);
                }

                // Assign Super Admin to first user
                $user = \App\User::first();
                if ($user) {
                    $user->role_id = $superAdmin->id;
                    $user->save();
                }
            }
        } catch (\Exception $e) {
            \Log::error("Migration Error: " . $e->getMessage());
        }
        // END TEMPORARY

        // Summary statistics
        $stats = [
            'total_transactions' => Transaction::count(),
            'today_transactions' => Transaction::whereDate('transaction_date', today())->count(),
            'pending_transactions' => Transaction::where('status', 'pending')->count(),
            'total_customers' => Customer::count(),
            'active_currencies' => Currency::where('is_active', true)->count(),
            'active_exchange_rates' => ExchangeRate::where('is_active', true)->count(),
        ];

        // Today's profit
        $todayProfit = Transaction::whereDate('transaction_date', today())
            ->where('status', 'sent')
            ->sum('profit_amount');

        // This month's profit
        $monthProfit = Transaction::whereYear('transaction_date', date('Y'))
            ->whereMonth('transaction_date', date('m'))
            ->where('status', 'sent')
            ->sum('profit_amount');

        // Recent transactions
        $recentTransactions = Transaction::with(['customer', 'currencyFrom', 'currencyTo'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Transaction status breakdown
        $transactionsByStatus = Transaction::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        return view('dashboard', compact(
            'stats',
            'todayProfit',
            'monthProfit',
            'recentTransactions',
            'transactionsByStatus'
        ));
    }
}
