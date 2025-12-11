<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use Auth;
use Log;
use DB;

class RemittanceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public static function getList(Request $request)
    {
        try
        {
            $id = Auth::id();

            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $name = $request->input('name');
            $sDate = $request->input('sdate');
            $eDate = $request->input('edate');

            $name = (!$name) ? '%' : "%$name%";
            $sDate = date('Y-m-d H:i:s', strtotime("$sDate 00:00:00 -8hours"));
            $eDate = date('Y-m-d H:i:s', strtotime("$eDate 23:59:59 -8hours"));

            $sql = "
                SELECT a.id, a.amount_from, a.amount_to, a.sell_rate AS rate, a.status, a.created_at,
                    b.name AS customer_name, c.fullname AS approved_by, d.fullname AS created_by,
                    e.code AS currency_from, e.symbol AS currency_from_symbol,
                    f.code AS currency_to, f.symbol AS currency_to_symbol
                FROM transactions a
                INNER JOIN customers b ON a.customer_id = b.id
                LEFT JOIN admin c ON a.approved_by = c.id
                INNER JOIN admin d ON a.created_by = d.id
                INNER JOIN currencies e ON a.currency_from_id = e.id
                INNER JOIN currencies f ON a.currency_to_id = f.id
                WHERE a.created_by = :agent_id
                    AND b.name LIKE :name
                    AND a.created_at BETWEEN :sdate AND :edate
            ";

            $params = [
                'agent_id' => $id,
                'name' => $name,
                'sdate' => $sDate,
                'edate' => $eDate
            ];

            $orderByAllow = ['id', 'amount_from', 'amount_to', 'rate', 'status', 'created_at'];
            $orderByDefault = 'id ASC';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);

            $data = Helper::paginateData($sql, $params, $page);

            foreach ($data['results'] as $d)
            {
                $d->created_at = date("Y-m-d H:i:s", strtotime("$d->created_at +8hours"));
            }

            return json_encode(['status' => 1, 'data' => $data]);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return json_encode(['status' => 0, 'error' => __('error.internal_error')]);
        }
    }

    public static function createRemittance (Request $request)
    {
        try
        {
            $id = Auth::id();

            $customer = $request->input('customer');
            $amountFrom = $request->input('from_amount');
            $amountTo = $request->input('to_amount');
            $currencyFrom = $request->input('from_currency');
            $currencyTo = $request->input('to_currency');

            log::debug($request);

            $db = DB::SELECT("SELECT 1 FROM customers WHERE id = ? AND agent_id = ?", [$customer, $id]);

            if (empty($db))
            {
                return json_encode(['status' => 0, 'error' => "Invalid Customer"]);
            }

            $db = DB::SELECT("SELECT 1 FROM system_settings WHERE setting_key = 'currency_id'AND setting_value = ?", [$currencyFrom]);

            log::debug($currencyFrom);

            if (empty($db))
            {
                return json_encode(['status' => 0, 'error' => "Invalid From Currency"]);
            }

            $db = DB::SELECT("SELECT 1 FROM currencies WHERE id = ?", [$currencyTo]);

            if (empty($db))
            {
                return json_encode(['status' => 0, 'error' => "Invalid To Currency"]);
            }

            if (!is_numeric($amountFrom))
            {
                return json_encode(['status' => 0, 'error' => "Amount From is not a valid numeric value"]);
            }

            if (!is_numeric($amountTo))
            {
                return json_encode(['status' => 0, 'error' => "Amount From is not a valid numeric value"]);
            }

            $db = DB::SELECT("
                SELECT sell_rate, buy_rate
                FROM exchange_rates
                WHERE currency_id = ?
            ", [$currencyTo]);

            $buyRate = $db[0]->buy_rate;
            $sellRate = $db[0]->sell_rate;

            $db = DB::INSERT("
                INSERT INTO transactions
                    (customer_id, currency_from_id, currency_to_id, amount_from, amount_to, buy_rate, sell_rate, status, created_by, created_at)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
            ", [
                $customer,
                $currencyFrom,
                $currencyTo,
                $amountFrom,
                $amountTo,
                $buyRate,
                $sellRate,
                $id
            ]);

            return json_encode(['status' => 1]);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return json_encode(['status' => 0, 'error' => __('error.internal_error')]);
        }
    }

    public static function getCustomers()
    {
        $id = Auth::id();

        $db = DB::SELECT("
            SELECT id, name
            FROM customers
            WHERE agent_id = ?
        ", [$id]);

        return $db;
    }

    public static function getCurrencies()
    {
        $db = DB::SELECT("
            SELECT a.id, a.code, a.symbol, b.sell_rate AS rate
            FROM currencies a
            INNER JOIN exchange_rates b ON a.id = b.currency_id
        ");

        return $db;
    }

    public static function getDefaultCurrencies()
    {
        $db = DB::SELECT("SELECT setting_value FROM system_settings WHERE setting_key = 'currency_id'");

        if (!empty($db))
            return $db[0]->setting_value;

        return 0;
    }
}
