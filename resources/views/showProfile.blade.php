@extends('layout')
@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="card" style=" margin-bottom:50px; background-color:lightgray;">
            <div class="card-header" style="font-size:40px;">
                Account Information
            </div>
            @foreach($users as $users)
            <div class="card-body">
                <strong>ID : {{$users->id}}<br>
                Name : {{$users->name}}<br>
                Email : {{$users->email}}<br>
                Phone Number : {{$users->phone_number}}</strong><br>
                <div style="text-align:right;">
                    <a href="{{route('deleteAccount')}}" class="delete" title="Delete" data-bs-toggle="tooltip"><i class="material-icons"><button 
                    style="background-color:red; color:white; border:1px solid red; border-radius: 20px; width:75px; height:35px; font-size:18px;">Delete</button></i></a>
                    <a href="{{route('editPassword',['id'=>$users->id])}}" class="changepassword" title="Edit" data-bs-toggle="tooltip"><i class="material-icons"><button style="border-radius:20px;">Change Password</button></i></a>
                    <a href="{{route('editProfile',['id'=>$users->id])}}" class="edit" title="Edit" data-bs-toggle="tooltip"><i class="material-icons"><button style="border-radius:20px;">Edit</button></i></a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-sm-1" ></div> 
</div>

@endsection