<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

use Auth;
use DB;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OddController;

class AppComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        
    }
}