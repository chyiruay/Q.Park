@extends('layoutadmin')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Feedback</h1>
        <p>{{ $feedback->count() }} {{ Str::plural('message', $feedback->count()) }} received.</p>
    </div>

    @include('partials.alerts')
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Message</th>
                        <th scope="col">From User</th>
                        <th scope="col" class="text-end">Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feedback as $item)
                        <tr>
                            <td class="text-muted">{{ $item->id }}</td>
                            <td class="text-break">{{ $item->content }}</td>
                            <td>{{ $item->user->name ?? 'Unknown User' }}</td>
                            <td class="text-end text-muted text-nowrap">{{ $item->created_at?->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="admin-panel__empty">No feedback yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
