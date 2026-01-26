@extends('layouthome')
@section('content')
<!-- Carousel -->
<img src="images/bg_fp.jpg" style="width:100%; height:450px;">
  <div class="hometitle">
    Find and Reserve Parking in Seconds.
  </div>
<div class="card col-md-12 offset-md-4" 
    style="background-color:rgba(225,225,225,0.5); width:520px; height:80px; text-align:center; 
    border-radius:20px; margin-top:50px; margin-bottom:220px;">
  <div class="card-body" >
    <div class="inline">
      <a class="parkingbutton"href="{{ route('Product') }}" ><button class="btn btn-outline-secondary btn-lg" style="margin-top:-6px;"><strong>View Carpark</strong></button></a>
    </div>
    <div class="inline">
      <form class="form-inline my-2 my-lg-0" action="{{route('search.product')}}" method="POST">
        @csrf
        <input class="form-control mr-sm-2" name="keyword" type="search" placeholder="Search" aria-label="Search">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit" style="background-color:green; color:white;">Search</button>
      </form>
    </div>
  </div>
</div>

<!-- end carousel -->

@endsection
