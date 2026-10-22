@extends('layoutadmin')
@section('content')
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6" style="height:650px;">
        <br></br>
        <div class="card">
        <div class="card-header" style="background-color:lightgray; font-size:40px;">Create New Parking</div>
        <div class="card-body" style="background-color:lightgray;">
        @include('partials.alerts')
        <form method="POST", action="{{ route('addProduct')}}" enctype="multipart/form-data">
            @CSRF
            <div class="mb-3">
                <label for="Category name">Name</label>
                <input type="text" class="form-control" id="productName" name="productName" value="{{ old('productName') }}" required>
            </div>

            <div class="mb-3">
                <label for="Category price">Price (per hour)</label>
                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="{{ old('price') }}" required>
            </div>

            <div class="mb-3">
                <label for="Category image">Image</label>
                <input type="file" class="form-control" id="product-image" name="product-image" accept="image/jpeg,image/png,image/webp" required>
            </div>

            <div class="mb-3">
                <label for="Category floorPlan">Floor Plan</label>
                <input type="file" class="form-control" id="product-floorPlan" name="product-floorPlan" accept="image/jpeg,image/png,image/webp" required>
            </div>

            <div class="mb-3">
                <label for="Category ID">Category ID</label>
                <select name="categoryID" id="categoryID" class="form-control">
                    @foreach($categoryID as $category)
                        <option value="{{$category->id}}">{{$category->name}}</option>
                    @endforeach

                </select>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        <br></br>
    </div>
    <div class="col-sm-3"></div>
</div>

@endsection