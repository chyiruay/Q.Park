@extends('layout')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>{{ __('Change Password') }}</h1>
            <p>{{ __('Use at least 8 characters for your new password.') }}</p>
        </div>

        {{-- field errors show under each input, so only the flash messages go here --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('updatePassword') }}" novalidate>
            @csrf

            <div class="form-floating mb-3">
                <input id="current-password"
                       type="password"
                       name="current-password"
                       class="form-control @error('current-password') is-invalid @enderror"
                       placeholder="{{ __('Current Password') }}"
                       required
                       autocomplete="current-password"
                       autofocus>
                <label for="current-password">{{ __('Current Password') }}</label>

                @error('current-password')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="new-password"
                       type="password"
                       name="new-password"
                       class="form-control @error('new-password') is-invalid @enderror"
                       placeholder="{{ __('New Password') }}"
                       required
                       minlength="8"
                       autocomplete="new-password">
                <label for="new-password">{{ __('New Password') }}</label>

                @error('new-password')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-4">
                <input id="new-password-confirm"
                       type="password"
                       name="new-password_confirmation"
                       class="form-control"
                       placeholder="{{ __('Confirm New Password') }}"
                       required
                       autocomplete="new-password">
                <label for="new-password-confirm">{{ __('Confirm New Password') }}</label>
            </div>

            <button type="submit" class="btn btn-qpark w-100 mb-2">{{ __('Change Password') }}</button>
            <a href="{{ route('viewProfile') }}" class="btn btn-outline-secondary w-100">{{ __('Cancel') }}</a>
        </form>

    </div>
</div>
@endsection
