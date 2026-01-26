@extends('layoutadmin')
@section('content')
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="card" style="background-color:lightgray; margin-bottom:5%;">
        <div class="card-header" style="background-color:lightgray; font-size:40px;">Update Category</div>
        <div class="card-body">
        <form method="POST" action="{{ route('updateCategory') }}">
            @CSRF

            @foreach($categories as $category)
            <input type="hidden" class="form-control" id="hid" name="hid" value="{{$category->id}}">
            <div class="form-group">
                <label for="Category Name">Name</label>
                <input type="text" class="form-control" id="categoryName" name="categoryName" value="{{$category->name}}">
            </div>

            @endforeach
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
        <br></br>
</div>
</div>
    </div>
    <div class="col-sm-3"></div>
</div>

@endsection