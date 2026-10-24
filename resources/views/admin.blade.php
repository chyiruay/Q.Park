@extends('layoutadmin')

@section('content')
<div class="container admin-dash">
    <div class="admin-dash__head">
        <h1>Admin Dashboard</h1>
        <p>Welcome back, {{ Auth::user()->name }}. Pick a module to manage.</p>
    </div>

    <div class="row g-4">
        @foreach ([
            ['route' => 'viewUser', 'title' => 'Users', 'count' => $customers, 'unit' => 'customers', 'text' => 'Customer accounts and profiles'],
            ['route' => 'viewProduct', 'title' => 'Car Parks', 'count' => $products, 'unit' => 'listed', 'text' => 'Car park details, prices and floor plans'],
            ['route' => 'viewCategory', 'title' => 'Categories', 'count' => $categories, 'unit' => 'areas', 'text' => 'Area and type classification'],
            ['route' => 'allOrder', 'title' => 'Payments', 'count' => $orders, 'unit' => 'orders · RM ' . number_format($revenue, 2), 'text' => 'Paid orders and the sales report'],
            ['route' => 'viewFeedback', 'title' => 'Feedback', 'count' => $feedback, 'unit' => 'messages', 'text' => 'Customer suggestions and comments'],
        ] as $module)
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route($module['route']) }}" class="admin-card">
                    <span class="admin-card__title">{{ $module['title'] }}</span>
                    <span class="admin-card__count">{{ $module['count'] }}</span>
                    <span class="admin-card__unit">{{ $module['unit'] }}</span>
                    <span class="admin-card__text">{{ $module['text'] }}</span>
                </a>
            </div>
        @endforeach
    </div>
</div>
@endsection
