@extends('layoutadmin')
@section('content')

@if(Session::has('success'))
    <div class="alert alert-success" role="alert">
        {{Session::get('success')}}
    </div>
@endif
<div class="row">
    <div class="col-sm-1"></div>
    <div class="col-sm-10">
        <br></br>
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-8" style="color:white;"><h2>User <b>List</b></h2></div>
                </div>
            </div>
            <table class="table table-bordered" style="background-color:lightgray;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>    
                        <th>Phone Number</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user as $user)
                    <tr>
                        <td>{{$user->id}}</td>
                        <td>{{$user->name}}</td>
                        <td>{{$user->email}}</td>
                        <td>{{$user->phone_number}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
</div>
</div>
    </div>
    <div class="col-sm-1"></div>
</div>


@endsection