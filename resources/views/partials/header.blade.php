{{--
    Shared site header.

    @include('partials.header')                              customer nav
    @include('partials.header', ['variant' => 'admin'])      admin nav
    @include('partials.header', ['showSearch' => true])      adds the search box

    Replaces five near-duplicate <nav> blocks. The old markup put the profile
    dropdown outside the .collapse wrapper (and outside any <ul>), so it never
    collapsed on mobile.
--}}
@php
    $variant = $variant ?? 'customer';
    $showSearch = $showSearch ?? false;
@endphp

<nav class="navbar navbar-expand-lg navbar-dark bg-dark site-header">
    <div class="container">

        <a class="navbar-brand site-header__brand"
           href="{{ $variant === 'admin' ? route('admin') : url('/') }}">
            Q . Park
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#siteNav"
                aria-controls="siteNav"
                aria-expanded="false"
                aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="siteNav">

            <ul class="navbar-nav ms-auto align-items-lg-center">

                @if ($variant === 'admin')
                    <li class="nav-item"><a class="nav-link" href="{{ route('viewUser') }}">User</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('viewProduct') }}">Parking</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('viewCategory') }}">Category</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('allOrder') }}">Payment</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('viewFeedback') }}">Feedback</a></li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('Product') }}">Car Parks</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('Help') }}">Help</a></li>
                @endif

                @if ($showSearch)
                    <li class="nav-item ms-lg-3 my-2 my-lg-0">
                        <form class="site-header__search" action="{{ route('search.product') }}" method="POST" role="search">
                            @csrf
                            <label for="navKeyword" class="visually-hidden">Search car parks</label>
                            <input id="navKeyword" class="form-control form-control-sm"
                                   name="keyword" type="search" placeholder="Search">
                            <button class="btn btn-sm btn-success" type="submit">Search</button>
                        </form>
                    </li>
                @endif

                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a></li>
                @else
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle" href="#" id="siteNavUser"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="siteNavUser">
                            @if (Auth::user()->isAdmin)
                                <li><a class="dropdown-item" href="{{ route('admin') }}">Admin</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('viewProfile') }}">My Profile</a></li>
                            @unless (Auth::user()->isAdmin)
                                <li><a class="dropdown-item" href="{{ route('myCart') }}">My Cart</a></li>
                                <li><a class="dropdown-item" href="{{ route('myOrder') }}">Payment History</a></li>
                                <li><a class="dropdown-item" href="{{ route('insertFeedback') }}">Feedback</a></li>
                            @endunless
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('site-logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endguest

            </ul>
        </div>
    </div>
</nav>

@auth
    <form id="site-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
@endauth
