{{-- Car park card grid. Expects $products. --}}
<div class="container py-5">
    @forelse ($products as $product)
        @if ($loop->first)<div class="row g-4">@endif

        <div class="col-12 col-sm-6 col-lg-4">
            <a class="product-card" href="{{ route('Order', ['id' => $product->id]) }}">
                <div class="product-card__media">
                    <img src="{{ asset('images') }}/{{ $product->image }}"
                         alt="{{ $product->name }}"
                         loading="lazy">
                </div>
                <div class="product-card__body">
                    <h2 class="product-card__title">{{ $product->name }}</h2>
                    <p class="product-card__price">RM {{ number_format($product->price, 2) }} <span>/ hour</span></p>
                </div>
            </a>
        </div>

        @if ($loop->last)</div>@endif
    @empty
        <p class="product-empty">No car parks found. Try a different search.</p>
    @endforelse
</div>
