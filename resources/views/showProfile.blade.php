@extends('layout')

@section('content')
<div class="auth-wrapper">
    <div class="auth-card">

        <div class="auth-brand">
            <div class="profile-avatar" aria-hidden="true">{{ Str::upper(Str::substr($user->name, 0, 1)) }}</div>
            <h1>{{ $user->name }}</h1>
            <p>
                <span class="badge {{ $user->isAdmin ? 'bg-dark' : 'admin-badge--customer' }}">
                    {{ $user->isAdmin ? 'Admin' : 'Customer' }}
                </span>
            </p>
        </div>

        @include('partials.alerts')

        <dl class="profile-details">
            <dt>Email Address</dt>
            <dd>{{ $user->email }}</dd>

            <dt>Phone Number</dt>
            <dd>{{ $user->phone_number }}</dd>

            <dt>Member Since</dt>
            <dd>{{ $user->created_at?->format('d M Y') ?? '-' }}</dd>
        </dl>

        <a href="{{ route('editProfile', ['id' => $user->id]) }}" class="btn btn-qpark w-100 mb-2">{{ __('Edit Profile') }}</a>
        <a href="{{ route('editPassword', ['id' => $user->id]) }}" class="btn btn-outline-secondary w-100">{{ __('Change Password') }}</a>

        <p class="auth-footer">
            <button type="button" class="btn btn-link p-0 text-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#delete-account">
                {{ __('Delete account') }}
            </button>
        </p>

    </div>
</div>

<div class="modal fade" id="delete-account" tabindex="-1" aria-labelledby="delete-account-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('deleteUser') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h2 class="modal-title fs-5 text-danger" id="delete-account-title">{{ __('Delete your account?') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('This permanently deletes the account for') }} <strong>{{ $user->email }}</strong>. {{ __('It cannot be undone.') }}</p>
                <p class="mb-0 text-muted small">{{ __('You will be signed out straight away.') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-danger">{{ __('Delete Account') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
