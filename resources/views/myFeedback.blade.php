@extends('layout')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>My Feedback</h1>
        <p>{{ $feedback->total() }} {{ Str::plural('message', $feedback->total()) }} sent.</p>
    </div>

    @include('partials.alerts')
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Message</th>
                        <th scope="col" class="text-end">Sent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($feedback as $item)
                        <tr>
                            <td class="text-break">{{ $item->content }}</td>
                            <td class="text-end text-muted text-nowrap">{{ $item->created_at?->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="admin-panel__empty">
                                You haven't sent any feedback yet.
                                <a href="{{ route('insertFeedback') }}" class="d-block mt-2">Send feedback</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $feedback->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
