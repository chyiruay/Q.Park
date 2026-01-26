@extends('layoutadmin')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 text-white font-weight-bold" style="text-shadow: 2px 4px 10px rgba(0,0,0,0.3);">
                Admin Dashboard
            </h1>
            <p class="lead text-light">Welcome back. Please select the module you wish to manage.</p>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <a href="{{ route('viewUser') }}" class="admin-btn">
                <div class="btn-content">
                    <strong>User</strong>
                    <small>User permissions and profile management</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <a href="{{ route('viewProduct') }}" class="admin-btn">
                <div class="btn-content">
                    <strong>Parking</strong>
                    <small>Parking space status and information settings</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <a href="{{ route('viewCategory') }}" class="admin-btn">
                <div class="btn-content">
                    <strong>Category</strong>
                    <small>Area and type classification</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6 mb-4 offset-md-2">
            <a href="{{ route('allOrder') }}" class="admin-btn">
                <div class="btn-content">
                    <strong>Payment</strong>
                    <small>Bill flow and payment details</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <a href="{{ route('viewFeedback') }}" class="admin-btn">
                <div class="btn-content">
                    <strong>Feedback</strong>
                    <small>View customer suggestions and feedback</small>
                </div>
            </a>
        </div>
    </div>
</div>

<style>

</style>
@endsection