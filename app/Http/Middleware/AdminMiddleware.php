<?php

namespace App\Http\Middleware;

use App\Models\AdminMenuOptions;
use App\Http\Controllers\AdminControllers\HomeController;

use Closure;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

class AdminMiddleware
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
        if (auth()->user()) {
            $user = HomeController::getUserData();

            if (!empty($user->access)) {
                $sidebarMenuItems = AdminMenuOptions::get();

                $availableAccess = explode(',', $user->access);
                $firstUrl = "";

                foreach ($sidebarMenuItems as $menu) {
                    if (empty($firstUrl)) {
                        if (in_array($menu->id, $availableAccess)) {
                            $firstUrl = $menu->url;
                        }
                    }

                    if (request()->is($menu->url)) {
                        if (!in_array($menu->id, $availableAccess)) {
                            return redirect('/login');
                        }
                    } else {
                        if (str_contains($menu->url, '{')) {
                            $currentPath = explode('/', parse_url(request()->url(), PHP_URL_PATH));
                            unset($currentPath[count($currentPath) - 1]);
                            $currentPath = implode('/', $currentPath);

                            $dbPath = explode('/', $menu->url);
                            unset($dbPath[count($dbPath) - 1]);
                            $dbPath = '/' . implode('/', $dbPath);

                            if ($currentPath == $dbPath) {
                                if (!in_array($menu->id, $availableAccess)) {
                                    return redirect('/login');
                                }
                            }
                        }
                    }
                }

                if (request()->is('home')) {
                    return redirect($firstUrl);
                }
            } else {
                if (request()->is('home')) {
                    return redirect('orderentry/index');
                }
            }

            return $next($request);
        } else {
            return redirect('/');
        }
    }
}
