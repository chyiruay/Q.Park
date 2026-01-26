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
                            <p><strong>Price : RM {{$product->price}} perhours</strong></p>
                            <strong>Date : </strong><input type="date" name="date">
                            <strong style="margin-left:40px;">Start from : </strong><input type="time"><p>
                            <p><strong>Hours : <input type="number" name="quantity" value="1" style="width:20%"> hours</strong></input>
                            <strong style="margin-left:40px;">Parking Space : </strong>
                                <select name="space">
                                    <option></option>
                                    <option>A1</option>
                                    <option>A2</option>
                                    <option>A3</option>
                                    <option>A4</option>
                                    <option>A5</option> 
                                    <option>A6</option>
                                    <option>A7</option>
                                    <option>A8</option>
                                    <option>A9</option>
                                    <option>A10</option>
                                    <option>A11</option>
                                    <option>A12</option>
                                    <option>A13</option>
                                    <option>A14</option>
                                    <option>A15</option>
                                    <option>A16</option>
                                    <option>A17</option>
                                    <option>A18</option>
                                    <option>A19</option>
                                    <option>A20</option>
                                    <option>A21</option>
                                    <option>A22</option>
                                    <option>A23</option>
                                    <option>A24</option>
                                    <option>A25</option>
                                    <option>A26</option>
                                    <option>A27</option>
                                    <option>A28</option>
                                    <option>A29</option>
                                    <option>A30</option>
                                    <option>A31</option>
                                    <option>A32</option>
                                    <option>A33</option>
                                    <option>A34</option>
                                    <option>A35</option>
                                    <option>A36</option>
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

