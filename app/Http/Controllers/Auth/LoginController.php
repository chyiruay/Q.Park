<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /** Same form, posting to /admin/login instead of /login. */
    public function showAdminLoginForm()
    {
        return view('auth.login', ['adminLogin' => true]);
    }

    /**
     * Each form only matches accounts of its own kind. `isAdmin` is a real column,
     * so the provider turns it into a where clause on the lookup — an admin at
     * /login simply isn't found, and gets the usual "credentials do not match".
     */
    protected function credentials(Request $request)
    {
        return array_merge(
            $request->only($this->username(), 'password'),
            ['isAdmin' => $request->is('admin/login')]
        );
    }

    /** Overrides the $redirectTo property: where you land depends on your role. */
    protected function redirectTo()
    {
        return Auth::user()->isAdmin ? route('admin') : RouteServiceProvider::HOME;
    }
}
