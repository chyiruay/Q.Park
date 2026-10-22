@extends('layouts.app')

@php($adminLogin = $adminLogin ?? false)

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>Quick Park{{ $adminLogin ? ' Admin' : '' }}</h1>
            <p>
                @if ($adminLogin)
                    {{ __('Staff sign-in. Customer accounts cannot be used here.') }}
                @else
                    {{ __('Reserve your car park ahead of time and skip the hunt for a space.') }}
                @endif
            </p>
        </div>

        <form method="POST" action="{{ $adminLogin ? route('admin.login') : route('login') }}" novalidate>
            @csrf

            <div class="form-floating mb-3">
                <input id="email"
                       type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="name@example.com"
                       value="{{ old('email') }}"
                       required
                       autocomplete="email"
                       autofocus>
                <label for="email">{{ __('Email Address') }}</label>

                @error('email')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="password"
                       type="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="{{ __('Password') }}"
                       required
                       autocomplete="current-password">
                <label for="password">{{ __('Password') }}</label>

                @error('password')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-options">
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="remember"
                           id="remember"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                </div>

                @if (Route::has('password.request'))
                    <a class="auth-link" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-qpark w-100">
                {{ __('Login') }}
            </button>
        </form>

        @if ($adminLogin)
            <p class="auth-footer">
                <a href="{{ route('login') }}">{{ __('Back to customer login') }}</a>
            </p>
        @else
            <p class="auth-footer">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}">{{ __('Register') }}</a>
            </p>

            <p class="auth-footer">
                <a href="{{ route('admin.login') }}">{{ __('Login as admin') }}</a>
            </p>
        @endif

    </div>
</div>
@endsection
