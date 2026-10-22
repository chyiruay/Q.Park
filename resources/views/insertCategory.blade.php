@extends('layoutadmin')
@section('content')
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="card">
            <div class="card-header" style="background-color:lightgray; font-size:40px;">Create New Category</div>
                <div class="card-body" style="background-color:lightgray;">
                    @include('partials.alerts')
                    <form method="POST", action="{{ route('addCategory')}}">
                        @CSRF
                        <div class="mb-3">
                            <label for="Category ID">Category ID <small>(leave blank to auto-number)</small></label>
                            <input type="number" min="1" class="form-control" id="categoryID" name="categoryID" value="{{ old('categoryID') }}">
                        </div>

                        <div class="mb-3">
                            <label for="Category name">Name</label>
                            <input type="text" class="form-control" id="categoryName" name="categoryName" value="{{ old('categoryName') }}" required>
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