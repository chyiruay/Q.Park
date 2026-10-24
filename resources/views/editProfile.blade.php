@extends('layout')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <h1>{{ __('Edit Profile') }}</h1>
            <p>{{ __('Update your name and contact details.') }}</p>
        </div>

        <form method="POST" action="{{ route('updateProfile') }}" novalidate>
            @csrf

            <div class="form-floating mb-3">
                <input id="userName"
                       type="text"
                       name="userName"
                       class="form-control @error('userName') is-invalid @enderror"
                       placeholder="{{ __('Name') }}"
                       value="{{ old('userName', $user->name) }}"
                       required
                       autocomplete="name"
                       autofocus>
                <label for="userName">{{ __('Name') }}</label>

                @error('userName')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input id="email"
                       type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="name@example.com"
                       value="{{ old('email', $user->email) }}"
                       required
                       autocomplete="email">
                <label for="email">{{ __('Email Address') }}</label>

                @error('email')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-floating mb-4">
                <input id="phone_number"
                       type="tel"
                       name="phone_number"
                       class="form-control @error('phone_number') is-invalid @enderror"
                       placeholder="{{ __('Phone Number') }}"
                       value="{{ old('phone_number', $user->phone_number) }}"
                       required
                       autocomplete="tel">
                <label for="phone_number">{{ __('Phone Number') }}</label>

                @error('phone_number')
                    <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-qpark w-100 mb-2">{{ __('Save Changes') }}</button>
            <a href="{{ route('viewProfile') }}" class="btn btn-outline-secondary w-100">{{ __('Cancel') }}</a>
        </form>

    </div>
</div>
@endsection
