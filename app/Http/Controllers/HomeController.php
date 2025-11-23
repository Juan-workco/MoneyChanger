<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Log;

class HomeController extends Controller
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
    public static function getTotalTurnover()
    {
        try 
        {
            $mercType = AUTH::user()->type;
            $mercId = AUTH::user()->merc_id;

            if($mercType == 'c')
            {
                $mercId= '';         
            }

            $db = DB::select("SELECT SUM(a.amount) AS total_turnover
                            FROM debit a
                            LEFT JOIN member b 
                                ON b.id = a.member_id
                            INNER JOIN credit c 
                                ON c.txn_id = a.txn_id
                            WHERE a.status = ?
                                AND (b.merc_id = ? OR '' = ?)"
                            ,['a',$mercId,$mercId]);    
            

            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->total_turnover;
            }
            
        } 
        catch (Exception $e)
        {
            return 0;
        }
    }

    public static function getTotalWinLoss()
    {
        try 
        {
            $mercType = AUTH::user()->type;
            $mercId = AUTH::user()->merc_id;

            if($mercType == 'c')
            {
                $mercId= '';         
            }

            $db = DB::select("SELECT SUM(b.amount - a.amount) AS total_winloss
                                FROM debit a
                                INNER JOIN credit b 
                                    ON a.txn_id = b.txn_id
                                LEFT JOIN member c
                                    ON c.id = a.member_id
                                WHERE a.status = ?
                                    AND (c.merc_id = ? OR '' = ?)"
                                ,['a',$mercId,$mercId]);  

            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->total_winloss;
            }
            
        } 
        catch (Exception $e)
        {
            return 0;
        }
    }

    public static function getTotalMemberBet()
    {
        try 
        {
            $mercType = AUTH::user()->type;
            $mercId = AUTH::user()->merc_id;

            if($mercType == 'c')
            {
                $mercId= '';         
            }

            $db = DB::select("SELECT COUNT(a.member_id) 'total_member_bet'
                                FROM 
                                    (SELECT DISTINCT COUNT(a.member_id) 'total_bet', a.member_id
                                FROM debit a
                                LEFT JOIN member b
                                    ON a.member_id = b.id
                                WHERE (b.merc_id = ? 
                                    OR '' = ?)
                                GROUP BY a.member_id) a"
                                ,[$mercId,$mercId]); 

            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->total_member_bet;
            }
            
        } 
        catch (Exception $e)
        {
            return 0;
        }
    }

    public static function getTotalMemberOnline()
    {
        try 
        {
            $mercType = AUTH::user()->type;
            $mercId = AUTH::user()->merc_id;

            if($mercType == 'c')
            {
                $db = DB::select("SELECT COUNT(member_id) AS total_member_online
                                FROM member_online
                                WHERE updated_at <= NOW() 
                                    AND updated_at > (NOW() - INTERVAL 5 MINUTE)");
            }
            else
            {
                $db = DB::select("SELECT COUNT(a.member_id) AS total_member_online
                                FROM member_online a 
                                LEFT JOIN member c
                                    ON c.id = a.member_id
                                WHERE a.updated_at <= NOW() 
                                    AND a.updated_at > (NOW() - INTERVAL 5 MINUTE)
                                    AND c.merc_id = ?"
                                ,[$mercId]);
            }


            if(sizeof($db)==0)
            {
                return 0;
            }  
            else
            {
                return $db[0]->total_member_online;
            }               

        } 
        catch (Exception $e) 
        {
            return 0;
        }
    }
}
