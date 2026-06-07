<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('user.login');
        }

        $user = Auth::user();

        if ($user->type === 'user') {
            return $next($request);
        }
        if ($user->type === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.login');
    }
}
