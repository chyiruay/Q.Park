<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Gate for the admin area. Routes pair this with 'auth', which redirects
     * guests first, so reaching here without the flag means "signed in, not admin".
     */
    public function handle(Request $request, Closure $next)
    {
        abort_unless(Auth::user()?->isAdmin, 403);

        return $next($request);
    }
}
