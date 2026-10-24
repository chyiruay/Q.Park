@extends('layout')

@section('content')
@foreach ($products as $product)
<div class="auth-wrapper">
    <div class="auth-card booking-card">

        <div class="auth-brand">
            <h1>{{ $product->name }}</h1>
            <p><strong>RM {{ number_format($product->price, 2) }}</strong> per hour</p>
        </div>

        <div class="booking-photos">
            <figure>
                <img src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}">
                <figcaption>{{ $product->name }}</figcaption>
            </figure>
            <figure>
                <a href="{{ asset('floorPlan/' . $product->floorPlan) }}" target="_blank" rel="noopener" title="Open full-size floor plan">
                    <img src="{{ asset('floorPlan/' . $product->floorPlan) }}" alt="{{ $product->name }} floor plan">
                </a>
                <figcaption>Floor plan (tap to enlarge)</figcaption>
            </figure>
        </div>

        @include('partials.alerts')

        <form method="POST" action="{{ route('add.to.cart') }}" novalidate>
            @csrf
            <input type="hidden" name="id" value="{{ $product->id }}">

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="form-floating">
                        <input id="date" type="date" name="date"
                               class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date') }}" min="{{ date('Y-m-d') }}" required>
                        <label for="date">{{ __('Date') }}</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-floating">
                        <input id="time" type="time" name="time"
                               class="form-control @error('time') is-invalid @enderror"
                               value="{{ old('time') }}" required>
                        <label for="time">{{ __('Start Time') }}</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-floating">
                        <input id="quantity" type="number" name="quantity"
                               class="form-control @error('quantity') is-invalid @enderror"
                               value="{{ old('quantity', 1) }}" min="1" max="24" required>
                        <label for="quantity">{{ __('Hours') }}</label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-floating">
                        <select id="space" name="space" class="form-select @error('space') is-invalid @enderror" required>
                            <option value="">{{ __('Select') }}</option>
                            @foreach (\App\Models\Product::spaces() as $space)
                                <option @selected(old('space') === $space)>{{ $space }}</option>
                            @endforeach
                        </select>
                        <label for="space">{{ __('Parking Space') }}</label>
                    </div>
                </div>
            </div>

            {{-- display only; the cart and checkout price it server-side --}}
            <div class="booking-total">
                <span>{{ __('Estimated total') }}</span>
                <strong id="booking-total" data-price="{{ $product->price }}">RM {{ number_format($product->price * (int) old('quantity', 1), 2) }}</strong>
            </div>

            <button type="submit" class="btn btn-qpark w-100">{{ __('Add to Cart') }}</button>
        </form>

    </div>
</div>
@endforeach

<script>
    document.getElementById('quantity').addEventListener('input', function () {
        var total = document.getElementById('booking-total');
        var hours = Math.max(0, parseInt(this.value, 10) || 0);
        total.textContent = 'RM ' + (total.dataset.price * hours).toFixed(2);
    });
</script>
@endsection
