<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Log;

class PaymentController extends Controller
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
    public static function getPaymentMethodList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $sql = "
                SELECT id, type, status FROM payment_method
            ";

            $params = [];

            $orderByAllow = ['type'];
            $orderByDefault = 'type asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            $aryStatus = self::getStatusOptions();

            foreach ($data['results'] as $d)
            {
                $d->status_desc = Helper::getOptionsValue($aryStatus, $d->status);
            }

            return json_encode(['status' => 1, 'data' => $data]);
        }
        catch (\Eception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error', __('error.internal_error')]);
        }
    }

    public static function createPaymentMethod(Request $request)
    {
        try
        {
            $type = $request->input('name');
            $status = $request->input('status');

            $type = ucfirst($type);

            $db = DB::SELECT("SELECT 1 FROM payment_method WHERE type = ?", [$type]);

            if (count($db) > 0)
            {
                return json_encode(['status' => 0, 'error' => 'Payment method already exists.']);
            }

            if (!in_array($status, [0,1]))
            {
                return json_encode(['status' => 0, 'error' => 'Status Invalid']);
            }

            DB::INSERT("
                INSERT INTO payment_method (`type`, `status`, `created_at`)
                VALUES (?, ?, NOW())
            ", [$type, $status]);

            return json_encode(['status' => 1]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => __('error.internal_error')]);
        }
    }

    public static function updatePaymentMethod(Request $request)
    {
        try
        {
            $id = $request->input('id');

            $db = DB::SELECT("SELECT status FROM payment_method WHERE id = ?", [$id]);

            if (count($db) == 0)
            {
                return json_encode(['status' => 0, 'error' => 'Invalid payment method']);
            }

            $newStatus = ($db[0]->status == 1) ? 0 : 1;

            DB::UPDATE("
                UPDATE payment_method
                SET status = ?
                WHERE id = ?
            ", [$newStatus, $id]);
            
            return json_encode(['status' => 1]);
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => __('error.internal_error')]);
        }
    }

    public static function getPaymentAccountList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $method = $request->input('method');
            $name = $request->input('name');
            $value = $request->input('value');

            $name = ($name == null) ? '%' : "%$name%";
            $value = ($value == null) ? '%' : "%$value%";

            $sql = "
                SELECT a.id, a.name, a.value, b.type AS method
                FROM payment_account a
                INNER JOIN payment_method b
                    ON a.method_id = b.id
                WHERE a.name LIKE :name
                    AND a.value LIKE :value
                    AND (a.method_id = :method1 OR :method2 = 0)
            ";

            $params = [
                'name' => $name,
                'value' => $value,
                'method1' => $method,
                'method2' => $method
            ];

            $orderByAllow = ['name, method'];
            $orderByDefault = 'method asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);
            
            return json_encode(['status' => 1, 'data' => $data]);
        }
        catch (\Eception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error', __('error.internal_error')]);
        }
    }

    public static function createPaymentAccount(Request $request)
    {
        try
        {
            $method = $request->input('method');
            $name = $request->input('name');
            $value = $request->input('value');

            $name = trim($name);
            $value = trim($value);

            $db = DB::SELECT("SELECT * FROM payment_method WHERE status = 1 AND id = ?", [$method]);

            if (count($db) == 0)
            {
                return json_encode(['status' => 0, 'error' => 'Invalid payment method.']);
            }

            DB::INSERT("
                INSERT INTO payment_account (`method_id`, `name`, `value`, `created_at`)
                VALUES (?, ?, ?, NOW())
            ", [$method, $name, $value]);

            return json_encode(['status' => 1]);
        }
        catch (\Eception $e)
        {
            Log::error($e);
            return json_encode(['status' => 0, 'error' => __('error.internal_error')]);
        }
    }

    public static function getPaymentMethodOptions()
    {
        try
        {
            $db = DB::SELECT("
                SELECT id, type
                FROM payment_method
                WHERE status = 1
            ");

            return $db;
        }
        catch (\Exception $e)
        {
            Log::error($e);
            return [];
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
