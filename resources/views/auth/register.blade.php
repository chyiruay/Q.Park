@extends('layouts.app')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>Quick Park</h1>
            <p>{{ __('Create an account to start booking car park spaces.') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <div class="form-floating mb-3">
                <input id="name"
                       type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       placeholder="{{ __('Name') }}"
                       value="{{ old('name') }}"
                       required
                       autocomplete="name"
                       autofocus>
                <label for="name">{{ __('Name') }}</label>

                @error('name')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="email"
                       type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="name@example.com"
                       value="{{ old('email') }}"
                       required
                       autocomplete="email">
                <label for="email">{{ __('Email Address') }}</label>

                @error('email')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="phone_number"
                       type="tel"
                       name="phone_number"
                       class="form-control @error('phone_number') is-invalid @enderror"
                       placeholder="{{ __('Phone Number') }}"
                       value="{{ old('phone_number') }}"
                       required
                       autocomplete="tel">
                <label for="phone_number">{{ __('Phone Number') }}</label>

                @error('phone_number')
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
                       autocomplete="new-password">
                <label for="password">{{ __('Password') }}</label>

                @error('password')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="password-confirm"
                       type="password"
                       name="password_confirmation"
                       class="form-control"
                       placeholder="{{ __('Confirm Password') }}"
                       required
                       autocomplete="new-password">
                <label for="password-confirm">{{ __('Confirm Password') }}</label>
            </div>

            <button type="submit" class="btn btn-qpark w-100">
                {{ __('Register') }}
            </button>
        </form>

        <p class="auth-footer">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}">{{ __('Login') }}</a>
        </p>

    </div>
</div>
@endsection
