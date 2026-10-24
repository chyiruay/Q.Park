@extends('layout')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>My Orders</h1>
        <p>{{ $orders->total() }} {{ Str::plural('payment', $orders->total()) }} made.</p>
    </div>

    @include('partials.alerts')
    <div class="admin-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Payment ID</th>
                        <th scope="col">Bookings</th>
                        <th scope="col">Paid On</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td class="text-muted">#{{ $order->id }}</td>
                            <td>
                                @forelse ($bookings->get($order->id, []) as $booking)
                                    <div class="{{ $loop->last ? '' : 'mb-2' }}">
                                        <span class="fw-semibold">{{ $booking->name ?? 'Removed car park' }}</span>
                                        <span class="badge admin-badge--customer ms-1">{{ $booking->space ?? '-' }}</span>
                                        <div class="small text-muted">
                                            {{ $booking->date ? \Carbon\Carbon::parse($booking->date)->format('d M Y') : '' }}
                                            {{ $booking->startTime ? \Carbon\Carbon::parse($booking->startTime)->format('h:i A') : '' }}
                                            · {{ $booking->quantity }} {{ Str::plural('hour', $booking->quantity) }}
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</td>
                            <td><span class="badge admin-badge--customer">{{ ucfirst($order->paymentStatus) }}</span></td>
                            <td class="text-end fw-semibold text-nowrap">RM {{ number_format($order->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-panel__empty">
                                No payments yet. Bookings you pay for will appear here.
                                <a href="{{ route('Product') }}" class="d-block mt-2">Browse car parks</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
