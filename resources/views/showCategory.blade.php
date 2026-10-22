@extends('layoutadmin')
@section('content')
@include('partials.alerts')
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="table-wrapper">
            <div class="table-title">
                <div class="row">
                    <div class="col-sm-8" style="color:white;"><h2>Category <b>List</b></h2></div>
                    <div class="col-sm-4 text-end">
                        <a href="{{ route('insertCategory') }}" class="btn btn-add-new">
                            <i class="fas fa-plus-circle me-2"></i>Add New Category
                        </a>
                    </div>
                </div>
            </div>
            <table class="table table-bordered" style="background-color:lightgray;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>                       
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>{{$category->id}}</td>
                        <td>{{$category->name}}</td>
                        <td>
                            <a href="{{route('editCategory',['id'=>$category->id])}}" class="edit" title="Edit" data-bs-toggle="tooltip"><i class="material-icons">Edit</i></a><br>
                            <form method="POST" action="{{route('deleteCategory',['id'=>$category->id])}}" onsubmit="return confirm('Sure Want Delete?')">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 delete" style="color:red">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-sm-3"></div>
</div>

@endsection