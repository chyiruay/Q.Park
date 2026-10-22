@extends('layouthome')

@section('content')
<section class="home-hero">
    <div class="home-hero__inner">

        <h1 class="home-hero__title">Find and Reserve Parking in Seconds.</h1>

        <p class="home-hero__subtitle">
            Book a space at malls and hotels across Johor Bahru before you arrive,
            and skip the hunt for a spot.
        </p>

        <div class="home-actions">
            <a href="{{ route('Product') }}" class="btn btn-qpark-outline">
                View Carpark
            </a>

            <form class="home-actions__search" action="{{ route('search.product') }}" method="POST" role="search">
                @csrf
                <label for="keyword" class="visually-hidden">Search car parks</label>
                <input id="keyword"
                       class="form-control"
                       name="keyword"
                       type="search"
                       placeholder="Search by name or category">
                <button class="btn btn-search" type="submit">Search</button>
            </form>
        </div>

    </div>
</section>
@endsection
