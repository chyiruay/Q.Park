@extends('layoutadmin')
@section('content')
<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <br></br>
        <div class="card" style="background-color:lightgray; margin-bottom:5%;">
        <div class="card-header" style="background-color:lightgray; font-size:40px;">Update Parking</div>
        <div class="card-body">
        <form method="POST" action="{{ route('updateProduct')}}" enctype="multipart/form-data">
            @CSRF
            @foreach($products as $product)
            <input type="hidden" class="form-control" id="hid" name="hid" value="{{$product->id}}">

            <div class="mb-3">
                <img src="{{asset('images/')}}/{{$product->image}}" alt="" width="200"><img src="{{asset('floorPlan/')}}/{{$product->floorPlan}}" alt="" width="200" style="margin-left:20px;"><br>
                <label for="Category name">Name</label>
                <input type="text" class="form-control" id="productName" name="productName" value="{{$product->name}}">
            </div>

            <div class="mb-3">
                <label for="Category price">Price</label>
                <input type="number" class="form-control" id="price" name="price" min="0" value="{{$product->price}}">
            </div>

            <div class="mb-3">
                <label for="Category image">Image</label>
                <input type="file" class="form-control" id="product-image" name="product-image">
            </div>
            

            <div class="mb-3">
                <label for="Category floorPlan">Floor Plan</label>
                <input type="file" class="form-control" id="product-floorPlan" name="product-floorPlan">
            </div>

            <div class="mb-3">
                <label for="Category ID">Category ID</label>
                <select name="categoryID" id="categoryID" class="form-control">
                    @foreach($categories as $category)
                        <option value="{{$category->id}}"
                            @if($product->categoryID==$category->id)
                                selected
                            @endif
                        >{{$category->name}}</option>
                    @endforeach
                    <!-- <option value="1" selected >Name</option> -->
                </select>            
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