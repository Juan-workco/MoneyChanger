<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use App;
use Gate;
use Log;
use Session;

class Helper extends Controller
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

    public static function checkUAC($module)
    {
        return true;
        if (Gate::denies($module, auth()->user())) 
        {
            abort(404);
        }
    }

    public static function checkUACFunctionPermission($module, $status = 0)
    {
        $arrPermission = [];

        foreach($module as $type)
        {
            if (Gate::denies($type, auth()->user())) 
            {
                array_push($arrPermission, false);
            }
            else
            {
                array_push($arrPermission, true);
            }
        }

        if(in_array(false, $arrPermission))
        {
            return ["status" => $status, "error" => __('error.sb.api_setting.permission_denied')];
        }

        return ["status" => 1];
    }

    public static function checkUserPermissions($type)
    {
        $user = Auth::user();
        $userId = $user->id;

        $db = DB::SELECT("

                            SELECT b.is_deleted 
                            FROM admin_role a 
                            LEFT JOIN role b 
                                ON a.role_id = b.id
                            WHERE a.admin_id = ? AND b.is_deleted = 0
                         ", [$userId]
                        );

        if(sizeof($db) > 0)
        {
             $db = DB::SELECT("

                            SELECT a.is_deleted 
                            FROM role_permissions a 
                            LEFT JOIN admin_role b 
                                ON a.role_id = b.role_id
                            WHERE b.admin_id = ? AND a.type = ? AND a.is_deleted = 1
                         ", [$userId, $type]
                        );

            if (sizeOf($db) != 0) 
            {
               return false;
            }
        }

        return true;
    }

    public static function checkPermissions($type)
    {
        $userId = Auth::user()->id;
        $db = DB::SELECT("
                            SELECT is_deleted
                            FROM permissions
                            WHERE admin_id =? AND type = ? AND is_deleted = 1
                         ", [$userId, $type]
                        );
        if (sizeOf($db) != 0) 
        {
           return false;
        }

        return true;
    }

    public static function convertToIndexedArray($data)
    {
        //field name must be "value" and "text"

        try
        {
            $result = [];
            
            foreach($data as $d)
            { 
                array_push($result,[$d->value,$d->text]);
            } 

            return $result;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault = '')
    {
        $orderTypeAllow = ['asc','desc'];

        $strOrder = '';

        if(in_array($orderBy,$orderByAllow))
        {
            if(in_array($orderType,$orderTypeAllow))
            {
                $strOrder = ' '.$orderBy.' '.$orderType;

            }
        }

        if($strOrder == '')
            $strOrder = $orderByDefault;

        if($strOrder != '')
            $strOrder = ' ORDER BY '.$strOrder;

        return $sql.$strOrder;
    }

    public static function paginateData($sql,$params,$page,$pageSize=0)
    {
        //pageNo = index 1-based
        //params :pagination_row and :pagination_size : reserved

        if($page == null)
            $page = 1;

        if($pageSize==0)
            $pageSize = env('GRID_PAGESIZE');

        //get data count
        $sqlCount = "SELECT COUNT(0) AS count FROM (".$sql.") AS a";
        $dbCount = DB::select($sqlCount,$params);

        //get data
        $sqlData = $sql." LIMIT :pagination_row,:pagination_size";

        $params['pagination_row'] = (($page - 1) * $pageSize);
        $params['pagination_size'] = $pageSize;

        $dbData = DB::select($sqlData,$params);

        $data = ['count' => $dbCount[0]->count,'page_size' => $pageSize,'results' => $dbData];

        return $data; 
    }

    public static function noPaginateData($sql,$params, $limit=2000)
    {
        //get data count
        $sqlCount = "SELECT COUNT(0) AS count FROM (".$sql.") AS a";
        $dbCount = DB::select($sqlCount,$params);

        //get data
        $sqlData =  $sql." LIMIT ".$limit;
        $dbData = DB::select($sqlData,$params);

        $data = ['count' => $dbCount[0]->count,'page_size' => 1,'results' => $dbData];

        return $data; 
    }


    public static function generateOptions($aryOptions,$default)
    {
        foreach ($aryOptions as $option) 
        {
            $selected = '';

            if($option[0] == $default)
                $selected = 'selected';

            echo '<option value="'.$option[0].'" '.$selected.'>'.$option[1].'</option>';
        }
    }

    public static function checkValidOptions($aryOptions,$value)
    {
        foreach ($aryOptions as $option) 
        {
            if($option[0] == $value)
                return true;
        }

        return false;
    }

    public static function getOptionsValue($aryOptions,$value)
    {
        foreach ($aryOptions as $option) 
        {
            if($option[0] == $value)
                return $option[1];
        }

        return '';
    }

    public static function getLocaleFlag() 
    {
        $localeFlag = array(
                    'en' => 'gb'
                    ,'kr' => 'kr'
                );

        return $localeFlag[App::getLocale()];
    }

    public static function logAPI($type,$content) 
    {
        //logging for debug
        $db = DB::insert('
            INSERT INTO log_json 
            (type,content)
            VALUES
            (?,?)'
            ,[$type,$content]);
    }

    public static function getData($url,$header = '')
    {
        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            
            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function postData($url,$data,$header = '')
    {
        try
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);

            if($header == '')
            {
                $header = array('Content-Type: application/json');
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

            if (is_array($data))
            {
                $data = json_encode($data);
            }
            
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function generateUniqueId($length = 64)
    {
        //minimum length 64

        $length = $length < 64 ? 64 : $length;

        $str = uniqid('',true); //23 char
        $str = md5($str); //32 char

        $str = self::generateRandomString($length - 32).$str;
        return $str;
    }

    public static function generateRandomString($length = 1) 
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) 
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getTimestamp() 
    {
        $microDate = microtime();
        $aryDate = explode(" ",$microDate);

        $date = date("Y-m-d H:i:s",$aryDate[1]);

        $ms = round($aryDate[0] * 1000);
        $ms = sprintf('%03d', $ms);

        return $date.'.'.$ms;
    }

    public static function checkInputFormat($type, $data)
    {
        //alphanumeric format
        if($type=='alphanumeric')
        {
            if(preg_match('/[^a-zA-Z0-9]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //alphabet format
        if($type=='alphabet')
        {
            if(preg_match('/[^A-Z]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //alphanumeric With Dot format
        else if($type=='alphanumericWithDot')
        {
            if(preg_match('/[^a-zA-Z\.0-9]/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //alphanumeric With Dash and Space
        else if($type =='alphanumericWithDashSpace')
        {
            if(preg_match('/[^a-zA-Z\-\s0-9]/', $data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //amount format
        else if($type=='amount')
        {
            if(!preg_match('/^\\d+(\\.\\d{1,2})?$/D',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        } 
        //numeric format
        else if($type=='numeric')
        {
            if(!preg_match('/^[1-9][0-9]*$/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        // numeric with zero
        else if($type=='numericWithZero')
        {
            if(!preg_match('/^[0-9][0-9]*$/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //significant figures- up to 9 before decimal point and up to 2 after
        //non-significant figures are allowed (trailing 0s)
        else if($type =='positiveTwoDecimal')
        {
            if(!preg_match('/^[+]?0*[0-9]{0,9}(\.)?[0-9]?[0-9]?0*$/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }
        //same criteria with 'positiveTwoDecimal' but both sign allowed
        else if($type=='twoDecimal')
        {
            if(!preg_match('/^[+,-]?0*[0-9]{0,9}(\.)?([0-9]?[0-9]?0*)$/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }   
        else if($type == 'positiveInteger')
        {
            if(!preg_match('/^[0-9]+$/', $data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }     
        //integer format
        else if($type=='integer')
        {
            if(!preg_match('/^[0-9]+$/',$data))
            {
                return false;
            }
            else
            {
                return true;
            }
        }            
    }

    public static function checkInputLength($data, $min, $max)
    {
        if(strlen($data)<$min || strlen($data)>$max)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function log(Request $request,$action)
    {
        try
        {
            $userId = Auth::id();
            $ip = \Request::ip();

            $referer = parse_url($request->headers->get('referer'));
            $path = $referer['path'];
            $query = '';

            //data that doesn't being stored in new data
            $except = [
                        '_token'
                        ,'log_old'
                        ,'username'
                        ,'action_details'
                        ,'group_id'
                        ,'id'
                        ,'prefix1'
                        ,'prefix2'
                        ,'merc_id'
                        ,'checkbox'
                    ];

            $username = $request->input('username');
            $actionDetails = $request->input('action_details');

            if(array_key_exists('query', $referer))
            {
                $query = $referer['query'];
            }
            else if($request->input('id'))
            {
                $query = "id=".$request->input('id');
            }

            $logOld = $request->input('log_old');
            $logNew = $request->except($except);
            $logNew = json_encode($logNew);

            DB::insert("
                INSERT INTO admin_log(user_id,path,query,action,data_old,data_new,ip_address,username,action_details)
                VALUES
                (?,?,?,?,?,?,?,?,?)
                "
                ,[  $userId
                    ,$path
                    ,$query
                    ,$action
                    ,$logOld
                    ,$logNew
                    ,$ip
                    ,$username
                    ,$actionDetails
                ]);
            
        }
        catch(\Exception $e)
        {
            Log::info($e);
        }
    }

    public static function validurl($uri)
    {
        if(preg_match( '/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}'.'((:[0-9]{1,5})?\\/.*)?$/i' ,$uri))
        {
          return $uri;
        }
        else
        {
            return false;
        }
    }

    public static function validip($validateIP)
    {
        if(preg_match( '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/' ,$validateIP))
        {
          return $validateIP;
        }
        else
        {
            return false;
        }
    }

    public static function prepareWhereIn($sql,$params)
    {
        $returnSql = $sql;
        $returnParams = [];

        $paramCount = 0;

        for($i = 0 ; $i < sizeOf($params) ; $i++)
        {
            if(is_array($params[$i]))
            {
                $explodeParams = str_repeat('?, ', count($params[$i]));
                $explodeParams = rtrim($explodeParams, ', ');

                $pos = self::strposOffset('?', $returnSql, $paramCount + 1);
                
                $returnSql = substr_replace($returnSql,$explodeParams,$pos,1);
                
                for($j = 0 ; $j < sizeOf($params[$i]) ; $j++)
                {
                    array_push($returnParams,$params[$i][$j]);
                    $paramCount++;
                }
            }
            else
            {
                array_push($returnParams,$params[$i]);
                $paramCount++;
            }
        }

        return ['sql' => $returnSql , 'params' => $returnParams];
    }

    public static function prepareBulkInsert($sql,$aryParams)
    {
        //reserved keyword :( and ):

        try
        {
            $returnSQL = '';
            $returnParams = [];

            $valueStart = self::strposOffset(':(', $sql, 1);
            $valueEnd = self::strposOffset('):', $sql, 1);

            $value = substr($sql,$valueStart + 1,$valueEnd - $valueStart);

            $values = str_repeat(','.$value, count($aryParams));
            $values = ltrim($values,',');

            $returnSQL = substr_replace($sql,$values,$valueStart,$valueEnd - $valueStart + 2);

            foreach ($aryParams as $params) 
            {
                foreach ($params as $param) 
                {
                    array_push($returnParams,$param);
                }
            }

            return ['sql' => $returnSQL,'params' => $returnParams];
        }
        catch (\Exception $e) 
        {
            return [];
        }
    }

    public static function strposOffset($search, $string, $offset)
    {
        $arr = explode($search, $string);

        switch($offset)
        {
            case $offset == 0:
            return false;
            break;
        
            case $offset > max(array_keys($arr)):
            return false;
            break;

            default:
            return strlen(implode($search, array_slice($arr, 0, $offset)));
        }
    }

    public static function convertSQLBindingParams($sql,$params)
    {
        //convert sql with params with ? to :
        //reserved binding params key : params_

        $returnSql = $sql;
        $returnParams = [];

        $paramCount = 0;

        for($i = 0 ; $i < sizeOf($params) ; $i++)
        {

            $pos = self::strposOffset('?', $returnSql, $paramCount + 1);
            $returnSql = substr_replace($returnSql,':params_'.$i,$pos,1);

            $returnParams['params_'.$i] = $params[$i];
        }

        return ['sql' => $returnSql , 'params' => $returnParams];
    }

    public static function getTierCodeWithoutSub()
    {
        try
        {   
            $user = Auth::user();

            $userType = $user->type;
            $userId = $user->merc_id;

            //CA got full access, so no tier code
            if($userType == 'c')
                return '';

            $db = DB::select("
                            SELECT merc_cd
                            FROM api_setting
                            WHERE merc_id =?
                         ", [$userId]
                        );

            if (sizeOf($db) == 0) 
            {
               return false;
            }

            $username = $db[0]->merc_cd;

            return $username;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function getTierToLoad($tier)
    {
        try
        {   
            $loadIndex = 0;
            $maxIndex = 0;

            $userType = Auth::user()->type;

            if($userType == 'c')
                $maxIndex = 3;

            if(strlen($tier) >= 5)
                $loadIndex = 1;
            else 
                $loadIndex = 3;

            if($loadIndex >= $maxIndex)
                $loadIndex = $maxIndex;

            return $loadIndex;
        }
        catch(\Exception $e)
        {
            return 0;
        }
    }

    public static function checkTierAccess($tier)
    {
        //true if myself or downline
        try
        {   
            $userType = Auth::user()->type;

            //CA got full access
            if($userType == 'c')
                return true;

            $adminTier = self::getTierCodeWithoutSub();

            //not able view other tier
            if($tier == $adminTier)
                return true;

            return false;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public static function formatMoney($money)
    {
        $appCurrency = Session::get('app_currency');

        if(in_array($appCurrency,['USD']))
        {
            $decimal = 2;
        }
        else
        {
            $decimal = 0;
        }

        return number_format($money,$decimal);
    }

    public static function validAmount($money)
    {
        if(strlen($money) > 15)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    //store log to each log database
    public static function storeLogInDatabase($txnId, $type, $content, $header, $totalExecutionTime = false)
    {
        try
        {
            if (is_array($content)) 
            {
                $content = json_encode($content);
            }

            if (is_array($header)) 
            {
                $header = json_encode($header);
            }

            DB::insert("
                    INSERT INTO log_merchant
                    (txn_id,type,content,header,total_execution_time)
                    VALUES
                    (?,?,?,?,?)"
                    ,[  $txnId
                        ,$type
                        ,$content
                        ,$header
                        ,$totalExecutionTime
                    ]);
        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public static function getCurrencyTimezone()
    {
        $appCurrency = Session::get('app_currency');

        if(in_array($appCurrency,['KRW']))
        {
            $timezone = '9';
        }
        else if(in_array($appCurrency,['USD']))
        {
            $timezone = '-4';
        }
        else
        {
            $timezone = '0';
        }

        return $timezone;
    }
}
