@extends('layout')
@section('content')
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="card" style="background-color:lightgray; margin-bottom:50px;">
            <div class="card-header" style="background-color:lightgray; font-size:40px;">Edit Profile</div>
            <div class="card-body">
                @include('partials.alerts')
                <form method="POST" action="{{ route('updateProfile') }}">
                    @CSRF
                    @foreach($users as $users)
                    <div class="mb-3">
                        <label for="User Name">Name</label>
                        <input type="text" class="form-control" id="userName" name="userName" value="{{ old('userName', $users->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="User Email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $users->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="User PhoneNumber">Phone Number</label>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $users->phone_number) }}" required>
                    </div>
                    @endforeach
                    <div style="text-align:right;">
                        <button type="submit" class="btn btn-primary" style="border:1px solid green; border-radius:20px; background-color:green;">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-3"></div>
</div>
@endsection