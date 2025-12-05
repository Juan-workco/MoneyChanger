<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;

use Auth;
use Log;

class AdminController extends Controller
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

    public static function getAdmin(Request $request)
    {
        try
        {
            $id = $request->input('id');
            $timezone = Helper::getCurrencyTimezone();

            $sql = "
                        SELECT a.id,a.username,a.created_at, a.status,b.role_id
                        FROM admin a
                        LEFT JOIN admin_role b ON a.id = b.admin_id 
                        WHERE a.id = :id
                        AND a.type = 'c'
                    ";

            $params = [
                    'id' => $id
                ];

            $data = DB::select($sql,$params);

            foreach($data as $d)
            {
                $d->created_at = date('Y-m-d H:i:s',strtotime($d->created_at. '+' .$timezone.' hours'));
            }

            return $data[0];
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function getOptionsStatus()
    {
        return  [
                ['a', __('option.admin.active')]
                ,['i', __('option.admin.inactive')]
            ];
    }

    public static function checkUsername(Request $request)
    {
        $username = $request->input('username');

        if(strlen($username) >= 5 && strlen($username) <= 20 && !preg_match('/[^a-zA-Z\.0-9]/', $username))
        {

            $db = DB::select('SELECT username FROM admin 
            WHERE username = ?',[$username]);

            if(sizeof($db) > 0)
            {
                return 1; //username exist
            }
            else
            {
                return 0; //username valid
            }
        }
        elseif(preg_match('/[^a-zA-Z\.0-9]/', $username))
        {
            return 2; //contain special character
        }
        elseif(strlen($username) < 5)
        {
            return 3; 
        }
        elseif(strlen($username) > 20)
        {
            return 4;
        }
    }


    public static function getList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $username = $request->input('username');
            $timezone = Helper::getCurrencyTimezone();

            $sql = "
                SELECT a.id,a.username,a.created_at,a.status,c.type,c.is_deleted
                FROM admin a
                LEFT JOIN admin_role b ON a.id = b.admin_id 
                LEFT JOIN role c ON c.id = b.role_id
                WHERE a.type = 'c' AND a.id > 1
                    AND username LIKE :username
                ";

            $params = [
                    'username' => '%'.$username.'%'
                ];

            $orderByAllow = ['username','created_at'];
            $orderByDefault = 'username asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            foreach($data['results'] as $d)
            {
                $d->created_at = date('Y-m-d H:i:s',strtotime($d->created_at. '+' .$timezone.' hours'));
            }

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }

    public static function update(Request $request)
    {
        try
        {
            $id = $request->input('id');
            $status = $request->input('status');
            $roleId = $request->input('role');
            
            //validation
            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                $response = ['status' => 0
                            ,'error' => __('error.admin.invalid_status')
                            ];

                return json_encode($response);
            }

            $sql = "
                UPDATE admin
                SET status = :status
                WHERE id = :id
                ";

            $params = [
                    'id' => $id
                    ,'status' => $status
                ];

            $data = DB::update($sql,$params);

            $sql3 = "
                        INSERT INTO admin_role
                        (admin_id,role_id)
                        VALUES
                        (:admin_id, :role_id)
                        ON DUPLICATE KEY UPDATE 
                        role_id = :role_id1
                        ";
            $params3 = [
                    'admin_id' => $id
                    ,'role_id' => $roleId
                    ,'role_id1' => $roleId
                ];

            DB::insert($sql3, $params3);

            //logging
            $request["action_details"] = 10;
            Helper::log($request,'update');
            
            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            $response = ['status' => 0
                        ,'error' => __('error.admin.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function create(Request $request)
    {
        try
        {
            $username = $request->input('username');
            $password = $request->input('password');
            $status = $request->input('status');
            $roleId = $request->input('role');
          
            //validation
            $errMsg = [];

            if(self::checkUser($username) == false)
            {
                array_push($errMsg,"The username have been created");
            }

             //Validate username length - must between 1 to 20
            if(!Helper::checkInputLength($username, 5, 20))
            {
                array_push($errMsg, __('error.merchant.error_username_5_20'));
            }

            //Validate username - must be alphanumeric or dot only
            if(!Helper::checkInputFormat('alphanumericWithDot', $username))
            {
                array_push($errMsg, __('error.admin.error_alpha_dot'));
            }

            if(!$password)
            {
                array_push($errMsg, __('error.admin.invalid_password'));
            }

            if(!Helper::checkValidOptions(self::getOptionsStatus(),$status))
            {
                array_push($errMsg, __('error.admin.invalid_status'));
            }

             if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.admin.invalid_password_length'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $sql = "
                INSERT INTO admin(username,password,status,created_at,type, is_sub)
                VALUES(:username,:password,:status,NOW(),'c', 0)
                ";

            $params = [
                    'username' => $username
                    ,'password' => Hash::make($password)
                    ,'status' => $status
                ];

            $data = DB::insert($sql,$params);
            $id = DB::getPdo()->lastInsertId();

            $sql = "
                INSERT INTO admin_role(admin_id,role_id)
                VALUES(:id,:role_id)
                ";

            $params = [
                    'id' => $id
                    ,'role_id' => $roleId
                ];


            $data = DB::insert($sql,$params);

            $userId = Auth::user()->id;

            $sql = "
                SELECT super_admin FROM admin 
                WHERE id = :id
                ";

            $params = [
                    'id' => $userId
                ];

            $db = DB::select($sql, $params);
            
            //logging
            $request["log_old"] = "{}";
            $request["id"] = $id;
            $request["password"] = "*";
            $request["action_details"] = 9;
            
            Helper::log($request,'create');

            $response = ['status' => 1,
                        'admin_type' => $db];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            $errMsg = '';

            if($e instanceof \PDOException) 
            {
                if($e->errorInfo[1] == 1062)
                    $errMsg = __('error.admin.duplicate_username');
            }

            if($errMsg == '')
                $errMsg = __('error.admin.internal_error');
            
            $response = ['status' => 0
                        ,'error' => $errMsg
                        ];

            return json_encode($response);
        }
    }

    public static function changePassword(Request $request)
    {
        try 
        {
            $user_id = $request->input('id');
            $password = $request->input('password');

            //validation
            $errMsg = [];

            if(!$password)
            {
                array_push($errMsg, __('error.admin.invalid_password'));
            }

            if(!Helper::checkInputLength($password, 8, 15))
            {
                array_push($errMsg, __('error.admin.invalid_password_length'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $new_password = Hash::make($password);

            $db = DB::update('UPDATE admin SET password = ? WHERE id = ?',
                    [$new_password,$user_id]
                  );

            //logging
            $request["password"] = "*";
            $request["log_old"] = '{"password" : "*"}';
            $request["action_details"] = 11;

            Helper::log($request,'update');

            $response = ['status' => 1];

            return json_encode($response);
            
        } 
        catch (Exception $e) 
        {
            $response = ['status' => 0
                        ,'error' => __('error.admin.internal_error')
                        ];

            return json_encode($response);
        }
    }

    public static function checkUser($username)
    {
        $select = DB::select('
            SELECT username FROM admin 
            WHERE username = ?',[$username]);

        if(sizeOf($select) > 0)
        {
            return false;
        }

        return true;
    }

    public static function createRoles(Request $request)
    {
        try
        {
            $type = $request->input('name');
            $check = $request->input('check');
          
            //validation
            $errMsg = [];

            //check exist type
            $db = DB::select("SELECT type
                                FROM role
                                WHERE type = ?"
                                ,[$type]
                            );

            if(sizeof($db) > 0)
            {
                array_push($errMsg, "Role Type exists");
            }

            //Validate username - must be alphanumeric
            if(!Helper::checkInputFormat('alphanumeric', $type))
            {
                array_push($errMsg, __('error.admin.error_alpha'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            DB::insert("
                INSERT INTO role(type, created_at)
                VALUES(?,NOW())
                ",[$type]);

            $id = DB::getPdo()->lastInsertId();

            $paramsPermission = [];

            foreach($check as $c)
            {
                $arr = explode('-',$c);

                array_push($paramsPermission,[$id,$arr[0],$arr[1]]);
            }

            $sql = "
                        INSERT INTO role_permissions
                        (role_id,type,is_deleted)
                        VALUES
                        :(?,?,?):
                        ON DUPLICATE KEY UPDATE 
                        is_deleted = VALUES(is_deleted)
                        ";

            $pdo = Helper::prepareBulkInsert($sql,$paramsPermission);

            DB::insert($pdo['sql'],$pdo['params']);
            
            // logging
            // $request["log_old"] = "{}";
            // $request["action_details"] = 23;
            
            // Helper::log($request,'Create');

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::debug($e);

            return ['status' => 0];
        }
    }

    public static function getRolesList(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $type = $request->input('name');

            $sql = "
                SELECT id,type
                FROM role 
                WHERE type LIKE :type
                ";

            $params = [
                    'type' => '%'.$type.'%'
                ];

            $orderByAllow = ['id','type'];
            $orderByDefault = 'id asc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }

    public static function deleteRole(Request $request)
    {
        try
        {
            $id = $request->input('id');

            $errMsg = [];

            $db = DB::select("
                SELECT *
                FROM role
                WHERE id = ?"
                ,[$id]
            );

            if(sizeof($db) == 0)
            {
                array_push($errMsg, "Invalid Role Type");
            }
            
            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            DB::DELETE("DELETE FROM role WHERE id = ?" ,[$id]);

            // $request['name'] = '';
            // $request["action_details"] = 24;
            
            // Helper::log($request,'Delete');

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }

    public static function getOptionsAdminRoles()
    {
        try
        {
            $data = [];

            $db = DB::select("
                                SELECT id, type
                                FROM role
                                WHERE is_deleted = 0
                    ");
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
       
        foreach($db as $d)
        {
            $data[] = [$d->id, $d->type];
        }
        
        return $data;
    }

     public static function getRolesPermission(Request $request)
    {
        try
        {
            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $id = $request->input('id');

            $sql = "
                SELECT a.type, a.is_deleted, b.type'role_name'
                FROM role_permissions a
                LEFT JOIN role b ON a.role_id = b.id
                WHERE role_id = :id
                ";

            $params = [
                    'id' => $id
                ];

            $orderByAllow = ['type','is_deleted'];
            $orderByDefault = '';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }

    public static function editRolesPermission(Request $request)
    {
        try
        {
            $name = $request->input('name');
            $check = $request->input('check');
            $id = $request->input('id');

            $errMsg = [];

            //check exist name
            $db = DB::select("
                SELECT *
                FROM role
                WHERE id = ?"
                ,[$id]
            );

            if(sizeof($db) < 0)
            {
                array_push($errMsg, "Role not exist");
            }
            
            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $paramsPermission = [];

            foreach($check as $c)
            {
                $arr = explode('-',$c);

                array_push($paramsPermission,[$id,$arr[0],$arr[1]]);
            }

            $sql = "
                INSERT INTO role_permissions
                (role_id,type,is_deleted)
                VALUES
                :(?,?,?):
                ON DUPLICATE KEY UPDATE 
                is_deleted = VALUES(is_deleted)
            ";

            $pdo = Helper::prepareBulkInsert($sql,$paramsPermission);

            DB::insert($pdo['sql'],$pdo['params']);

            // unset($request["id"]);
            // $request["action_details"] = 25;
            
            // Helper::log($request,'Edit');

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            Log::debug($e);
            return [];
        }
    }
}
