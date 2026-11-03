@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>{{ __('Choose a New Password') }}</h1>
            <p>{{ __('This link works once. Pick a password of at least eight characters.') }}</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-floating mb-3">
                <input id="email"
                       type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="name@example.com"
                       value="{{ $email ?? old('email') }}"
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
                       placeholder="{{ __('New Password') }}"
                       required
                       autocomplete="new-password">
                <label for="password">{{ __('New Password') }}</label>

                @error('password')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="password-confirm"
                       type="password"
                       name="password_confirmation"
                       class="form-control"
                       placeholder="{{ __('Confirm New Password') }}"
                       required
                       autocomplete="new-password">
                <label for="password-confirm">{{ __('Confirm New Password') }}</label>
            </div>

            <button type="submit" class="btn btn-qpark w-100">
                {{ __('Reset Password') }}
            </button>
        </form>

        <p class="auth-footer">
            <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
        </p>

    </div>
</div>
@endsection
