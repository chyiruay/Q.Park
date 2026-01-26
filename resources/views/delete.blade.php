@extends('layout')
@section('content')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="card" style=" margin-bottom:50px; background-color:red;">
            <div class="card-header" style="font-size:40px; color:white;">
                Delete Account
            </div>
            @foreach($users as $users)
            <div class="card-body" style="color:white;">
                <div style="text-align:center">
                    <div style="font-size:20px;">
                        <strong>Are you sure you want to delete your account?</strong><br>
                    </div>
                    <strong><i>**The account will be permanently deleted.**</i></strong><p>
                </div>
                <strong>ID : {{$users->id}}<br>
                Name : {{$users->name}}<br>
                Email : {{$users->email}}<br>
                Phone Number : {{$users->phone_number}}</strong><br>
                <div style="text-align:right;">
                    <a href="{{route('deleteUser',['id'=>$users->id])}}" class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons"><button 
                    style="background-color:white; color:red; border:1px solid red; border-radius: 20px; width:75px; height:35px; font-size:18px;">Delete</button></i></a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div class="col-sm-1" ></div> 
</div>


@endsection
