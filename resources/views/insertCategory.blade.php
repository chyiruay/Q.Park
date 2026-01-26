@extends('layoutadmin')
@section('content')
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="card">
            <div class="card-header" style="background-color:lightgray; font-size:40px;">Create New Category</div>
                <div class="card-body" style="background-color:lightgray;">
                    <form method="POST", action="{{ route('addCategory')}}">
                        @CSRF
                        <div class="form-group">
                            <label for="Category ID">Category ID</label>
                            <input type="text" class="form-control" id="categoryID" name="categoryID">
                        </div>
                        
                        <div class="form-group">
                            <label for="Category name">Name</label>
                            <input type="text" class="form-control" id="categoryName" name="categoryName">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <br></br>
    </div>
    <div class="col-sm-3"></div>
</div>

@endsection