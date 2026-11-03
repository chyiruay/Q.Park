@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>{{ __('Reset Password') }}</h1>
            <p>{{ __('Enter the email address on your account and we will send you a link to set a new password.') }}</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="alert">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" novalidate>
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

            <button type="submit" class="btn btn-qpark w-100">
                {{ __('Send Password Reset Link') }}
            </button>
        </form>

        <p class="auth-footer">
            <a href="{{ route('login') }}">{{ __('Back to login') }}</a>
        </p>

    </div>
</div>
@endsection
