@extends('layout')
@section('content')
    <div class="row">
    @foreach($products as $product)
        <div class="col-sm-3"></div>
        <div class="col-sm-6">
            <div class="card" style="background-color:lightgray; margin-top:5%; margin-bottom:100px;">
                <form method="POST" action="{{ route('add.to.cart') }}">
                @csrf
                    <div class="card-header" style="text-align:center; font-size:25px;"><strong>{{$product->name}}</strong></div>
                    <div class="card-body">
                        <input type="hidden" name="id" value="{{$product->id}}"> 
                        <img src="{{ asset('images/') }}/{{$product->image}}" class="img-fluid" alt="" style="width:250px; height:150px;">
                        <div class="floorPlan"><img src="{{ asset('floorPlan/') }}/{{$product->floorPlan}}" alt=""  style="margin-left:260px; margin-top:-180px; width:250px; height:150px;"></div>
                        <div>
                            @include('partials.alerts')
                            <p><strong>Price : RM {{$product->price}} per hour</strong></p>
                            <strong>Date : </strong><input type="date" name="date" value="{{ old('date') }}" min="{{ date('Y-m-d') }}" required>
                            <strong style="margin-left:40px;">Start from : </strong><input type="time" name="time" value="{{ old('time') }}" required><p>
                            <p><strong>Hours : <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="24" style="width:20%" required> hours</strong></input>
                            <strong style="margin-left:40px;">Parking Space : </strong>
                                <select name="space" required>
                                    <option value="">Select</option>
                                    @foreach (\App\Models\Product::spaces() as $space)
                                        <option @selected(old('space') === $space)>{{ $space }}</option>
                                    @endforeach
                                </select>
                            <p style="text-align:right;"><button class="btn btn-danger btn-xs" type="submit" style="width:100%; background-color:green; border-radius:20px; border:green">Add to cart</button></p>
                        </div>
                    </div>
                    </div>
                </form>
                </div>
            </div>
        </div>  
        @endforeach
    </div>

@endsection

