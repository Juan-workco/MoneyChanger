<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use Auth;
use Log;

class CurrencyController extends Controller
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
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $code = $request->input('code');
            $name = $request->input('name');

            $code = ($code === null) ? "%" : "%$code%";
            $name = ($name === null) ? "%" : "%$name%";

            $sql = "
                SELECT code, name, symbol, buy_rate, sell_rate, status 
                FROM currencies
                WHERE code LIKE :code
                    OR name LIKE :name
            ";

            $params = [
                'code' => $code,
                'name' => $name
            ];
            
            $orderByAllow = ['code, name, status'];
            $orderByDefault = 'code asc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            $aryStatus = self::getStatusOptions();

            foreach ($data['results'] as $d)
            {
                $d->status_desc = Helper::getOptionsValue($aryStatus, $d->status);
            }

            return json_encode(['status' => 1, 'data' => $data]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => 'Internal Error']);
        }
    }

    public static function createCurrency(Request $request)
    {
        try
        {
            $code = $request->input('code');
            $name = $request->input('name');
            $symbol = $request->input('symbol');
            $buyRate = $request->input('buy_rate');
            $sellRate = $request->input('sell_rate');
            $status = $request->input('status');

            $code = strtoupper( trim($code) );
            $name = trim($name);
            $symbol = trim($symbol);
            
            $db = DB::SELECT("SELECT 1 FROM currencies WHERE code = ?", [$code]);

            if (count($db) > 0)
            {
                return json_encode(['status' => 0, 'error' => "Code duplicate. There is currency created with the same code."]);
            }

            if (!is_numeric($buyRate))
            {
                return json_encode(['status' => 0, 'error' => "Buy Rate field only allow contains numeric value"]);
            }

            if (!is_numeric($buyRate))
            {
                return json_encode(['status' => 0, 'error' => "Sell Rate field only allow contains numeric value"]);
            }

            if ($buyRate < 0)
            {
                return json_encode(['status' => 0, 'error' => "Buy Rate must greater than 0"]);
            }

            if (!is_numeric($buyRate))
            {
                return json_encode(['status' => 0, 'error' => "Sell Rate must greater than 0"]);
            }

            if (!in_array($status, [0, 1]))
            {
                return json_encode(['status' => 0, 'error' => "Status Invalid"]);
            }

            DB::INSERT("
                INSERT INTO currencies (`code`, `name`, `symbol`, `buy_rate`, `sell_rate`, `status`, `created_at`)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [$code, $name, $symbol, $buyRate, $sellRate, $status]);

            return json_encode(['status' => 1]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => 'Internal Error']);
        }
    }

    public static function edit(Request $request)
    {
        try
        {
            $code = $request->input('code');
            $name = $request->input('name');
            $symbol = $request->input('symbol');
            $buyRate = $request->input('buy_rate');
            $sellRate = $request->input('sell_rate');
            $status = $request->input('status');

            $code = strtoupper( trim($code) );
            $name = trim($name);
            $symbol = trim($symbol);
            
            $db = DB::SELECT("SELECT 1 FROM currencies WHERE code = ?", [$code]);

            if (count($db) == 0)
            {
                return json_encode(['status' => 0, 'error' => "Invalid Currency"]);
            }

            if (!is_numeric($buyRate))
            {
                return json_encode(['status' => 0, 'error' => "Buy Rate field only allow contains numeric value"]);
            }

            if (!is_numeric($buyRate))
            {
                return json_encode(['status' => 0, 'error' => "Sell Rate field only allow contains numeric value"]);
            }

            if ($buyRate < 0)
            {
                return json_encode(['status' => 0, 'error' => "Buy Rate must greater than 0"]);
            }

            if (!is_numeric($buyRate))
            {
                return json_encode(['status' => 0, 'error' => "Sell Rate must greater than 0"]);
            }

            if (!in_array($status, [0, 1]))
            {
                return json_encode(['status' => 0, 'error' => "Status Invalid"]);
            }

            DB::UPDATE("
                UPDATE currencies 
                SET name = ?,
                    symbol = ?,
                    buy_rate = ?,
                    sell_rate = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE code = ?
            ", [$name, $symbol, $buyRate, $sellRate, $status, $code]);

            return json_encode(['status' => 1]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => 'Internal Error']);
        }
    }

    public static function getStatusOptions()
    {
        return [
            [0, 'Inactive'],
            [1, 'Active']
        ];
    }
}
