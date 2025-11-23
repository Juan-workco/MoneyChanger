<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Jobs\ProcessCredit;
use App\Jobs\ProcessSBReCredit;
use App\Jobs\ProcessLiveSBReCredit;
// use App\Jobs\ProcessRacingResults;
// use App\Jobs\ProcessFootballFastResults;
// use App\Jobs\ProcessFootballLeagueResults;
// use App\Jobs\ProcessRacketResults;
// use App\Jobs\ProcessSBCredit;
// use App\Jobs\ProcessItalianFootballFastResults;
// use App\Jobs\ProcessSpanishFootballFastResults;
// use App\Jobs\ProcessArcheryResults;

use Log;
use DB;
use Carbon\Carbon;

class JobController extends Controller
{
   /**
     * Handle Queue Process
     */

    // //this queue will run at API component
    // public static function processSBCredit($userId,$txnId,$amount,$type = 'c',$prdId)
    // {
    //     static $sbqueue = 1;
    //     $queueNum = env('CREDIT_QUEUE_NUMBER');

    //     dispatch(new ProcessSBCredit($userId,$txnId,$amount,$type,$prdId))->allonQueue('Credit_SB_Queue_'.$sbqueue);

    //     if ($sbqueue % $queueNum == 0) 
    //     {
    //         $sbqueue = 1;
    //     }
    //     else
    //     {
    //         $sbqueue++;
    //     }

    //     // dispatch(new ProcessRacingResults($eventId,$endTime));
    // }

    public static function processSBRefundCredit($userId,$txnId,$amount,$type = 'c',$prdId,$now,$id)
    {
        static $sbrequeue = 1;
        $queueNum = env('CREDIT_QUEUE_NUMBER');

        dispatch(new ProcessSBReCredit($userId,$txnId,$amount,$type,$prdId,$now,$id))->allonQueue('Credit_Resettlement_SB_Queue_'.$sbrequeue);

        if ($sbrequeue % $queueNum == 0) 
        {
            $sbrequeue = 1;
        }
        else
        {
            $sbrequeue++;
        }
    }

    public static function processLiveSBRefundCredit($userId,$txnId,$amount,$type = 'c',$prdId,$now,$id)
    {
        static $sbrequeue = 1;
        $queueNum = env('CREDIT_QUEUE_NUMBER');

        dispatch(new ProcessLiveSBReCredit($userId,$txnId,$amount,$type,$prdId,$now,$id))->allonQueue('Credit_Resettlement_SB_Live_Queue_'.$sbrequeue);

        if ($sbrequeue % $queueNum == 0) 
        {
            $sbrequeue = 1;
        }
        else
        {
            $sbrequeue++;
        }
    }
}


