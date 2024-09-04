<?php

namespace App\Http\Middleware;

use App\Models\AdminMenuOptions;
use App\Http\Controllers\AdminControllers\HomeController;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Session;

class ReferralMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Session::has('ref_user')) {
            return $next($request);
        } else {
            return redirect('referralpanel/login');
        }
    }
}
