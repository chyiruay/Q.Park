{{--
    Search + category filter band for the car park listings.
    Shared by products.blade.php and productDetail.blade.php, which render the
    same chrome (the latter is the category-filtered view).
--}}
<section class="product-hero">
    <div class="container">

        <h1 class="product-hero__title">Looking for a parking?</h1>

        <form class="product-search" action="{{ route('search.product') }}" method="POST" role="search">
            @csrf
            <label for="productKeyword" class="visually-hidden">Search car parks by name or category</label>
            <input id="productKeyword"
                   class="form-control"
                   name="keyword"
                   type="search"
                   value="{{ request('keyword') }}"
                   placeholder="Search by name or category">
            <button class="btn btn-search" type="submit">Search</button>
        </form>

        <nav class="product-filters" aria-label="Filter by category">
            <a href="{{ route('Product') }}" class="product-filters__item">All</a>
            <a href="{{ route('viewDetail', 1) }}" class="product-filters__item">Hotel</a>
            <a href="{{ route('viewDetail', 2) }}" class="product-filters__item">Mall</a>
        </nav>

    </div>
</section>
