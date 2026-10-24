@extends('layoutadmin')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Payments</h1>
        <p>{{ $orders->total() }} {{ Str::plural('order', $orders->total()) }} · RM {{ number_format($revenue, 2) }} total.</p>
    </div>

    @include('partials.alerts')
    <div class="text-end mb-3">
        <a href="{{ route('salesReport') }}" class="btn btn-qpark">Download Sales Report (PDF)</a>
    </div>
    <div class="admin-panel">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Payment ID</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Date &amp; Time</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="text-muted">#{{ $order->id }}</td>
                                <td>
                                    @if ($order->name)
                                        <div class="fw-semibold">{{ $order->name }}</div>
                                        <div class="small text-muted">{{ $order->email }}</div>
                                    @else
                                        <span class="text-muted">Deleted user #{{ $order->userID }}</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d M Y, h:i A') }}</td>
                                <td><span class="badge admin-badge--customer">{{ ucfirst($order->paymentStatus) }}</span></td>
                                <td class="text-end fw-semibold text-nowrap">RM {{ number_format($order->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="admin-panel__empty">No payments yet.</td></tr>
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
