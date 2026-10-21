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
                    <div class="col-sm-8" style="color:white;"><h2>Parking List</h2></div>
                    <div class="col-sm-4 text-end">
                        <a href="{{ route('insertProduct') }}" class="btn btn-add-new">
                            <i class="fas fa-plus-circle me-2"></i>Add New Parking
                        </a>
                    </div>
                </div>
            </div>
            <table class="table table-bordered" style="background-color:lightgray;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Floor Plan</th>
                        <th>Name</th>
                        <th>Price(per hours)</th>                          
                        <th>Category</th>           
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{$product->id}}</td>
                        <td width="10%"><img src="{{ asset('images/') }}/{{$product->image}}" width="100%" alt="" class="img-fluid"></td>
                        <td width="10%"><img src="{{ asset('floorPlan/') }}/{{$product->floorPlan}}" width="100%" alt="" class="img-fluid"></td>
                        <td>{{$product->name}}</td>
                        <td>RM {{$product->price}}</td>                    
                        <td>{{$product->catname}}</td>
                        <td>         <!-- editProduct.php?id=$product->id -->
                            <a href="{{route('editProduct',['id'=>$product->id])}}" class="edit" title="Edit" data-bs-toggle="tooltip"><i class="material-icons">Edit</i></a><br>
                            <a href="{{route('deleteProduct',['id'=>$product->id])}}" class="delete" style="color:red"title="Delete" data-bs-toggle="tooltip" onclick="return confirm('Sure Want Delete?')"><i class="material-icons">Delete</i></a>
                        </td>
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