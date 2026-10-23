<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsCustomer
{
    /**
     * Mirror of IsAdmin: keeps admins out of the shop. Guests and customers fall
     * through untouched, so this is safe on public pages as well as gated ones.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()?->isAdmin) {
            return redirect()->route('admin');
        }

        return $next($request);
    }
}
