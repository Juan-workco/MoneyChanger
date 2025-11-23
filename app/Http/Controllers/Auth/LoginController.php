<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Session;
use Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    // protected $redirectToWinloss = '/reports/kironwinloss';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    protected function credentials(Request $request)
    {
        return ['username'=>$request->{$this->username()},'password'=>$request->password,'status'=>'a'];
    }

    protected function authenticated(Request $request, $user)
    {
        $userId = $user->id;
        $loginToken = \Session::getId();
        $type = $user->type;

        \Session::put('login_token',$loginToken);
        self::setAppCurrency($user);

        DB::UPDATE("
            UPDATE admin
            SET login_token = ?
            WHERE id = ?
            ", [
                 $loginToken
                ,$userId
            ]);

        // if($type == 'k')
        // {            
        //     return redirect($this->redirectToWinloss);  
        // }
        // else
        // {

        //    return redirect($this->redirectTo);
        // }

        return redirect($this->redirectTo);

        
    }

    protected function setAppCurrency($user) 
    {
        $userId = $user->merc_id;
        $type = $user->type;

        if($type == 'm')
        {
            $db = DB::select('SELECT currency_cd 
                        FROM admin_currency 
                        WHERE merc_id = :id', 
                        ['id' => $userId]);

            Session::put('app_currency', $db[0]->currency_cd);
        }
        else
        {
            Session::put('app_currency', 'KRW');

        }
    }


    public function logout(Request $request) 
    {
        $user_id = Auth::id();
        
        Auth::logout();

        DB::UPDATE("
            UPDATE admin
            set login_token = NULL
            WHERE id = ?
        ", [
            $user_id
        ]);

        return redirect('/');
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        //prepare redirect path
        $redirectToLogin = '/login';

        $username = $request->input('username');

        //check not exists
        $data = DB::SELECT("
                SELECT status
                FROM admin
                where username = ?
            ", [
                $username
            ]);

        //check is closed      
        if($data)
        {
            if ($data[0]->status == "i") 
            {
                return redirect()->to($redirectToLogin)
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => __('auth.inactive'),
                ]); 
            }
            else
            {
                return redirect()->to($redirectToLogin)
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => __('auth.failed'),
                ]);
            }
            
        }
        else
        {
            return redirect()->to($redirectToLogin)
            ->withInput($request->only($this->username()))
            ->withErrors([
                $this->username() => __('auth.failed'),
            ]);
        }
    }

}
