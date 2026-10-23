<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * The flash explains why: clicking a car park used to drop guests on the login
     * page with no indication of what happened. Laravel stores the intended URL, so
     * logging in returns them to the car park they clicked.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        $request->session()->flash('notice', 'Please log in first — you need an account to book a parking space.');

        return route('login');
    }
}
