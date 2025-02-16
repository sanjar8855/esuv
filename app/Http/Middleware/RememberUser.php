<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RememberUser
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() && $request->hasCookie('remember_web')) {
            Auth::guard('web')->viaRemember();
        }

        return $next($request);
    }
}
