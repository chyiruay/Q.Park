@extends('layoutproduct')
@section('content')
<div class="row">
    <div>
        <img src="{{ asset('images/parking_2.webp') }}" alt="Parking Slot" style="width:1530px; height:560px;">
        <div class="category" style="margin-bottom:180px; text-align:center;">
        <div style="margin-top:-350px; font-size:70px; color:white;"><strong>Looking for a parking?</strong></div>
            <a href="{{route('Product')}}"><button><strong>All</strong></button></a>
            <a href="{{route('viewDetail',1)}}"><button><strong>HOTEL</strong></button></a>
            <a href="{{route('viewDetail',2)}}"><button><strong>MALL</strong></button></a>
        </div>
    </div>
    @foreach($products as $product)
    <div class="col-sm-4">
        <div class="card-body">
            @csrf
            <input type="hidden" name="id" value="{{$product->id}}">
            <a class="order" href="{{route('Order',['id'=>$product->id])}}"><img src="{{ asset('images/') }}/{{$product->image}}" class="img-fluid" alt="" width="80%"></a>
            <h4 class="card-title" style="color:white;">{{$product->name}}</h4>
        </div>
    </div>
    @endforeach
</div>
    
@endsection