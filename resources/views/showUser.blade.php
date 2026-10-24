@extends('layoutadmin')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Users</h1>
        <p>{{ $users->count() }} {{ Str::plural('account', $users->count()) }} registered.</p>
    </div>

    @include('partials.alerts')

    <div class="admin-panel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone Number</th>
                            <th scope="col">Role</th>
                            <th scope="col">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="text-muted">{{ $user->id }}</td>
                                <td class="fw-semibold">{{ $user->name }}</td>
                                <td><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                                <td>{{ $user->phone_number }}</td>
                                <td>
                                    @if ($user->isAdmin)
                                        <span class="badge bg-dark">Admin</span>
                                    @else
                                        <span class="badge admin-badge--customer">Customer</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $user->created_at?->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="admin-panel__empty">No users yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
</div>
@endsection
