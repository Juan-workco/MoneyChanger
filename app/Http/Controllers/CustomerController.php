<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Helper;
use Log;
use Auth;
use DB;

class CustomerController extends Controller
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

            $name = $request->input('name');
            $phone = $request->input('phone');

            $name = ($name === null) ? "%" : "%$name%";
            $phone = ($phone === null) ? "%" : "%$phone%";

            $sql = "
                SELECT id, name, phone, is_active AS status, total_transactions, created_at
                FROM customers
                WHERE name LIKE :name
                    OR phone LIKE :phone
            ";

            $params = [
                'name' => $name,
                'phone' => $phone
            ];
            
            $orderByAllow = ['name, phone, created_at'];
            $orderByDefault = 'created_at asc';

            $sql = Helper::appendOrderBy($sql, $orderBy, $orderType, $orderByAllow, $orderByDefault);
            $data = Helper::paginateData($sql, $params, $page);

            $aryStatus = self::getStatusOptions();

            foreach ($data['results'] as $d)
            {
                $d->active_desc = Helper::getOptionsValue($aryStatus, $d->status);
            }

            return json_encode(['status' => 1, 'data' => $data]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error', __('error.internal_error')]);
        }
    }
    
    public static function createCustomer(Request $request)
    {
        try
        {
            $adminId = Auth::id();
            $name = $request->input('name');
            $phone = $request->input('phone');

            $name = trim($name);
            $phone = trim($phone);

            $db = DB::SELECT("SELECT 1 FROM customers WHERE name = ? AND phone = ? AND agent_id = ?", [$name, $phone, $adminId]);

            if (!empty($db))
            {
                return json_encode(['status' => 1, 'error' => "Customer already exists"]);
            }

            DB::INSERT("
                INSERT INTO customers (`name`, `phone`, `is_active`, `agent_id`, `created_at`)
                VALUES (?, ?, 1, ?, NOW())
            ", [$name, $phone, $adminId]);

            return json_encode(['status' => 1]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 1, 'error' => __('error.internal_error')]);
        }
    }
    
    public static function editCustomer(Request $request)
    {
        try
        {
            $adminId = Auth::id();
            $id = $request->input('id');
            $name = $request->input('name');
            $phone = $request->input('phone');
            $status = $request->input('status');

            $name = trim($name);
            $phone = trim($phone);

            if (!in_array($status, [0,1]))
            {
                return json_encode(['status' => 0, 'error' => "Invalid Status"]);
            }

            $db = DB::SELECT("
                SELECT 1
                FROM customers
                WHERE id = ? AND agent_id = ?
            ", [$id, $adminId]);

            if (empty($db))
            {
                return json_encode(['status' => 0, 'error' => "Customer not exists"]);
            }

            $db = DB::SELECT("
                SELECT 1
                FROM customers
                WHERE agent_id = ? 
                    AND id != ?
                    AND (name = ? OR phone = ? )
            ", [$adminId, $id, $name, $phone]);

            if (!empty($db))
            {
                return json_encode(['status' => 0, 'error' => "Name or phone already registed. Please check again your customer"]);
            }

            DB::UPDATE("
                UPDATE customers
                SET name = ?, 
                    phone = ?,
                    is_active = ?
                WHERE id = ?
            ", [$name, $phone, $status,  $id]);

            return json_encode(['status' => 1]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => __('error.internal_error')]);
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
